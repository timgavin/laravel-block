<?php

namespace TimGavin\LaravelBlock\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelBlock extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-block';
    }
}
