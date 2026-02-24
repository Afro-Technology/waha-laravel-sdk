<?php

namespace Vendor\Waha\Registry;

use Vendor\Waha\Contracts\HostRegistry;
use Vendor\Waha\Models\WahaHost;
use Vendor\Waha\Support\UnknownHostException;

class DbHostRegistry implements HostRegistry
{
    public function get(string $hostKey): array
    {
        $host = WahaHost::query()->where('key', $hostKey)->where('is_active', true)->first();
        if (! $host) {
            throw new UnknownHostException("Unknown or inactive WAHA host: {$hostKey}");
        }

        return [
            'base_url' => $host->base_url,
            'api_key_header' => $host->api_key_header ?: 'X-Api-Key',
            'admin_api_key' => $host->admin_api_key,
            'default_session' => $host->default_session ?: 'default',
            'webhook_secret' => $host->webhook_secret,
            'mode' => $host->mode ?: 'admin_fallback',
        ];
    }

    public function all(): array
    {
        return WahaHost::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('key')
            ->map(fn ($h) => [
                'base_url' => $h->base_url,
                'api_key_header' => $h->api_key_header ?: 'X-Api-Key',
                'admin_api_key' => $h->admin_api_key,
                'default_session' => $h->default_session ?: 'default',
                'webhook_secret' => $h->webhook_secret,
                'mode' => $h->mode ?: 'admin_fallback',
            ])->all();
    }

    public function exists(string $hostKey): bool
    {
        return WahaHost::query()->where('key', $hostKey)->where('is_active', true)->exists();
    }
}
