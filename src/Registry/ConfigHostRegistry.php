<?php

namespace Vendor\Waha\Registry;

use Vendor\Waha\Contracts\HostRegistry;
use Vendor\Waha\Support\UnknownHostException;

class ConfigHostRegistry implements HostRegistry
{
    public function get(string $hostKey): array
    {
        $hosts = config('waha.hosts', []);
        if (! isset($hosts[$hostKey])) {
            throw new UnknownHostException("Unknown WAHA host: {$hostKey}");
        }

        return $hosts[$hostKey];
    }

    public function all(): array
    {
        return config('waha.hosts', []);
    }

    public function exists(string $hostKey): bool
    {
        $hosts = config('waha.hosts', []);

        return isset($hosts[$hostKey]);
    }
}
