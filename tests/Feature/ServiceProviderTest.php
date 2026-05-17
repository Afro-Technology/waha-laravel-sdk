<?php

namespace AfroTechnology\Waha\Tests\Feature;

use AfroTechnology\Waha\Facades\Waha as WahaFacade;
use AfroTechnology\Waha\Tests\TestCase;
use AfroTechnology\Waha\WahaManager;
use Illuminate\Contracts\Container\BindingResolutionException;

final class ServiceProviderTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function test_it_resolves_manager(): void
    {
        $manager = $this->app->make(WahaManager::class);
        $this->assertInstanceOf(WahaManager::class, $manager);
    }

    public function test_config_is_mergeable(): void
    {
        $this->assertIsArray(config('waha.hosts'));
        $this->assertSame('primary', config('waha.default_host'));
    }

    public function test_facade_resolves_client(): void
    {
        $client = WahaFacade::host('primary');
        $this->assertInstanceOf(\AfroTechnology\Waha\OpenApi\WahaApiProxy::class, $client);
    }
}
