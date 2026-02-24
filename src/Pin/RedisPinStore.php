<?php

namespace Vendor\Waha\Pin;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Vendor\Waha\Contracts\PinStore;

class RedisPinStore implements PinStore
{
    public function __construct(private RedisFactory $redis, private string $connection = 'default') {}

    private function key(string $sessionName): string
    {
        return "waha:pin:{$sessionName}";
    }

    public function getHostForSession(string $sessionName): ?string
    {
        $val = $this->redis->connection($this->connection)->get($this->key($sessionName));

        return $val ?: null;
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        $conn = $this->redis->connection($this->connection);
        $key = $this->key($sessionName);

        if ($ttlSeconds && $ttlSeconds > 0) {
            $conn->setex($key, $ttlSeconds, $hostKey);
        } else {
            $conn->set($key, $hostKey);
        }
    }

    public function forget(string $sessionName): void
    {
        $this->redis->connection($this->connection)->del($this->key($sessionName));
    }
}
