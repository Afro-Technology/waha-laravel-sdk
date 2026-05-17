<?php

namespace AfroTechnology\Waha\Webhooks;

use AfroTechnology\Waha\Webhooks\Contracts\WahaWebhookHandler;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;
use AfroTechnology\Waha\Webhooks\Handlers\NullWebhookHandler;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;

final class WahaWebhookRouter
{
    public function __construct(private Container $container) {}

    /**
     * @throws BindingResolutionException
     */
    public function handle(WahaWebhookReceived $event): void
    {
        /** @var array<string,string> $map */
        $map = (array) config('waha.webhooks.handlers', []);

        $eventName = $this->eventName($event);

        $handlerClass = $map[$eventName] ?? null;

        // Optional wildcard support: "message.*"
        if (! $handlerClass) {
            foreach ($map as $key => $class) {
                if (str_ends_with($key, '.*')) {
                    $prefix = substr($key, 0, -2);
                    if ($prefix !== '' && str_starts_with($eventName, $prefix.'.')) {
                        $handlerClass = $class;
                        break;
                    }
                }
            }
        }

        if (! is_string($handlerClass) || $handlerClass === '') {
            $handler = new NullWebhookHandler;
            $handler->handle($event);

            return;
        }

        $handler = $this->container->make($handlerClass);

        if (! $handler instanceof WahaWebhookHandler) {
            throw new \RuntimeException("Webhook handler must implement WahaWebhookHandler: {$handlerClass}");
        }

        $handler->handle($event);
    }

    private function eventName(WahaWebhookReceived $event): string
    {
        $name = $event->payload['event'] ?? null;

        return is_string($name) && $name !== '' ? $name : 'unknown';
    }
}
