<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $db = $app['config']->get('database.connections.pgsql.database');
        if (! $app->environment('testing') || $app['config']->get('database.default') !== 'pgsql'
            || $db !== 'progress_new_model_test') {
            throw new \RuntimeException('Tests require PostgreSQL database progress_new_model_test; refusing unsafe database.');
        }

        return $app;
    }
}
