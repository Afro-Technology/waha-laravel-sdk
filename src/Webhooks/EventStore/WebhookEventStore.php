<?php

namespace AfroTechnology\Waha\Webhooks\EventStore;

use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use Throwable;

interface WebhookEventStore
{
    /**
     * Persist accepted webhook event.
     *
     * @param  array<string,mixed>|null  $payload
     * @param array{
     *   request_id?: string|null,
     *   timestamp_ms?: int|null,
     *   hmac_present?: bool,
     *   hmac_algo?: string|null,
     *   raw_sha256?: string|null,
     * } $meta
     */
    public function store(string $hostKey, ?string $eventName, ?array $payload, ?string $rawBody, array $meta = [], string $status = WahaWebhookEvent::STATUS_QUEUED): ?WahaWebhookEvent;

    public function markProcessing(?int $eventId): void;

    public function markProcessed(?int $eventId): void;

    public function markFailed(?int $eventId, Throwable $e): void;
}
