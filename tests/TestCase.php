<?php

namespace TimGavin\LaravelBlock\Tests;

use TimGavin\LaravelBlock\LaravelBlockServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelBlockServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');

        include_once __DIR__.'/migrations/create_users_table.php';

        (new \CreateUsersTable())->up();
    }
}
