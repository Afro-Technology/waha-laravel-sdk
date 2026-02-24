<?php

namespace Vendor\Waha\Debug;

final class WahaDebugManager
{
    private bool $enabled = false;

    private int $maxBodyBytes = 65536; // 64KB default

    private string $logChannel = 'stack';

    /** @var bool[] */
    private array $stack = [];

    public function __construct(private readonly WahaDebugStore $store) {}

    public function configure(bool $enabled, int $maxBodyBytes, string $logChannel): void
    {
        $this->enabled = $enabled;
        $this->maxBodyBytes = max(0, $maxBodyBytes);
        $this->logChannel = $logChannel !== '' ? $logChannel : 'stack';
    }

    public function store(): WahaDebugStore
    {
        return $this->store;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function maxBodyBytes(): int
    {
        return $this->maxBodyBytes;
    }

    public function logChannel(): string
    {
        return $this->logChannel;
    }

    public function pushEnabled(bool $enabled): void
    {
        $this->stack[] = $this->enabled;
        $this->enabled = $enabled;
    }

    public function popEnabled(): void
    {
        if ($this->stack === []) {
            return;
        }
        $this->enabled = array_pop($this->stack);
    }
}
