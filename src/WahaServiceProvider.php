<?php

namespace AfroTechnology\Waha;

use AfroTechnology\Waha\Console\FetchOpenApiSpecCommand;
use AfroTechnology\Waha\Console\GenerateIdeHelperCommand;
use AfroTechnology\Waha\Console\GenerateOpenApiClientCommand;
use AfroTechnology\Waha\Console\UpdateOpenApiCommand;
use AfroTechnology\Waha\Contracts\ApiKeyProvider;
use AfroTechnology\Waha\Contracts\HostRegistry;
use AfroTechnology\Waha\Contracts\PinStore;
use AfroTechnology\Waha\Contracts\SessionRouter;
use AfroTechnology\Waha\Debug\WahaDebugManager;
use AfroTechnology\Waha\Debug\WahaDebugStore;
use AfroTechnology\Waha\Http\WahaHttpClient;
use AfroTechnology\Waha\Pin\CompositePinStore;
use AfroTechnology\Waha\Pin\DbPinStore;
use AfroTechnology\Waha\Pin\RedisPinStore;
use AfroTechnology\Waha\Registry\ConfigHostRegistry;
use AfroTechnology\Waha\Registry\DbHostRegistry;
use AfroTechnology\Waha\Routing\NullRouter;
use AfroTechnology\Waha\Routing\PinningRouter;
use AfroTechnology\Waha\Security\ConfigApiKeyProvider;
use AfroTechnology\Waha\Security\DbApiKeyProvider;
use AfroTechnology\Waha\Webhooks\Config\WebhookConfigResolver;
use AfroTechnology\Waha\Webhooks\Console\PruneWebhookEventsCommand;
use AfroTechnology\Waha\Webhooks\EventStore\WebhookEventStoreFactory;
use AfroTechnology\Waha\Webhooks\Http\WahaWebhookController;
use AfroTechnology\Waha\Webhooks\WahaWebhookRouter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');

        $this->app->bind(HostRegistry::class, function (Container $app) {
            $driver = (string) config('waha.registry.driver', 'config');

            if ($driver === 'custom') {
                return $this->makeConfiguredContract(
                    $app,
                    'waha.registry.custom.host_registry',
                    HostRegistry::class,
                );
            }

            return $driver === 'db'
                ? new DbHostRegistry
                : new ConfigHostRegistry;
        });

        $this->app->bind(ApiKeyProvider::class, function (Container $app) {
            $hosts = $app->make(HostRegistry::class);
            $driver = (string) config('waha.registry.driver', 'config');

            if ($driver === 'custom') {
                $customProvider = config('waha.registry.custom.api_key_provider');
                if (is_string($customProvider) && $customProvider !== '') {
                    return $this->makeConfiguredContract(
                        $app,
                        'waha.registry.custom.api_key_provider',
                        ApiKeyProvider::class,
                    );
                }

                return new ConfigApiKeyProvider($hosts);
            }

            return $driver === 'db'
                ? new DbApiKeyProvider($hosts)
                : new ConfigApiKeyProvider($hosts);
        });

        $this->app->singleton(PinStore::class, function ($app) {
            $driver = config('waha.pin_store.driver', 'auto');
            $ttl = (int) config('waha.pin_store.ttl_seconds', 0);

            $redisAvailable = class_exists(RedisManager::class) && $app->bound('redis');
            $dbAvailable = $this->pinTablesExist();

            $redis = fn () => new RedisPinStore($app->make('redis'), config('waha.pin_store.redis_connection', 'default'));
            $db = fn () => new DbPinStore;

            if ($driver === 'redis') {
                return $redis();
            }
            if ($driver === 'db') {
                return $db();
            }
            if ($driver === 'composite') {
                return new CompositePinStore($redis(), $db(), $ttl);
            }

            // auto
            if ($redisAvailable && $dbAvailable) {
                return new CompositePinStore($redis(), $db(), $ttl);
            }
            if ($redisAvailable) {
                return $redis();
            }
            if ($dbAvailable) {
                return $db();
            }

            // no backing store
            return new class implements PinStore
            {
                public function getHostForSession(string $sessionName): ?string
                {
                    return null;
                }

                public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void {}

                public function forget(string $sessionName): void {}
            };
        });

        $this->app->bind(SessionRouter::class, function ($app) {
            $defaultHost = config('waha.default_host', 'primary');
            $driver = config('waha.routing.driver', 'none');

            return $driver === 'pin'
                ? new PinningRouter($app->make(PinStore::class), $defaultHost)
                : new NullRouter($defaultHost);
        });

        $this->app->singleton(WahaHttpClient::class, function ($app) {
            return new WahaHttpClient(
                $app->make(HostRegistry::class),
                $app->make(ApiKeyProvider::class),
                $app->make(SessionRouter::class)
            );
        });

        // Debug store (captures last HTTP request/response when enabled)
        $this->app->singleton(WahaDebugStore::class, function () {
            return new WahaDebugStore;
        });

        $this->app->singleton(WahaDebugManager::class, function ($app) {
            $mgr = new WahaDebugManager($app->make(WahaDebugStore::class));

            $enabled = (bool) config('waha.debug.enabled', false);
            $maxKb = (int) config('waha.debug.max_body_kb', 64);
            $channel = (string) config('waha.debug.log_channel', 'stack');

            $mgr->configure($enabled, $maxKb * 1024, $channel);

            return $mgr;
        });

        // Single source of truth: Manager singleton
        $this->app->singleton(WahaManager::class, function (Container $app) {
            return new WahaManager(
                config('waha'),
                $app->make(WahaDebugManager::class),
                $app->make(HostRegistry::class),
                $app->make(ApiKeyProvider::class),
            );
        });

        // Facade accessor: use alias only (NO separate singleton that calls make() again)

        $this->app->alias(WahaManager::class, 'waha');

        // Resolver can be singleton.
        $this->app->singleton(
            WebhookConfigResolver::class,
            fn (Container $app) => new WebhookConfigResolver($app->make(HostRegistry::class))
        );

        $this->app->singleton(WahaWebhookRouter::class, fn ($app) => new WahaWebhookRouter($app));

        $this->app->singleton(
            WebhookEventStoreFactory::class,
            fn ($app) => new WebhookEventStoreFactory(
                $app->make(WebhookConfigResolver::class)
            )
        );

    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/waha.php' => config_path('waha.php'),
        ], 'waha-config');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'waha-migrations');

            $this->commands([
                FetchOpenApiSpecCommand::class,
                GenerateOpenApiClientCommand::class,
                UpdateOpenApiCommand::class,
                GenerateIdeHelperCommand::class,
                PruneWebhookEventsCommand::class,
            ]);
        }

        $this->registerWebhookRoutes();

    }

    private function registerWebhookRoutes(): void
    {
        /** @var array<string,mixed> $webhooks */
        $webhooks = (array) config('waha.webhooks', []);

        if (! (bool) ($webhooks['enabled'] ?? true)) {
            return;
        }

        $route = (array) ($webhooks['route'] ?? []);
        $prefix = (string) ($route['prefix'] ?? '/webhooks/waha');
        $middleware = array_values((array) ($route['middleware'] ?? ['api']));

        Route::middleware($middleware)
            ->prefix(trim($prefix, '/'))
            ->group(function (): void {
                Route::post('{hostKey}', WahaWebhookController::class);
            });
    }

    private function pinTablesExist(): bool
    {
        try {
            return $this->app['db']->connection()->getSchemaBuilder()->hasTable('waha_session_pins');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $contract
     * @return T
     */
    private function makeConfiguredContract(Container $app, string $configKey, string $contract): object
    {
        $class = config($configKey);

        if (! is_string($class) || $class === '') {
            throw new RuntimeException("WAHA custom registry config '{$configKey}' must be a class name.");
        }

        $instance = $app->make($class);

        if (! $instance instanceof $contract) {
            throw new RuntimeException("WAHA custom registry class '{$class}' must implement {$contract}.");
        }

        return $instance;
    }
}
