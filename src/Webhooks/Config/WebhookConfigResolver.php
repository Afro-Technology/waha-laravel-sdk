<?php

namespace AfroTechnology\Waha\Webhooks\Config;

use AfroTechnology\Waha\Contracts\HostRegistry;

final class WebhookConfigResolver
{
    public function __construct(private readonly ?HostRegistry $hosts = null) {}

    /**
     * Resolve webhook config for a host by merging global defaults + host overrides.
     *
     * @return array{
     *   enabled: bool,
     *   require_hmac: bool,
     *   max_clock_skew_ms: int,
     *   replay: array{enabled: bool, ttl_seconds: int, cache_prefix: string},
     *   store: array{enabled: bool, store_raw: bool, retention_days: int},
     *   route: array{prefix: string, middleware: list<string>},
     *   webhook_secret: string
     * }
     */
    public function resolve(string $hostKey): array
    {
        /** @var array<string,mixed> $global */
        $global = (array) config('waha.webhooks', []);

        /** @var array<string,mixed> $host */
        $host = $this->hostConfig($hostKey);

        /** @var array<string,mixed> $hostOverrides */
        $hostOverrides = (array) ($host['webhooks'] ?? []);

        $enabled = (bool) ($global['enabled'] ?? true);

        $requireHmacGlobal = (bool) ($global['require_hmac'] ?? false);
        $requireHmacHost = $hostOverrides['require_hmac'] ?? null;
        $requireHmac = is_bool($requireHmacHost) ? $requireHmacHost : $requireHmacGlobal;

        $maxClockSkewMs = (int) ($global['max_clock_skew_ms'] ?? 300_000);

        $replay = (array) ($global['replay'] ?? []);
        $replayEnabled = (bool) ($replay['enabled'] ?? true);
        $replayTtl = (int) ($replay['ttl_seconds'] ?? 900);
        $replayPrefix = (string) ($replay['cache_prefix'] ?? 'waha:webhook:');

        $store = (array) ($global['store'] ?? []);
        $storeEnabledGlobal = (bool) ($store['enabled'] ?? false);
        $storeEnabledHost = $hostOverrides['store_events'] ?? null;
        $storeEnabled = is_bool($storeEnabledHost) ? $storeEnabledHost : $storeEnabledGlobal;

        $storeRaw = (bool) ($store['store_raw'] ?? true);
        $retentionDays = (int) ($store['retention_days'] ?? 7);

        $route = (array) ($global['route'] ?? []);
        $prefix = (string) ($route['prefix'] ?? '/webhooks/waha');
        /** @var list<string> $middleware */
        $middleware = array_values((array) ($route['middleware'] ?? ['api']));

        $secret = (string) ($host['webhook_secret'] ?? '');

        return [
            'enabled' => $enabled,
            'require_hmac' => $requireHmac,
            'max_clock_skew_ms' => $maxClockSkewMs,
            'replay' => [
                'enabled' => $replayEnabled,
                'ttl_seconds' => $replayTtl,
                'cache_prefix' => $replayPrefix,
            ],
            'store' => [
                'enabled' => $storeEnabled,
                'store_raw' => $storeRaw,
                'retention_days' => $retentionDays,
            ],
            'route' => [
                'prefix' => $prefix,
                'middleware' => $middleware,
            ],
            'webhook_secret' => $secret,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hostConfig(string $hostKey): array
    {
        if ($this->hosts && $this->hosts->exists($hostKey)) {
            return $this->hosts->get($hostKey);
        }

        return (array) config("waha.hosts.{$hostKey}", []);
    }
}
