<?php

namespace Vendor\Waha\Pin;

use Vendor\Waha\Contracts\PinStore;
use Vendor\Waha\Models\WahaSessionPin;

class DbPinStore implements PinStore
{
    public function getHostForSession(string $sessionName): ?string
    {
        $pin = WahaSessionPin::query()->where('session_name', $sessionName)->first();

        return $pin?->host_key;
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        WahaSessionPin::query()->updateOrCreate(
            ['session_name' => $sessionName],
            ['host_key' => $hostKey, 'last_seen_at' => now()]
        );
    }

    public function forget(string $sessionName): void
    {
        WahaSessionPin::query()->where('session_name', $sessionName)->delete();
    }
}
