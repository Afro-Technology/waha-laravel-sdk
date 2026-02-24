<?php

namespace Vendor\Waha;

use Vendor\Waha\Debug\WahaDebugManager;
use Vendor\Waha\OpenApi\GeneratedClientFactory;
use Vendor\Waha\OpenApi\OpenApiRouter;
use Vendor\Waha\OpenApi\OpenApiSpecRepository;
use Vendor\Waha\OpenApi\WahaApiProxy;
use Vendor\Waha\OpenApi\WahaManagerProxy;

final class WahaManager
{
    private ?OpenApiRouter $router = null;

    private ?GeneratedClientFactory $clientFactory = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config, private readonly WahaDebugManager $debug) {}

    public function host(string $hostKey): WahaApiProxy
    {
        return new WahaApiProxy(
            router: $this->getRouter(),
            clientFactory: $this->getClientFactory(),
            hostKey: $hostKey,
            debug: $this->debug,
        );
    }

    /**
     * Enable debug for the next HTTP call, without losing access to host().
     *
     * Examples:
     *   Waha::debug()->sendText(...)
     *   Waha::debug()->host('primary')->chats()->getChats(...)
     */
    public function debug(): WahaManagerProxy
    {
        return new WahaManagerProxy($this, true, null);
    }

    /** Force response normalization to arrays for this call chain. */
    public function asArray(): WahaManagerProxy
    {
        return new WahaManagerProxy($this, false, 'array');
    }

    /** Force response normalization to JSON strings for this call chain. */
    public function asJson(): WahaManagerProxy
    {
        return new WahaManagerProxy($this, false, 'json');
    }

    /** Force response to be returned as generated OpenAPI model objects (default). */
    public function asModel(): WahaManagerProxy
    {
        return new WahaManagerProxy($this, false, 'model');
    }

    /** Read config values (internal helper for proxies). */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $default = $this->config['default_host'] ?? 'primary';
        $proxy = $this->host($default);

        return $proxy->{$name}(...$arguments);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastHttp(): ?array
    {
        return $this->debug->store()->last();
    }

    public function lastHttpCurl(): ?string
    {
        return $this->debug->store()->lastCurl();
    }

    /**
     * Enable debug only for the duration of the callback.
     */
    public function withDebug(callable $fn): mixed
    {
        $this->debug->pushEnabled(true);
        try {
            return $fn();
        } finally {
            $this->debug->popEnabled();
        }
    }

    private function getClientFactory(): GeneratedClientFactory
    {
        if ($this->clientFactory) {
            return $this->clientFactory;
        }

        // Single source of truth for HTTP timeouts: config('waha.openapi.timeout_seconds').
        // Hosts may optionally override via hosts[...].timeout_seconds but we inject the global
        // default to avoid accidental regressions.
        $hosts = $this->config['hosts'] ?? [];
        $globalTimeout = (int) ($this->config['openapi']['timeout_seconds'] ?? 30);
        if (is_array($hosts)) {
            foreach ($hosts as $k => $h) {
                if (! is_array($h)) {
                    continue;
                }
                if (! array_key_exists('timeout_seconds', $h) && ! array_key_exists('timeout', $h)) {
                    $h['timeout_seconds'] = $globalTimeout;
                    $hosts[$k] = $h;
                }
            }
        }

        return $this->clientFactory = new GeneratedClientFactory(
            hostsConfig: is_array($hosts) ? $hosts : [],
            responsesConfig: $this->config['responses'] ?? [],
            debug: $this->debug,
        );
    }

    private function getRouter(): OpenApiRouter
    {
        if ($this->router) {
            return $this->router;
        }

        // Prefer current config keys; keep legacy fallbacks.
        $specPath = $this->config['openapi']['spec_path']
            ?? ($this->config['openapi']['paths']['spec'] ?? null);

        if (! $specPath) {
            throw new \RuntimeException(
                'WAHA OpenAPI spec path not configured. Set config(waha.openapi.spec_path) or config(waha.openapi.paths.spec).'
            );
        }

        // Allow relative paths in config.
        if (! str_starts_with($specPath, '/')) {
            $specPath = base_path($specPath);
        }

        $repo = new OpenApiSpecRepository($specPath);
        $spec = $repo->load();

        return $this->router = new OpenApiRouter($spec);
    }
}
