<?php

namespace AfroTechnology\Waha\Webhooks\EventStore;

use AfroTechnology\Waha\Webhooks\Config\WebhookConfigResolver;

final class WebhookEventStoreFactory
{
    public function __construct(private WebhookConfigResolver $resolver) {}

    public function forHost(string $hostKey): WebhookEventStore
    {
        $cfg = $this->resolver->resolve($hostKey);

        $store = (array) ($cfg['store'] ?? []);
        $enabled = (bool) ($store['enabled'] ?? false);
        if (! $enabled) {
            return new NullWebhookEventStore;
        }

        $storeRaw = (bool) ($store['store_raw'] ?? true);

        // Optional: allow overriding raw cap later if needed.
        return new DatabaseWebhookEventStore([
            'store_raw' => $storeRaw,
            'max_raw_kb' => 256,
        ]);
    }
}
