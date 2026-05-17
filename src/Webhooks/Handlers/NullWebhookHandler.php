<?php

namespace AfroTechnology\Waha\Webhooks\Handlers;

use AfroTechnology\Waha\Webhooks\Contracts\WahaWebhookHandler;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;

final class NullWebhookHandler implements WahaWebhookHandler
{
    public function handle(WahaWebhookReceived $event): void
    {
        // Intentionally no-op.
    }
}
