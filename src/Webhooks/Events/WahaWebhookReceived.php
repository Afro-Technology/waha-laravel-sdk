<?php

namespace AfroTechnology\Waha\Webhooks\Events;

final class WahaWebhookReceived
{
    /**
     * @param  array<string,mixed>  $payload
     * @param  array<string,mixed>  $meta
     */
    public function __construct(
        public readonly string $hostKey,
        public readonly array $payload,
        public readonly string $rawBody,
        public readonly array $meta = [],
        public readonly ?int $eventId = null,
    ) {}
}
