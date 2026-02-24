<?php

namespace Vendor\Waha\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Vendor\Waha\Contracts\ApiKeyProvider;
use Vendor\Waha\Contracts\HostRegistry;
use Vendor\Waha\Contracts\SessionRouter;
use Vendor\Waha\Support\ApiKeyMissingException;

class WahaHttpClient
{
    public function __construct(
        private HostRegistry $hosts,
        private ApiKeyProvider $keys,
        private SessionRouter $router
    ) {}

    /**
     * Perform a JSON request against WAHA.
     *
     * @param  string  $method  HTTP method
     * @param  string  $path  Path like /api/sendText
     * @param  array<string,mixed>  $json  JSON body
     * @param  string|null  $hostKey  Optional host key
     * @param  string|null  $session  Optional session name (used for routing + session-scoped key selection)
     * @return array<string,mixed>
     */
    public function json(string $method, string $path, array $json = [], ?string $hostKey = null, ?string $session = null): array
    {
        $resolvedHost = $this->router->resolveHostKey($hostKey, $session);
        $cfg = $this->hosts->get($resolvedHost);

        $base = rtrim($cfg['base_url'], '/');
        $url = $base.$path;

        $headerName = $this->keys->headerName($resolvedHost);

        $apiKey = null;
        if ($session) {
            $apiKey = $this->keys->sessionKey($resolvedHost, $session);
        }
        if (! $apiKey) {
            $mode = $this->keys->mode($resolvedHost);
            if ($mode === 'strict_session_key' && $session) {
                throw new ApiKeyMissingException("Session-scoped API key is required for session '{$session}' on host '{$resolvedHost}'.");
            }
            $apiKey = $this->keys->adminKey($resolvedHost);
        }
        if (! $apiKey) {
            throw new ApiKeyMissingException("No API key configured for host '{$resolvedHost}'.");
        }

        $client = new Client([
            'http_errors' => false,
            'timeout' => 30,
        ]);

        try {
            $resp = $client->request($method, $url, [
                'headers' => [
                    $headerName => $apiKey,
                    'Accept' => 'application/json',
                ],
                'json' => $json,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException("WAHA request failed: {$e->getMessage()}", 0, $e);
        }

        $body = (string) $resp->getBody();
        $data = $body !== '' ? json_decode($body, true) : [];
        if (! is_array($data)) {
            $data = ['raw' => $body];
        }

        $data['_http'] = [
            'status' => $resp->getStatusCode(),
            'url' => $url,
        ];

        return $data;
    }
}
