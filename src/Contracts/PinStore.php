<?php

namespace Vendor\Waha\Contracts;

/**
 * Stores a mapping between WAHA session name and hostKey.
 */
interface PinStore
{
    public function getHostForSession(string $sessionName): ?string;

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void;

    public function forget(string $sessionName): void;
}
