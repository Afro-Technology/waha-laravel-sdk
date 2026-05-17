<?php

namespace AfroTechnology\Waha\Webhooks\EventStore;

use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use Illuminate\Support\Str;
use Throwable;

final class DatabaseWebhookEventStore implements WebhookEventStore
{
    /**
     * @param  array{store_raw?: bool, max_raw_kb?: int}  $options
     */
    public function __construct(private array $options = [])
    {
        $this->options += [
            'store_raw' => true,
            'max_raw_kb' => 256, // safety: 256 KB max
        ];
    }

    public function store(string $hostKey, ?string $eventName, ?array $payload, ?string $rawBody, array $meta = [], string $status = WahaWebhookEvent::STATUS_QUEUED): WahaWebhookEvent
    {
        $storeRaw = (bool) ($this->options['store_raw'] ?? true);
        $maxRawKb = (int) ($this->options['max_raw_kb'] ?? 256);

        $raw = null;
        if ($storeRaw && is_string($rawBody)) {
            $raw = $this->truncateUtf8($rawBody, $maxRawKb * 1024);
        }

        return WahaWebhookEvent::query()->create([
            'host_key' => $hostKey,
            'event_name' => $eventName,
            'request_id' => $this->stringOrNull($meta['request_id'] ?? null),
            'timestamp_ms' => $this->intOrNull($meta['timestamp_ms'] ?? null),
            'hmac_algo' => $this->stringOrNull($meta['hmac_algo'] ?? null),
            'hmac_present' => (bool) ($meta['hmac_present'] ?? false),
            'raw_sha256' => $this->stringOrNull($meta['raw_sha256'] ?? null),
            'payload' => $payload,
            'raw_body' => $raw,
            'status' => $status,
            'attempts' => 0,
        ]);
    }

    public function markProcessing(?int $eventId): void
    {
        $event = $this->findEvent($eventId);
        if (! $event) {
            return;
        }

        $event->forceFill([
            'status' => WahaWebhookEvent::STATUS_PROCESSING,
            'attempts' => ((int) $event->attempts) + 1,
            'failed_at' => null,
            'last_error' => null,
        ])->save();
    }

    public function markProcessed(?int $eventId): void
    {
        $event = $this->findEvent($eventId);
        if (! $event) {
            return;
        }

        $event->forceFill([
            'status' => WahaWebhookEvent::STATUS_PROCESSED,
            'processed_at' => now(),
            'failed_at' => null,
            'last_error' => null,
        ])->save();
    }

    public function markFailed(?int $eventId, Throwable $e): void
    {
        $event = $this->findEvent($eventId);
        if (! $event) {
            return;
        }

        $event->forceFill([
            'status' => WahaWebhookEvent::STATUS_FAILED,
            'failed_at' => now(),
            'last_error' => $this->truncateUtf8(get_class($e).': '.$e->getMessage(), 4000),
        ])->save();
    }

    private function findEvent(?int $eventId): ?WahaWebhookEvent
    {
        if ($eventId === null || $eventId <= 0) {
            return null;
        }

        return WahaWebhookEvent::query()->find($eventId);
    }

    private function truncateUtf8(string $s, int $maxBytes): string
    {
        if ($maxBytes <= 0) {
            return '';
        }

        if (strlen($s) <= $maxBytes) {
            return $s;
        }

        // Try to cut cleanly (avoid breaking multi-byte chars).
        $cut = substr($s, 0, $maxBytes);

        return Str::limit($cut, $maxBytes, '…');
    }

    private function stringOrNull(mixed $v): ?string
    {
        if (! is_string($v) || $v === '') {
            return null;
        }

        return $v;
    }

    private function intOrNull(mixed $v): ?int
    {
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && $v !== '' && ctype_digit($v)) {
            return (int) $v;
        }

        return null;
    }
}
