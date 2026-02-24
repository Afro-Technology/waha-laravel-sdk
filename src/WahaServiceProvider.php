<?php

namespace Vendor\Waha;

use Illuminate\Support\ServiceProvider;
use Vendor\Waha\Console\FetchOpenApiSpecCommand;
use Vendor\Waha\Console\GenerateIdeHelperCommand;
use Vendor\Waha\Console\GenerateOpenApiClientCommand;
use Vendor\Waha\Console\UpdateOpenApiCommand;
use Vendor\Waha\Contracts\ApiKeyProvider;
use Vendor\Waha\Contracts\HostRegistry;
use Vendor\Waha\Contracts\PinStore;
use Vendor\Waha\Contracts\SessionRouter;
use Vendor\Waha\Debug\WahaDebugManager;
use Vendor\Waha\Debug\WahaDebugStore;
use Vendor\Waha\Http\WahaHttpClient;
use Vendor\Waha\Pin\CompositePinStore;
use Vendor\Waha\Pin\DbPinStore;
use Vendor\Waha\Pin\RedisPinStore;
use Vendor\Waha\Registry\ConfigHostRegistry;
use Vendor\Waha\Registry\DbHostRegistry;
use Vendor\Waha\Routing\NullRouter;
use Vendor\Waha\Routing\PinningRouter;
use Vendor\Waha\Security\ConfigApiKeyProvider;
use Vendor\Waha\Security\DbApiKeyProvider;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');

        $this->app->bind(HostRegistry::class, function () {
            return config('waha.registry.driver', 'config') === 'db'
                ? new DbHostRegistry
                : new ConfigHostRegistry;
        });

        $this->app->bind(ApiKeyProvider::class, function ($app) {
            $hosts = $app->make(HostRegistry::class);

            return config('waha.registry.driver', 'config') === 'db'
                ? new DbApiKeyProvider($hosts)
                : new ConfigApiKeyProvider($hosts);
        });

        $this->app->singleton(PinStore::class, function ($app) {
            $driver = config('waha.pin_store.driver', 'auto');
            $ttl = (int) config('waha.pin_store.ttl_seconds', 0);

            $redisAvailable = class_exists(\Illuminate\Redis\RedisManager::class) && $app->bound('redis');
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
            return new class implements \Vendor\Waha\Contracts\PinStore
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
        $this->app->singleton(\Vendor\Waha\WahaManager::class, function () {
            return new \Vendor\Waha\WahaManager(config('waha'), $this->app->make(WahaDebugManager::class));
        });

        // Facade accessor: use alias only (NO separate singleton that calls make() again)

        $this->app->alias(\Vendor\Waha\WahaManager::class, 'waha');

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
            ]);
        }
    }

    private function pinTablesExist(): bool
    {
        try {
            return $this->app['db']->connection()->getSchemaBuilder()->hasTable('waha_session_pins');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
