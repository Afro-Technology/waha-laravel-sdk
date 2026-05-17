<?php

namespace AfroTechnology\Waha\Webhooks\Http;

use AfroTechnology\Waha\Security\WebhookGuard;
use AfroTechnology\Waha\Security\WebhookVerifier;
use AfroTechnology\Waha\Webhooks\Config\WebhookConfigResolver;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;
use AfroTechnology\Waha\Webhooks\EventStore\WebhookEventStoreFactory;
use AfroTechnology\Waha\Webhooks\Jobs\ProcessWahaWebhookJob;
use AfroTechnology\Waha\Webhooks\Models\WahaWebhookEvent;
use AfroTechnology\Waha\Webhooks\WahaWebhookRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WahaWebhookController
{
    public function __construct(
        private WebhookConfigResolver $resolver,
        private WebhookVerifier $verifier,
    ) {}

    public function __invoke(Request $request, string $hostKey): JsonResponse
    {
        $cfg = $this->resolver->resolve($hostKey);

        if (! $cfg['enabled']) {
            // Webhooks globally disabled.
            return response()->json(['ok' => false, 'reason' => 'webhooks_disabled'], 404);
        }

        $rawBody = (string) $request->getContent();

        // WAHA standard headers (based on WAHA 2026.x)
        $hmac = $request->header('X-Webhook-Hmac');
        $algo = $request->header('X-Webhook-Hmac-Algorithm'); // e.g. sha512
        $requestId = $request->header('X-Webhook-Request-Id');
        $timestampMsHeader = $request->header('X-Webhook-Timestamp');

        $timestampMs = null;
        if (is_string($timestampMsHeader) && $timestampMsHeader !== '' && ctype_digit($timestampMsHeader)) {
            $timestampMs = (int) $timestampMsHeader;
        }

        // Build guard with resolved config
        $guard = new WebhookGuard([
            'require_hmac' => (bool) $cfg['require_hmac'],
            'max_clock_skew_ms' => (int) $cfg['max_clock_skew_ms'],
            'replay' => $cfg['replay'],
        ]);

        // 1) If HMAC is required, missing signature => reject
        $hasHmac = is_string($hmac) && $hmac !== '';
        if ($cfg['require_hmac'] && ! $hasHmac) {
            Log::warning('WAHA webhook rejected (missing HMAC)', [
                'hostKey' => $hostKey,
                'request_id' => is_string($requestId) ? $requestId : null,
            ]);

            return response()->json(['ok' => false, 'reason' => 'missing_hmac'], 401);
        }

        // 2) If signature present, verify it (HMAC optional but supported)
        if ($hasHmac) {
            $ok = $this->verifier->verify(
                hostKey: $hostKey,
                rawBody: $rawBody,
                hmacHeader: $hmac,
                algoHeader: is_string($algo) ? $algo : null,
            );

            if (! $ok) {
                Log::warning('WAHA webhook rejected (invalid HMAC)', [
                    'hostKey' => $hostKey,
                    'request_id' => is_string($requestId) ? $requestId : null,
                    'algo' => is_string($algo) ? $algo : null,
                ]);

                return response()->json(['ok' => false, 'reason' => 'invalid_hmac'], 401);
            }
        }

        // 3) Timestamp freshness check (optional, but recommended)
        $tsCheck = $guard->checkTimestamp($timestampMs);
        if (! $tsCheck['ok']) {
            Log::warning('WAHA webhook rejected (timestamp)', [
                'hostKey' => $hostKey,
                'request_id' => is_string($requestId) ? $requestId : null,
                'timestamp_ms' => $timestampMs,
                'reason' => $tsCheck['reason'],
            ]);

            // 401/400/409 could be debated; 401 is "auth-ish". Use 400 here.
            return response()->json(['ok' => false, 'reason' => $tsCheck['reason']], 400);
        }

        // 4) Replay dedup by request id (optional)
        $replayCheck = $guard->checkReplay($hostKey, is_string($requestId) ? $requestId : null);
        if (! $replayCheck['ok']) {
            Log::info('WAHA webhook replay detected (ignored)', [
                'hostKey' => $hostKey,
                'request_id' => is_string($requestId) ? $requestId : null,
            ]);

            // Important: return 200 to avoid sender retry storms.
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => $replayCheck['reason']]);
        }

        // 5) At this point request is accepted. Parse payload.
        $payload = $request->json()->all();

        $meta = [
            'request_id' => is_string($requestId) ? $requestId : null,
            'timestamp_ms' => $timestampMs !== null ? (string) $timestampMs : null,
            'algo' => is_string($algo) ? $algo : null,
            'hmac_present' => $hasHmac ? '1' : '0',
        ];

        Log::info('WAHA webhook accepted', [
            'hostKey' => $hostKey,
            'event' => $payload['event'] ?? null,
            'request_id' => $meta['request_id'],
            'timestamp_ms' => $meta['timestamp_ms'],
            'algo' => $meta['algo'],
            'hmac_present' => $hasHmac,
        ]);

        $processing = (array) config('waha.webhooks.processing', []);
        $mode = (string) ($processing['mode'] ?? 'sync');
        $eventName = is_string($payload['event'] ?? null) ? (string) $payload['event'] : null;

        $metaForStore = [
            'request_id' => is_string($requestId) ? $requestId : null,
            'timestamp_ms' => $timestampMs,
            'hmac_present' => $hasHmac,
            'hmac_algo' => is_string($algo) ? $algo : null,
            'raw_sha256' => hash('sha256', $rawBody),
        ];

        /** @var WebhookEventStoreFactory $storeFactory */
        $storeFactory = app(WebhookEventStoreFactory::class);
        $store = $storeFactory->forHost($hostKey);
        $storedEvent = $store->store(
            hostKey: $hostKey,
            eventName: $eventName,
            payload: $payload,
            rawBody: $rawBody,
            meta: $metaForStore,
            status: $mode === 'queue' ? WahaWebhookEvent::STATUS_QUEUED : WahaWebhookEvent::STATUS_PROCESSING,
        );
        $storedEventId = $storedEvent ? (int) $storedEvent->getKey() : null;

        if ($mode === 'queue') {
            $job = new ProcessWahaWebhookJob(
                hostKey: $hostKey,
                payload: $payload,
                rawBody: $rawBody,
                meta: $meta,
                eventId: $storedEventId,
            );

            $connection = $processing['queue_connection'] ?? null;
            $queueName = (string) ($processing['queue_name'] ?? 'default');

            if (is_string($connection) && $connection !== '') {
                $job->onConnection($connection);
            }

            if ($queueName !== '') {
                $job->onQueue($queueName);
            }

            dispatch($job);

            // Return 200 immediately to prevent retries.
            return response()->json(['ok' => true, 'queued' => true]);
        }

        // sync mode: fire event + run router inline
        $evt = new WahaWebhookReceived(
            hostKey: $hostKey,
            payload: $payload,
            rawBody: $rawBody,
            meta: $meta,
            eventId: $storedEventId,
        );

        try {
            $store->markProcessing($storedEventId);
            event($evt);

            /** @var WahaWebhookRouter $router */
            $router = app(WahaWebhookRouter::class);
            $router->handle($evt);

            $store->markProcessed($storedEventId);
        } catch (Throwable $e) {
            $store->markFailed($storedEventId, $e);

            throw $e;
        }

        return response()->json(['ok' => true]);
    }
}
