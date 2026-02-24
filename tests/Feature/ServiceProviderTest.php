<?php

namespace Vendor\Waha\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Vendor\Waha\Facades\Waha as WahaFacade;
use Vendor\Waha\Tests\TestCase;
use Vendor\Waha\WahaManager;

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
        $this->assertInstanceOf(\Vendor\Waha\OpenApi\WahaApiProxy::class, $client);
    }
}
