<?php

namespace Eightyfour600\LaravelRouterGenerator\Tests;

use File;
use ReflectionClass;
use ReflectionMethod;
use App\Http\Controllers;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use Eightyfour600\LaravelRouterGenerator\Models\Router;
use Eightyfour600\LaravelRouterGenerator\LaravelRouterGenerator;
use Eightyfour600\LaravelRouterGenerator\LaravelRouterGeneratorServiceProvider;
class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelRouterGeneratorServiceProvider::class];
    }

    // /** @test */
    // public function it_can_generate_route_action()
    // {
    //     $router_generator = new LaravelRouterGenerator('get', '/', 'HomeController@index');
    //     $using_controller = $router_generator->generateRouteAction();
    //     dd($using_controller);
    //     $this->assertTrue($using_controller);
    // }

    /** @test */
    public function it_can_not_generate_route_action()
    {
        $router_generator = new LaravelRouterGenerator('get', '/', 'FooController@index');
        $generate_route_action = $router_generator->generateRouteAction();
        $this->assertFalse($generate_route_action);
    }
    /** @test */
    public function it_can_use_controller()
    {
        $router_generator = new LaravelRouterGenerator('get', '/', 'BarController@index');
        $using_controller = $router_generator->isUsingController();
        $this->assertTrue($using_controller);
    }

    /** @test */
    public function it_can_generate_routes()
    {
        $router_generator = new LaravelRouterGenerator('get', '/', 'HomeController@index');
        $routes = $router_generator->generate(__DIR__.'/../src/Routes/routes.php');
        $this->assertTrue($routes->exists);
    }

    /** @test */
    public function it_can_check_controller_directory_exists()
    {
        $path = app_path('Http/Controllers/');
        $this->assertTrue(File::isDirectory($path));
    }

    /** @test */
    public function it_can_scan_controller_directory()
    {
        $path = app_path('Http/Controllers/');
        $directory = array_values(array_diff(scandir($path), array('..', '.')));
        $this->assertArrayHasKey(1, $directory);
    }

}
