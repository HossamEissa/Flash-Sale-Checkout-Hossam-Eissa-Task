<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    protected static $migrationRun = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$migrationRun) {
            Artisan::call('migrate:fresh');
            static::$migrationRun = true;
        }
    }
}
