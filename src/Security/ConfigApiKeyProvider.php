<?php

namespace Vendor\Waha\Security;

use Vendor\Waha\Contracts\ApiKeyProvider;
use Vendor\Waha\Contracts\HostRegistry;

class ConfigApiKeyProvider implements ApiKeyProvider
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
        $cfg = $this->hosts->get($hostKey);
        $keys = $cfg['session_keys'] ?? [];

        return $keys[$sessionName] ?? ($keys['default'] ?? null);
    }

    public function mode(string $hostKey): string
    {
        $cfg = $this->hosts->get($hostKey);

        return $cfg['mode'] ?? 'admin_fallback';
    }
}
