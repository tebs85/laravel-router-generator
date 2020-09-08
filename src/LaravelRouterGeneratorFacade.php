<?php

namespace Eightyfour600\LaravelRouterGenerator;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Eightyfour600\LaravelRouterGenerator\Skeleton\SkeletonClass
 */
class LaravelRouterGeneratorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-router-generator';
    }
}
