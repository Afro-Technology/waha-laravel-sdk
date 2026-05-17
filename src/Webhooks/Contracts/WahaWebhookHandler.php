<?php

namespace AfroTechnology\Waha\Webhooks\Contracts;

use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;

interface WahaWebhookHandler
{
    public function handle(WahaWebhookReceived $event): void;
}
