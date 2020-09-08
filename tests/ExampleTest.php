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
            // print "App\\Http\\Controllers\\" . $file_name[0];
            $methods = (new ReflectionClass('App\\Http\\Controllers\\' . $file_name[0]))->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if ($method->class == $$file_name[0] && $method->name != '__contruct') {
                    switch ($method) {
                        case 'index':
                            # code...
                            break;

                        case 'create':
                            # code...
                            break;

                        case 'store':
                            # code...
                            break;

                        case 'show':
                            # code...
                            break;

                        case 'edit':
                            # code...
                            break;

                        case 'update':
                            # code...
                            break;

                        case 'destroy':
                            # code...
                            break;

                        default:
                            $method;
                            break;
                    }
                }

            }
        }
        $this->assertTrue($directory->exists);
    }
}
