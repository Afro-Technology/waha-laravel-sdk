<?php

namespace AfroTechnology\Waha\Security;

use AfroTechnology\Waha\Contracts\HostRegistry;
use Illuminate\Http\Request;

/**
 * Verifies WAHA webhook signature (HMAC) according to WAHA docs.
 *
 * WAHA headers:
 * - X-Webhook-Request-Id
 * - X-Webhook-Timestamp (unix ms)
 * - X-Webhook-Hmac
 * - X-Webhook-Hmac-Algorithm (usually sha512)
 */
final class WebhookVerifier
{
    public const HEADER_HMAC = 'X-Webhook-Hmac';

    public const HEADER_HMAC_ALGO = 'X-Webhook-Hmac-Algorithm';

    public const HEADER_REQUEST_ID = 'X-Webhook-Request-Id';

    public const HEADER_TIMESTAMP = 'X-Webhook-Timestamp';

    /**
     * Legacy/alternate headers you may have used before.
     * Keeping as fallback makes upgrades smoother.
     */
    public const LEGACY_HEADER_HMAC = 'X-Waha-Signature';

    public const LEGACY_HEADER_HMAC_ALGO = 'X-Waha-Signature-Algorithm';

    /**
     * Allow only these algorithms to avoid weird inputs.
     */
    private const ALLOWED_ALGOS = ['sha256', 'sha512'];

    public function __construct(private HostRegistry $hosts) {}

    /**
     * Verify HMAC using raw body and headers.
     *
     * - Uses host-specific secret: hosts->get($hostKey)['webhook_secret']
     * - Accepts raw hex HMAC, or formats like "sha256=<hex>"
     */
    public function verify(
        string $hostKey,
        string $rawBody,
        ?string $hmacHeader,
        ?string $algoHeader = null
    ): bool {
        $secret = $this->hosts->get($hostKey)['webhook_secret'] ?? null;
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        if (! is_string($hmacHeader) || $hmacHeader === '') {
            return false;
        }

        $algo = $this->normalizeAlgo($algoHeader);
        if ($algo === null) {
            return false;
        }

        $provided = $this->extractSignature($hmacHeader);
        if ($provided === '') {
            return false;
        }

        $expected = hash_hmac($algo, $rawBody, $secret);

        // WAHA uses hex output (hash_hmac default). Compare in constant-time.
        return hash_equals(strtolower($expected), strtolower($provided));
    }

    /**
     * Convenience helper for Laravel Request.
     * Reads WAHA headers by default, but also supports legacy headers as fallback.
     *
     * @return array{ok: bool, request_id: string|null, timestamp_ms: int|null}
     */
    public function verifyRequest(Request $request, string $hostKey): array
    {
        $raw = (string) $request->getContent();

        $hmac = $request->header(self::HEADER_HMAC);
        $algo = $request->header(self::HEADER_HMAC_ALGO);

        // Fallback to legacy headers if WAHA headers are missing.
        if (! is_string($hmac) || $hmac === '') {
            $hmac = $request->header(self::LEGACY_HEADER_HMAC);
            $algo = $request->header(self::LEGACY_HEADER_HMAC_ALGO);
        }

        $ok = $this->verify($hostKey, $raw, is_string($hmac) ? $hmac : null, is_string($algo) ? $algo : null);

        $requestId = $request->header(self::HEADER_REQUEST_ID);
        $ts = $request->header(self::HEADER_TIMESTAMP);

        return [
            'ok' => $ok,
            'request_id' => is_string($requestId) && $requestId !== '' ? $requestId : null,
            'timestamp_ms' => $this->parseTimestampMs(is_string($ts) ? $ts : null),
        ];
    }

    private function normalizeAlgo(?string $algoHeader): ?string
    {
        $algo = strtolower(trim((string) $algoHeader));

        // WAHA docs say sha512 is used; treat empty as sha512 (pragmatic default).
        if ($algo === '') {
            $algo = 'sha512';
        }

        if (! in_array($algo, self::ALLOWED_ALGOS, true)) {
            return null;
        }

        return $algo;
    }

    private function extractSignature(string $headerValue): string
    {
        $sig = trim($headerValue);

        // allow formats like "sha512=<hex>" or "sha256=<hex>"
        if (str_contains($sig, '=')) {
            $parts = explode('=', $sig, 2);
            $sig = trim($parts[1] ?? '');
        }

        // keep only hex-ish characters (defensive)
        $sig = preg_replace('/[^a-fA-F0-9]/', '', $sig) ?? '';

        return $sig;
    }

    private function parseTimestampMs(?string $timestampHeader): ?int
    {
        if (! is_string($timestampHeader) || $timestampHeader === '') {
            return null;
        }

        if (! ctype_digit($timestampHeader)) {
            return null;
        }

        $v = (int) $timestampHeader;

        return $v > 0 ? $v : null;
    }
}
