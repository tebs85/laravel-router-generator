<?php

namespace Eightyfour600\LaravelRouterGenerator\Tests;

use File;
use ReflectionClass;
use ReflectionMethod;
use App\Http\Controllers;
use Orchestra\Testbench\TestCase;
use Illuminate\Filesystem\Filesystem;
use Eightyfour600\LaravelRouterGenerator\Models\Router;
use Eightyfour600\LaravelRouterGenerator\LaravelRouterGeneratorServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelRouterGeneratorServiceProvider::class];
    }

    /** @test */
    public function it_can_check_controller_directory_exists()
    {
        $path = app_path('Http/Controllers/');

        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }

        $this->assertTrue(File::isDirectory($path));
    }

    public function it_can_scan_controller_directory()
    {
        $path = app_path('Http/Controllers/');
        $directory = array_values(array_diff(scandir($path), array('..', '.')));
        $this->assertTrue($directory->exists);
    }

    public function it_can_print_controller_methods()
    {
        $path = app_path('Http/Controllers/');
        $files = array_values(array_diff(scandir($path), array('..', '.')));

        foreach ($files as $file) {
            $file_name = explode('.', $file);
            $class = new ReflectionClass('App\\Http\\Controllers\\' . $file_name[0]);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $resources = preg_split('/(?=[A-Z])/', lcfirst($class));
                if ($method->class == $file_name[0] && $method->name != '__contruct') {
                    switch ($method->name) {
                        case 'index':
                            Route::get(Str::plural($resources[0], 2), $file_name[0] . '@' . $method->name);
                            break;

                        case 'create':
                            Route::get(Str::plural($resources[0], 2) .'create', $file_name[0] . '@' . $method->name);
                            break;

                        case 'store':
                            Route::post(Str::plural($resources[0], 2) .'', $file_name[0] . '@' . $method->name);
                            break;

                        case 'show':
                            Route::get(Str::plural($resources[0], 2) .'{id}', $file_name[0] . '@' . $method->name);
                            break;

                        case 'edit':
                            Route::get(Str::plural($resources[0], 2) .'{id}/edit', $file_name[0] . '@' . $method->name);
                            break;

                        case 'update':
                            Route::put(Str::plural($resources[0], 2) .'{id}', $file_name[0] . '@' . $method->name);
                            break;

                        case 'destroy':
                            Route::delete(Str::plural($resources[0], 2) .'{id}', $file_name[0] . '@' . $method->name);
                            break;

                        default:
                            $method;
                            break;
                    }
                }

            }
        }
        // app_routes();
        // $this->assertTrue($directory->exists);
    }
}
