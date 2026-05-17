<?php

namespace AfroTechnology\Waha\Tests\Feature;

use AfroTechnology\Waha\Tests\TestCase;
use Illuminate\Support\Facades\Route as RouteFacade;

final class WebhookRouteDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('waha.webhooks.enabled', false);
    }

    public function test_webhook_route_is_not_registered_when_disabled(): void
    {
        $this->assertFalse($this->hasPostRoute('webhooks/waha/{hostKey}'));
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
