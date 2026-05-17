<?php

namespace AfroTechnology\Waha\Tests\Feature;

use AfroTechnology\Waha\Facades\Waha as WahaFacade;
use AfroTechnology\Waha\Generated\Api\StorageApi;
use AfroTechnology\Waha\Generated\Model\MessageTextRequest;
use AfroTechnology\Waha\Generated\Model\ModelInterface;
use AfroTechnology\Waha\OpenApi\GeneratedClientFactory;
use AfroTechnology\Waha\OpenApi\Tags\StorageTag;
use AfroTechnology\Waha\Tests\TestCase;
use AfroTechnology\Waha\Webhooks\Contracts\WahaWebhookHandler;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;
use AfroTechnology\Waha\Webhooks\WahaWebhookRouter;
use Illuminate\Support\Facades\Route as RouteFacade;

final class PackageReadinessTest extends TestCase
{
    public function test_generated_client_runtime_classes_are_available(): void
    {
        $this->assertTrue(interface_exists(ModelInterface::class));
        $this->assertTrue(class_exists(MessageTextRequest::class));
        $this->assertContains(ModelInterface::class, class_implements(MessageTextRequest::class));
        $this->assertTrue(class_exists(StorageApi::class));

        $factory = new GeneratedClientFactory([
            'primary' => [
                'base_url' => 'http://localhost',
                'admin_api_key' => 'test',
                'api_key_header' => 'X-Api-Key',
            ],
        ]);

        $this->assertInstanceOf(StorageApi::class, $factory->makeTagApi('primary', 'Storage'));
        $this->assertInstanceOf(StorageTag::class, WahaFacade::storage());
    }

    public function test_webhook_config_uses_nested_defaults_without_root_fallback(): void
    {
        PackageReadinessWebhookHandler::reset();

        $this->assertSame('sync', config('waha.webhooks.processing.mode'));
        $this->assertSame([], config('waha.webhooks.handlers'));

        config()->set('waha.processing.mode', 'queue');
        config()->set('waha.handlers', [
            'message.any' => PackageReadinessWebhookHandler::class,
        ]);

        $router = $this->app->make(WahaWebhookRouter::class);
        $router->handle(new WahaWebhookReceived(
            hostKey: 'primary',
            payload: ['event' => 'message.any'],
            rawBody: '{"event":"message.any"}',
        ));

        $this->assertSame('sync', config('waha.webhooks.processing.mode'));
        $this->assertSame([], PackageReadinessWebhookHandler::$events);
    }

    public function test_webhook_route_is_registered_when_enabled(): void
    {
        $this->assertTrue($this->hasPostRoute('webhooks/waha/{hostKey}'));
    }

    private function hasPostRoute(string $uri): bool
    {
        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            if (! in_array('POST', $route->methods(), true)) {
                continue;
            }

            if ($route->uri() === $uri) {
                return true;
            }
        }

        return false;
    }
}

final class PackageReadinessWebhookHandler implements WahaWebhookHandler
{
    /** @var list<string> */
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
    }

    public function handle(WahaWebhookReceived $event): void
    {
        $eventName = $event->payload['event'] ?? null;
        if (is_string($eventName)) {
            self::$events[] = $eventName;
        }
    }
}
