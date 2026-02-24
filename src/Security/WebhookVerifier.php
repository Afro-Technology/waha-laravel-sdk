<?php

namespace Vendor\Waha\Security;

use Vendor\Waha\Contracts\HostRegistry;

/**
 * Verifies WAHA webhook signature (HMAC).
 *
 * WAHA typically signs payload using a shared secret configured per session/host.
 * This verifier supports host-specific secrets.
 */
class WebhookVerifier
{
    public function __construct(private HostRegistry $hosts) {}

    /**
     * Verify request using raw body and signature header.
     *
     * @param  string  $hostKey  Host key (from route like /webhooks/waha/{hostKey})
     * @param  string  $rawBody  Raw request body
     * @param  string|null  $signature  Header value (e.g. X-Waha-Signature or similar)
     * @param  string  $algo  Hash algo, default sha256
     */
    public function verify(string $hostKey, string $rawBody, ?string $signature, string $algo = 'sha256'): bool
    {
        $secret = $this->hosts->get($hostKey)['webhook_secret'] ?? null;
        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac($algo, $rawBody, $secret);
        // allow formats like "sha256=...." or raw hex
        $sig = str_contains($signature, '=') ? explode('=', $signature, 2)[1] : $signature;

        return hash_equals($expected, $sig);
    }
}
