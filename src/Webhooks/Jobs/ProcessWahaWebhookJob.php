<?php

namespace AfroTechnology\Waha\Webhooks\Jobs;

use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;
use AfroTechnology\Waha\Webhooks\EventStore\WebhookEventStoreFactory;
use AfroTechnology\Waha\Webhooks\WahaWebhookRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ProcessWahaWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

    public function handle(WahaWebhookRouter $router, WebhookEventStoreFactory $storeFactory): void
    {
        $store = $storeFactory->forHost($this->hostKey);
        $store->markProcessing($this->eventId);

        $evt = new WahaWebhookReceived(
            hostKey: $this->hostKey,
            payload: $this->payload,
            rawBody: $this->rawBody,
            meta: $this->meta,
            eventId: $this->eventId,
        );

        try {
            event($evt);
            $router->handle($evt);
            $store->markProcessed($this->eventId);
        } catch (Throwable $e) {
            $store->markFailed($this->eventId, $e);

            throw $e;
        }
    }
}
