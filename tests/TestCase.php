<?php

namespace Vendor\Waha\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \Vendor\Waha\WahaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Ensure config is available for tests.
        $app['config']->set('waha.registry.driver', 'config');
        $app['config']->set('waha.routing.driver', 'none');
        $app['config']->set('waha.default_host', 'primary');
        $app['config']->set('waha.hosts.primary.base_url', 'http://localhost');
        $app['config']->set('waha.hosts.primary.admin_api_key', 'test');
        $app['config']->set('waha.hosts.primary.default_session', 'default');
    }
}
