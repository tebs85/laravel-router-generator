<?php

namespace Eightyfour600\LaravelRouterGenerator\Tests;

use Orchestra\Testbench\TestCase;
use Eightyfour600\LaravelRouterGenerator\LaravelRouterGeneratorServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelRouterGeneratorServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
