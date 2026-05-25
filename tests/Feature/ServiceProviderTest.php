<?php

namespace AfroTechnology\Waha\Tests\Feature;

use AfroTechnology\Waha\Contracts\ApiKeyProvider;
use AfroTechnology\Waha\Contracts\HostRegistry;
use AfroTechnology\Waha\Facades\Waha as WahaFacade;
use AfroTechnology\Waha\Generated\Api\StorageApi;
use AfroTechnology\Waha\OpenApi\GeneratedClientFactory;
use AfroTechnology\Waha\OpenApi\OpenApiRouter;
use AfroTechnology\Waha\OpenApi\OpenApiSpecRepository;
use AfroTechnology\Waha\OpenApi\WahaApiProxy;
use AfroTechnology\Waha\OpenApi\WahaTagProxy;
use AfroTechnology\Waha\Support\ApiKeyMissingException;
use AfroTechnology\Waha\Tests\TestCase;
use AfroTechnology\Waha\WahaManager;
use AfroTechnology\Waha\Webhooks\Config\WebhookConfigResolver;
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
        $this->assertInstanceOf(WahaApiProxy::class, $client);
    }

    public function test_custom_registry_driver_resolves_sdk_runtime_contracts_from_container(): void
    {
        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', CustomApiKeyProvider::class);
        config()->set('waha.hosts.vault', null);

        $hosts = $this->app->make(HostRegistry::class);
        $keys = $this->app->make(ApiKeyProvider::class);

        $this->assertInstanceOf(CustomHostRegistry::class, $hosts);
        $this->assertInstanceOf(CustomApiKeyProvider::class, $keys);

        $manager = $this->app->make(WahaManager::class);
        $factoryMethod = new \ReflectionMethod($manager, 'getClientFactory');
        $factoryMethod->setAccessible(true);
        $factory = $factoryMethod->invoke($manager);

        $this->assertInstanceOf(GeneratedClientFactory::class, $factory);

        $api = $factory->makeTagApi('vault', 'Storage');

        $this->assertInstanceOf(StorageApi::class, $api);
        $this->assertSame('https://vault-waha.test', $api->getConfig()->getHost());
        $this->assertSame('custom-secret', $api->getConfig()->getApiKey('X-Custom-Waha-Key'));
    }

    public function test_openapi_factory_uses_session_scoped_key_when_session_is_known(): void
    {
        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', CustomApiKeyProvider::class);

        $manager = $this->app->make(WahaManager::class);
        $factoryMethod = new \ReflectionMethod($manager, 'getClientFactory');
        $factoryMethod->setAccessible(true);
        $factory = $factoryMethod->invoke($manager);

        $api = $factory->makeTagApi('vault', 'Storage', 'vault-session');

        $this->assertInstanceOf(StorageApi::class, $api);
        $this->assertSame('custom-session-secret', $api->getConfig()->getApiKey('X-Custom-Waha-Key'));
        $this->assertSame(['X-Custom-Waha-Key' => 'custom-session-secret'], $factory->authHeaders('vault', 'vault-session'));
    }

    public function test_openapi_factory_rejects_missing_session_key_in_strict_mode(): void
    {
        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', CustomApiKeyProvider::class);

        $manager = $this->app->make(WahaManager::class);
        $factoryMethod = new \ReflectionMethod($manager, 'getClientFactory');
        $factoryMethod->setAccessible(true);
        $factory = $factoryMethod->invoke($manager);

        $this->expectException(ApiKeyMissingException::class);

        $factory->authHeaders('vault', 'missing-session');
    }

    public function test_openapi_proxy_extracts_body_session_before_building_generated_client(): void
    {
        $router = new OpenApiRouter((new OpenApiSpecRepository(dirname(__DIR__, 2).'/resources/openapi/openapi.json'))->load());
        $operation = collect($router->operationsByTag('📤 Chatting'))
            ->first(fn (array $operation): bool => ($operation['alias'] ?? null) === 'sendText');

        $this->assertIsArray($operation);

        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', CustomApiKeyProvider::class);

        $manager = $this->app->make(WahaManager::class);
        $factoryMethod = new \ReflectionMethod($manager, 'getClientFactory');
        $factoryMethod->setAccessible(true);
        $factory = $factoryMethod->invoke($manager);

        $proxy = new WahaTagProxy($router, $factory, 'vault', '📤 Chatting');
        $buildInputs = new \ReflectionMethod($proxy, 'buildOperationInputs');
        $buildInputs->setAccessible(true);
        $sessionFromInputs = new \ReflectionMethod($proxy, 'sessionFromInputs');
        $sessionFromInputs->setAccessible(true);

        $inputs = $buildInputs->invoke($proxy, $operation, [
            '11111111111@c.us',
            'Hello',
            null,
            null,
            true,
            false,
            'vault-session',
        ]);

        $this->assertSame('vault-session', $sessionFromInputs->invoke($proxy, $inputs));
        $this->assertSame(['X-Custom-Waha-Key' => 'custom-session-secret'], $factory->authHeaders('vault', 'vault-session'));
    }

    public function test_openapi_proxy_maps_php_safe_named_arguments_to_openapi_query_and_generated_parameters(): void
    {
        $router = new OpenApiRouter((new OpenApiSpecRepository(dirname(__DIR__, 2).'/resources/openapi/openapi.json'))->load());
        $operation = collect($router->operationsByTag('💬 Chats'))
            ->first(fn (array $operation): bool => ($operation['alias'] ?? null) === 'getChatMessages');

        $this->assertIsArray($operation);

        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', CustomApiKeyProvider::class);

        $manager = $this->app->make(WahaManager::class);
        $factoryMethod = new \ReflectionMethod($manager, 'getClientFactory');
        $factoryMethod->setAccessible(true);
        $factory = $factoryMethod->invoke($manager);

        $proxy = new WahaTagProxy($router, $factory, 'vault', '💬 Chats');
        $buildInputs = new \ReflectionMethod($proxy, 'buildOperationInputs');
        $buildInputs->setAccessible(true);
        $buildArgs = new \ReflectionMethod($proxy, 'buildCallArgsByReflection');
        $buildArgs->setAccessible(true);

        $inputs = $buildInputs->invoke($proxy, $operation, [
            'chat_id' => '11111111111@c.us',
            'sort_by' => 'timestamp',
            'sort_order' => 'desc',
            'download_media' => true,
            'merge' => true,
            'limit' => 50,
            'offset' => 10,
            'filter_timestamp_lte' => 1774040340,
            'filter_timestamp_gte' => 1773994500,
            'filter_from_me' => false,
            'session' => 'vault-session',
        ]);

        $this->assertSame('vault-session', $inputs['path']['session']);
        $this->assertSame('11111111111@c.us', $inputs['path']['chatId']);
        $this->assertSame(1774040340, $inputs['query']['filter.timestamp.lte']);
        $this->assertSame(1773994500, $inputs['query']['filter.timestamp.gte']);
        $this->assertFalse($inputs['query']['filter.fromMe']);

        $api = $factory->makeTagApi('vault', '💬 Chats', 'vault-session');
        $method = $factory->resolveGeneratedMethod($api, $operation['operationId'] ?? null, 'getChatMessages');
        $args = $buildArgs->invoke($proxy, $api, $method, $operation, $inputs);

        $this->assertSame(50, $args[0]);
        $this->assertSame('vault-session', $args[1]);
        $this->assertSame('11111111111@c.us', $args[2]);
        $this->assertSame(1774040340, $args[8]);
        $this->assertSame(1773994500, $args[9]);
        $this->assertFalse($args[10]);
    }

    public function test_webhook_config_resolver_uses_custom_host_registry(): void
    {
        config()->set('waha.registry.driver', 'custom');
        config()->set('waha.registry.custom.host_registry', CustomHostRegistry::class);
        config()->set('waha.registry.custom.api_key_provider', null);
        config()->set('waha.hosts.vault.webhook_secret', 'wrong-config-secret');

        $resolver = $this->app->make(WebhookConfigResolver::class);
        $resolved = $resolver->resolve('vault');

        $this->assertSame('custom-webhook-secret', $resolved['webhook_secret']);
        $this->assertFalse($resolved['require_hmac']);
        $this->assertTrue($resolved['store']['enabled']);
    }
}

final class CustomHostRegistry implements HostRegistry
{
    public function get(string $hostKey): array
    {
        if ($hostKey !== 'vault') {
            throw new \RuntimeException("Unknown host {$hostKey}.");
        }

        return [
            'base_url' => 'https://vault-waha.test',
            'api_key_header' => 'X-Custom-Waha-Key',
            'admin_api_key' => 'host-secret',
            'default_session' => 'vault-session',
            'webhook_secret' => 'custom-webhook-secret',
            'mode' => 'strict_session_key',
            'webhooks' => [
                'require_hmac' => false,
                'store_events' => true,
            ],
        ];
    }

    public function all(): array
    {
        return [
            'vault' => $this->get('vault'),
        ];
    }

    public function exists(string $hostKey): bool
    {
        return $hostKey === 'vault';
    }
}

final class CustomApiKeyProvider implements ApiKeyProvider
{
    public function headerName(string $hostKey): string
    {
        return 'X-Custom-Waha-Key';
    }

    public function adminKey(string $hostKey): ?string
    {
        return $hostKey === 'vault' ? 'custom-secret' : null;
    }

    public function sessionKey(string $hostKey, string $sessionName): ?string
    {
        return $hostKey === 'vault' && $sessionName === 'vault-session' ? 'custom-session-secret' : null;
    }

    public function mode(string $hostKey): string
    {
        return 'strict_session_key';
    }
}
