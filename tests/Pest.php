<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use TimGavin\LaravelBlock\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in(__DIR__);
