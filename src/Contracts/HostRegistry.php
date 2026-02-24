<?php

namespace Vendor\Waha\Contracts;

/**
 * Provides host definitions (base_url, keys, webhook secret, default_session, mode).
 */
interface HostRegistry
{
    /**
     * @return array<string, mixed> Host config.
     */
    public function get(string $hostKey): array;

    /**
     * @return array<string, array<string, mixed>> All hosts keyed by hostKey.
     */
    public function all(): array;

    public function exists(string $hostKey): bool;
}
