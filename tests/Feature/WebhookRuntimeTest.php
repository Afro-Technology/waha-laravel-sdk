<?php

namespace AfroTechnology\Waha\Tests\Feature;

use AfroTechnology\Waha\Tests\TestCase;
use AfroTechnology\Waha\Webhooks\Contracts\WahaWebhookHandler;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;
use AfroTechnology\Waha\Webhooks\EventStore\DatabaseWebhookEventStore;
use AfroTechnology\Waha\Webhooks\EventStore\WebhookEventStoreFactory;
use AfroTechnology\Waha\Webhooks\Jobs\ProcessWahaWebhookJob;
use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use AfroTechnology\Waha\Webhooks\WahaWebhookRouter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class WebhookRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RecordingWebhookHandler::reset();
    }

    public function test_queue_mode_acknowledges_fast_and_dispatches_processing_job(): void
    {
        Queue::fake();

        config()->set('waha.webhooks.require_hmac', false);
        config()->set('waha.webhooks.replay.enabled', false);
        config()->set('waha.webhooks.store.enabled', false);
        config()->set('waha.webhooks.processing.mode', 'queue');
        config()->set('waha.webhooks.processing.queue_connection', 'database');
        config()->set('waha.webhooks.processing.queue_name', 'waha-webhooks');

        $response = $this->postJson('/webhooks/waha/primary', [
            'event' => 'message.any',
            'payload' => ['id' => 'msg-1'],
        ]);

        $response->assertOk()->assertJson([
            'ok' => true,
            'queued' => true,
        ]);

        Queue::assertPushed(ProcessWahaWebhookJob::class, function (ProcessWahaWebhookJob $job): bool {
            return $job->hostKey === 'primary'
                && ($job->payload['event'] ?? null) === 'message.any'
                && $job->eventId === null
                && $job->connection === 'database'
                && $job->queue === 'waha-webhooks';
        });
    }

    public function test_router_supports_exact_wildcard_missing_and_invalid_handlers(): void
    {
        $router = $this->app->make(WahaWebhookRouter::class);

        config()->set('waha.webhooks.handlers', [
            'message.any' => RecordingWebhookHandler::class,
            'group.*' => RecordingWebhookHandler::class,
        ]);

        $router->handle($this->event('message.any'));
        $router->handle($this->event('group.join'));
        $router->handle($this->event('unknown.event'));

        $this->assertSame(['message.any', 'group.join'], RecordingWebhookHandler::$events);

        config()->set('waha.webhooks.handlers', [
            'message.any' => \stdClass::class,
        ]);

        $this->expectException(RuntimeException::class);
        $router->handle($this->event('message.any'));
    }

    public function test_processing_job_runs_router_without_requiring_event_storage(): void
    {
        config()->set('waha.webhooks.store.enabled', false);
        config()->set('waha.webhooks.handlers', [
            'message.any' => RecordingWebhookHandler::class,
        ]);

        $job = new ProcessWahaWebhookJob(
            hostKey: 'primary',
            payload: ['event' => 'message.any'],
            rawBody: '{"event":"message.any"}',
        );

        $job->handle(
            $this->app->make(WahaWebhookRouter::class),
            $this->app->make(WebhookEventStoreFactory::class),
        );

        $this->assertSame(['message.any'], RecordingWebhookHandler::$events);
    }

    public function test_database_event_store_tracks_processing_state(): void
    {
        $this->createWebhookEventsTable();

        $store = new DatabaseWebhookEventStore([
            'store_raw' => true,
            'max_raw_kb' => 256,
        ]);

        $event = $store->store(
            hostKey: 'primary',
            eventName: 'message.any',
            payload: ['event' => 'message.any'],
            rawBody: '{"event":"message.any"}',
            meta: [
                'request_id' => 'req-1',
                'timestamp_ms' => 123,
                'hmac_present' => true,
                'hmac_algo' => 'sha512',
                'raw_sha256' => hash('sha256', '{"event":"message.any"}'),
            ],
        );

        $this->assertInstanceOf(WahaWebhookEvent::class, $event);
        $this->assertSame(WahaWebhookEvent::STATUS_QUEUED, $event->status);
        $this->assertSame(0, $event->attempts);

        $store->markProcessing((int) $event->getKey());
        $event->refresh();

        $this->assertSame(WahaWebhookEvent::STATUS_PROCESSING, $event->status);
        $this->assertSame(1, $event->attempts);

        $store->markProcessed((int) $event->getKey());
        $event->refresh();

        $this->assertSame(WahaWebhookEvent::STATUS_PROCESSED, $event->status);
        $this->assertNotNull($event->processed_at);

        $failed = $store->store('primary', 'message.any', ['event' => 'message.any'], '{}');
        $store->markProcessing((int) $failed->getKey());
        $store->markFailed((int) $failed->getKey(), new RuntimeException('handler failed'));
        $failed->refresh();

        $this->assertSame(WahaWebhookEvent::STATUS_FAILED, $failed->status);
        $this->assertSame(1, $failed->attempts);
        $this->assertNotNull($failed->failed_at);
        $this->assertStringContainsString('handler failed', (string) $failed->last_error);
    }

    private function event(string $eventName): WahaWebhookReceived
    {
        return new WahaWebhookReceived(
            hostKey: 'primary',
            payload: ['event' => $eventName],
            rawBody: '{"event":"'.$eventName.'"}',
        );
    }

    private function createWebhookEventsTable(): void
    {
        Schema::dropIfExists('waha_webhook_events');

        Schema::create('waha_webhook_events', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('host_key', 64)->index();
            $table->string('event_name', 128)->nullable()->index();
            $table->string('request_id', 128)->nullable()->index();
            $table->unsignedBigInteger('timestamp_ms')->nullable()->index();
            $table->string('hmac_algo', 32)->nullable();
            $table->boolean('hmac_present')->default(false);
            $table->char('raw_sha256', 64)->nullable()->index();
            $table->json('payload')->nullable();
            $table->longText('raw_body')->nullable();
            $table->string('status', 32)->default('queued')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['host_key', 'request_id'], 'waha_webhook_events_host_request_unique');
        });
    }
}

final class RecordingWebhookHandler implements WahaWebhookHandler
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
