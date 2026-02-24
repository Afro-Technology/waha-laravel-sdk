<?php

namespace Vendor\Waha\OpenApi;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vendor\Waha\Debug\WahaDebugManager;

final class GeneratedClientFactory
{
    /**
     * @param  array<string, array<string, mixed>>  $hostsConfig
     * @param  array<string, mixed>  $responsesConfig
     */
    public function __construct(
        private readonly array $hostsConfig,
        private readonly array $responsesConfig = [],
        private readonly ?WahaDebugManager $debug = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function responsesConfig(): array
    {
        return $this->responsesConfig;
    }

    public function defaultSession(string $hostKey): string
    {
        $host = $this->hostsConfig[$hostKey] ?? [];
        $def = $host['default_session'] ?? $host['defaultSession'] ?? 'default';

        return is_string($def) && $def !== '' ? $def : 'default';
    }

    public function makeTagApi(string $hostKey, string $tagName): object
    {
        $method = OpenApiRouter::normalizeTagToMethod($tagName);
        $classShort = ucfirst($method).'Api';

        $apiClass = "Vendor\\Waha\\Generated\\Api\\{$classShort}";
        $configClass = 'Vendor\\Waha\\Generated\\Configuration';

        if (! class_exists($apiClass) || ! class_exists($configClass)) {
            throw new \RuntimeException(
                'Generated OpenAPI client not found. Run: php artisan waha:openapi:generate (driver=npx recommended).'
            );
        }

        $host = $this->hostsConfig[$hostKey] ?? null;
        if (! $host) {
            throw new \RuntimeException("WAHA host '{$hostKey}' not configured.");
        }

        $baseUrl = rtrim((string) ($host['base_url'] ?? $host['url'] ?? ''), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException("WAHA host '{$hostKey}' base_url/url is empty.");
        }

        $cfg = new $configClass;
        $cfg->setHost($baseUrl);

        // --- API KEY INJECTION (generator-agnostic) ---
        // openapi-generator PHP uses the *header name* as the apiKey identifier (e.g. 'X-Api-Key').
        $headerName = (string) ($host['api_key_header'] ?? 'X-Api-Key');
        $apiKey = $host['admin_api_key'] ?? $host['token'] ?? null;

        if (is_string($apiKey) && $apiKey !== '') {
            // Primary path (matches generated code):
            $cfg->setApiKey($headerName, $apiKey);

            // Extra fallback keys (harmless if unused by the generated client):
            $cfg->setApiKey('api_key', $apiKey);
            $cfg->setAccessToken($apiKey);
        }

        $timeout = (int) ($host['timeout_seconds'] ?? $host['timeout'] ?? 30);

        $httpClient = $this->makeHttpClient($timeout);

        return new $apiClass($httpClient, $cfg);
    }

    /**
     * HTTP client for raw fallback requests.
     *
     * IMPORTANT: uses the same middleware as generated client so lastHttp/lastHttpCurl
     * are always captured consistently.
     */
    public function makeRawHttpClient(string $hostKey): Client
    {
        $host = $this->hostsConfig[$hostKey] ?? null;
        if (! is_array($host)) {
            throw new \RuntimeException("WAHA host '{$hostKey}' not configured.");
        }

        $timeout = (int) ($host['timeout_seconds'] ?? $host['timeout'] ?? 30);

        return $this->makeHttpClient($timeout);
    }

    private function makeHttpClient(int $timeoutSeconds): Client
    {
        $handler = null;

        // Even when debug logging is disabled, we still attach the middleware so we can
        // reliably capture the *last* HTTP call for Waha::lastHttp()/lastHttpCurl().
        if ($this->debug) {
            $handler = HandlerStack::create();

            $handler->push(function (callable $handler) {
                return function (RequestInterface $request, array $options) use ($handler) {
                    $promise = $handler($request, $options);

                    return $promise->then(
                        function (ResponseInterface $response) use ($request) {
                            $this->captureHttp($request, $response, null);

                            return $response;
                        },
                        function ($reason) use ($request) {
                            $this->captureHttp($request, null, $reason);
                            throw $reason;
                        }
                    );
                };
            });
        }

        return new Client([
            'timeout' => $timeoutSeconds,
            'http_errors' => false,
            ...($handler ? ['handler' => $handler] : []),
        ]);
    }

    public function resolveGeneratedMethod(object $api, ?string $operationId, string $alias): string
    {
        $methods = get_class_methods($api);

        if ($operationId) {
            $opSuffix = $this->operationIdSuffix($operationId);
            $cand = $this->findSuffixMatch($methods, $opSuffix);
            if ($cand) {
                return $cand;
            }
        }

        $cand = $this->findSuffixMatch($methods, $alias);
        if ($cand) {
            return $cand;
        }

        if (in_array($alias, $methods, true)) {
            return $alias;
        }

        throw new \RuntimeException("Could not resolve generated method for alias '{$alias}' on ".get_class($api));
    }

    private function operationIdSuffix(string $operationId): string
    {
        if (str_contains($operationId, '_')) {
            return lcfirst(substr($operationId, strrpos($operationId, '_') + 1));
        }

        return lcfirst($operationId);
    }

    /**
     * @param  list<string>  $methods
     */
    private function findSuffixMatch(array $methods, string $suffix): ?string
    {
        $suffixLower = strtolower($suffix);

        foreach ($methods as $m) {
            if (str_ends_with(strtolower($m), $suffixLower)) {
                if (str_ends_with($m, 'WithHttpInfo')) {
                    continue;
                }
                if (str_ends_with($m, 'Async')) {
                    continue;
                }

                return $m;
            }
        }

        return null;
    }

    private function captureHttp(RequestInterface $request, ?ResponseInterface $response, mixed $reason): void
    {
        if (! $this->debug) {
            return;
        }

        $max = $this->debug->maxBodyBytes();

        $maskHeaders = function (array $headers): array {
            $out = [];
            foreach ($headers as $k => $vals) {
                $name = (string) $k;
                $lower = strtolower($name);

                $isSensitive = in_array($lower, [
                    'authorization',
                    'x-api-key',
                    'api-key',
                    'x-auth-token',
                    'x-access-token',
                ], true);

                if (! is_array($vals)) {
                    $vals = [$vals];
                }
                $outVals = [];
                foreach ($vals as $v) {
                    $sv = (string) $v;
                    if ($isSensitive) {
                        $outVals[] = '***';

                        continue;
                    }
                    // lightweight heuristic: mask Bearer tokens even if header name is odd.
                    if (str_starts_with(strtolower($sv), 'bearer ')) {
                        $outVals[] = 'Bearer ***';

                        continue;
                    }
                    $outVals[] = $sv;
                }
                $out[$name] = $outVals;
            }

            return $out;
        };

        $reqBody = (string) $request->getBody();
        if ($max > 0 && strlen($reqBody) > $max) {
            $reqBody = substr($reqBody, 0, $max).'...';
        }

        $respBody = null;
        $status = null;
        $respHeaders = null;

        if ($response) {
            $status = $response->getStatusCode();
            $respHeaders = $response->getHeaders();
            $respBody = (string) $response->getBody();
            if ($max > 0 && strlen($respBody) > $max) {
                $respBody = substr($respBody, 0, $max).'...';
            }
        }

        $entry = [
            'ts' => date('c'),
            'request' => [
                'method' => $request->getMethod(),
                'url' => (string) $request->getUri(),
                'headers' => $maskHeaders($request->getHeaders()),
                'body' => $reqBody !== '' ? $reqBody : null,
            ],
            'response' => $response ? [
                'status' => $status,
                'headers' => $respHeaders ? $maskHeaders($respHeaders) : null,
                'body' => $respBody,
            ] : null,
            'error' => $reason ? (is_object($reason) ? (get_class($reason).': '.($reason->getMessage() ?? '')) : (string) $reason) : null,
        ];

        $this->debug->store()->setLast($entry);

        // Optional logging
        if ($this->debug->enabled()) {
            try {
                Log::channel($this->debug->logChannel())->debug('WAHA HTTP', $entry);
            } catch (\Throwable $e) {
                // ignore logging failures
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function hostConfig(string $hostKey): array
    {
        $host = $this->hostsConfig[$hostKey] ?? null;
        if (! is_array($host)) {
            throw new \RuntimeException("WAHA host '{$hostKey}' not configured.");
        }

        return $host;
    }
}
