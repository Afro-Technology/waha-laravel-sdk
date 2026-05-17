<?php

namespace AfroTechnology\Waha\Security;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class WebhookGuard
{
    /**
     * @param array{
     *   require_hmac: bool,
     *   max_clock_skew_ms: int,
     *   replay: array{enabled: bool, ttl_seconds: int, cache_prefix: string}
     * } $config
     */
    public function __construct(private array $config) {}

    /**
     * @return array{ok: bool, reason: string|null}
     */
    public function checkTimestamp(?int $timestampMs): array
    {
        $maxSkew = (int) ($this->config['max_clock_skew_ms'] ?? 0);
        if ($maxSkew <= 0) {
            return ['ok' => true, 'reason' => null];
        }

        if (! is_int($timestampMs) || $timestampMs <= 0) {
            // If you want strict mode, enforce it via require_hmac or add require_timestamp config later.
            return ['ok' => true, 'reason' => null];
        }

        $nowMs = (int) (Carbon::now()->getTimestampMs());
        $delta = abs($nowMs - $timestampMs);

        if ($delta > $maxSkew) {
            return ['ok' => false, 'reason' => 'timestamp_outside_window'];
        }

        return ['ok' => true, 'reason' => null];
    }

    /**
     * Deduplicate by request id for a TTL window using cache.
     *
     * @return array{ok: bool, reason: string|null}
     */
    public function checkReplay(string $hostKey, ?string $requestId): array
    {
        $replay = (array) ($this->config['replay'] ?? []);
        if (! (bool) ($replay['enabled'] ?? false)) {
            return ['ok' => true, 'reason' => null];
        }

        if (! is_string($requestId) || $requestId === '') {
            // No request id -> cannot dedup
            return ['ok' => true, 'reason' => null];
        }

        $ttl = (int) ($replay['ttl_seconds'] ?? 0);
        if ($ttl <= 0) {
            return ['ok' => true, 'reason' => null];
        }

        $prefix = (string) ($replay['cache_prefix'] ?? 'waha:webhook:');
        $key = $prefix.$hostKey.':'.$requestId;

        // Cache::add returns false if key already exists
        $added = Cache::add($key, 1, $ttl);

        if (! $added) {
            return ['ok' => false, 'reason' => 'replay_detected'];
        }

        return ['ok' => true, 'reason' => null];
    }
}
