<?php

namespace AfroTechnology\Waha\Security;

use AfroTechnology\Waha\Contracts\ApiKeyProvider;
use AfroTechnology\Waha\Contracts\HostRegistry;
use AfroTechnology\Waha\Models\WahaSessionKey;

class DbApiKeyProvider implements ApiKeyProvider
{
    public function __construct(private HostRegistry $hosts) {}

    public function headerName(string $hostKey): string
    {
        $cfg = $this->hosts->get($hostKey);

        return $cfg['api_key_header'] ?? 'X-Api-Key';
    }

    public function adminKey(string $hostKey): ?string
    {
        $cfg = $this->hosts->get($hostKey);

        return $cfg['admin_api_key'] ?? null;
    }

    public function sessionKey(string $hostKey, string $sessionName): ?string
    {
        $key = WahaSessionKey::query()
            ->where('host_key', $hostKey)
            ->where('session_name', $sessionName)
            ->whereNull('revoked_at')
            ->first();

        return $key?->api_key;
    }

    public function mode(string $hostKey): string
    {
        $cfg = $this->hosts->get($hostKey);

        return $cfg['mode'] ?? 'admin_fallback';
    }
}
