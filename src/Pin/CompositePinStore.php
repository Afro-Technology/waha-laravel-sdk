<?php

namespace Vendor\Waha\Pin;

use Vendor\Waha\Contracts\PinStore;

/**
 * Write-through cache:
 * - read: redis -> db
 * - write: db + redis
 */
class CompositePinStore implements PinStore
{
    public function __construct(
        private PinStore $redis,
        private PinStore $db,
        private int $ttlSeconds = 0
    ) {}

    public function getHostForSession(string $sessionName): ?string
    {
        $host = $this->redis->getHostForSession($sessionName);
        if ($host) {
            return $host;
        }

        $host = $this->db->getHostForSession($sessionName);
        if ($host) {
            $this->redis->pin($sessionName, $host, $this->ttlSeconds ?: null);
        }

        return $host;
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        $this->db->pin($sessionName, $hostKey);
        $this->redis->pin($sessionName, $hostKey, $ttlSeconds ?? ($this->ttlSeconds ?: null));
    }

    public function forget(string $sessionName): void
    {
        $this->db->forget($sessionName);
        $this->redis->forget($sessionName);
    }
}
