<?php

namespace AfroTechnology\Waha\Webhooks\EventStore;

use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use Throwable;

final class NullWebhookEventStore implements WebhookEventStore
{
    public function store(string $hostKey, ?string $eventName, ?array $payload, ?string $rawBody, array $meta = [], string $status = WahaWebhookEvent::STATUS_QUEUED): ?WahaWebhookEvent
    {
        return null;
    }

    public function markProcessing(?int $eventId): void {}

    public function markProcessed(?int $eventId): void {}

    public function markFailed(?int $eventId, Throwable $e): void {}
}
