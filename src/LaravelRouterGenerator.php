<?php

namespace Eightyfour600\LaravelRouterGenerator;

use Exception;
use Route as Router;
use Illuminate\Support\Arr;
use Illuminate\Routing\Route;
use Eightyfour600\LaravelRouterGenerator\LaravelRouterActionGenerator;
use Eightyfour600\LaravelRouterGenerator\Exceptions\LaravelRouterFileNotFoundException;
use Eightyfour600\LaravelRouterGenerator\Exceptions\LaravelRouterHasRegisteredException;
class LaravelRouterGenerator extends Route
{
    public $available_methods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

	protected $action_generator;

	public function __construct($methods, $uri, $action)
	{
		parent::__construct($methods, $uri, $action);
	}

	public function getConditions()
	{
		return $this->wheres;
	}

	public function getActionName()
	{
		$action = $this->getAction();

		if (is_string($action['uses'])) {
			return $action['uses'];
		}

		return parent::getActionName();
	}

	public function getFiltersBefore()
	{
		$action = $this->getAction();

		if (!isset($action['before'])) return '';

		return is_array($action['before'])? implode('|', $action['before']) : $action['before'];
	}

	public function getFiltersAfter()
	{
		$action = $this->getAction();
		if (!isset($action['after'])) return '';

		return is_array($action['after'])? implode('|', $action['after']) : $action['after'];
	}

	public function getMethodsString()
	{
		$methods = $this->methods;

		foreach($methods as $i => $method) {
			$methods[$i] = strtoupper($method);
		}

		if (count($methods) == 2 AND in_array('HEAD', $methods) AND in_array('GET', $methods)) {
			return 'GET';
		}

		return implode('|', $methods);
	}

	public function isUsingController()
	{
		return ("closure" != strtolower($this->getActionName()));
	}

	public function getActionGenerator()
	{
		if (!$this->action_generator) {
			$this->action_generator = new LaravelRouterActionGenerator($this);
		}

		return $this->action_generator;
	}

	public function generate($route_file, $generate_action = true) {

		if ($this->routeHasRegistered()) {
			$methods = $this->getMethodsString();
			$uri = $this->resolvedUri();
			throw new LaravelRouterHasRegisteredException("Route '{$methods} {$uri}' has registered before");
		}

		$this->generateRoute($route_file);

		if ($generate_action) {
			$this->generateRouteAction();
		}
	}

	public function generateRouteAction($controller_file = null)
	{
		if (!$this->needGenerateRouteAction()) {
			return false;
		}

		$this->action_generator->generate();
	}

	protected function generateRoute($route_file)
	{
		if (!file_exists($route_file)) {
			throw new LaravelRouterFileNotFoundException("Cannot generate route, target route file not found");
		}

		$route_code = $this->makeRouteCode();

		file_put_contents($route_file, trim(file_get_contents($route_file))."\n\n".$route_code);
	}

	protected function makeRouteCode()
	{
		$conditions = $this->getConditions();
		$action = $this->getAction();
		$uri = $this->resolvedUri();
		$methods = $this->methods;
		$method = strtolower($methods[0]);

		$before_filters = Arr::get($action, 'before');
		$after_filters = Arr::get($action, 'after');

		$route_action = [
			'as' => $this->getName(),
			'before' => is_array($before_filters)? implode('|', $before_filters) : $before_filters,
			'after' => is_array($after_filters)? implode('|', $after_filters) : $after_filters,
			'uses' => $this->getRouteAction(),
		];

		$action_arr_def = array();

		foreach ($route_action as $key => $value) {
			if (is_string($value)) {
				$action_arr_def[] = "'{$key}' => '{$value}'";
			}
		}

		$only_uses = true;
		foreach ($route_action as $key => $value) {
			if ($key != "uses" AND !empty($value)) {
				$only_uses = false;
			}
		}

		$actions_str = implode(",\n\r\t", $action_arr_def);

		$route_data = $only_uses? "'".$route_action['uses']."'" : "[\n\r\t{$actions_str}\n\r\t]";
		$code = "// Laravel Router generated route\nRoute::{$method}('{$uri}', ".$route_data.")";

		foreach ($conditions as $param => $condition) {
			$code .= "\n\r\t->where('{$param}', '{$condition}')";
		}

		$code .= ";";
		return $code;
	}

	public function needGenerateRouteAction()
	{
		return (
			$this->isUsingController()
			AND (
				$this->needGenerateController()
				OR
				$this->needGenerateMethod()
			)
		);
	}

	public function needGenerateController()
	{
		list($controller, $method) = explode('@', $this->getActionName(), 2);

		return false == $this->getActionGenerator()->controllerExists();
	}

	public function needGenerateMethod()
	{
		list($controller, $method) = explode('@', $this->getActionName(), 2);

		return false == $this->getActionGenerator()->methodExists();
	}

	protected function getRouteAction()
	{
		$route_action = $this->getActionName();

		if (!$this->isUsingController()) {
			$params_code = $this->makeParamsCode();
			$route_action = "function({$params_code}){\n\r\t\t\t\n\r\t}";
		}

		return $route_action;
	}

	public function parseParams()
	{
		$uri = $this->uri();
		preg_match_all("/{(?<params>\w+)(\?(=(?<default>\S+))?)?}/", $uri, $match);

		$route_params_key = $match['params'];
		$route_params_value = $match['default'];

		$route_params = array();
		foreach($route_params_key as $i => $key) {
			// check if parameter is optional
			if (preg_match('/\{'.$key.'\?/', $uri)) {
				$value = $route_params_value[$i];
				if (in_array($value, ['false','true','array()','null']) OR is_numeric($value)) {
					$route_params[$key] = $value;
 				} else {
					$route_params[$key] = "'{$value}'";
 				}
			} else {
				$route_params[$key] = null;
			}
		}

		return $route_params;
	}

	public function resolvedUri()
	{
		$uri = $this->uri();
		return "/".ltrim(preg_replace("/\=\S+(})/", "$1", $uri), "/");
	}

	public function routeHasRegistered()
	{
		$uri = $this->resolvedUri();
		$routes = Router::getRoutes();
		list($method) = explode('|', $this->getMethodsString(), 1);

		foreach ($routes as $route) {
			if (ltrim($uri,"/") == ltrim($route->uri(),"/") && in_array($method, $route->methods)) {
				return true;
			}
		}

		return false;
	}

	public static function makeFromRoute(Route $route)
	{
		$route_generator = new static($route->methods, $route->uri(), $route->getAction());
		return $route_generator;
	}
}
