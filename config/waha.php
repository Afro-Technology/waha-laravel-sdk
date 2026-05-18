<?php

use AfroTechnology\Waha\Support\PackagePath;

return [
    /*
    |--------------------------------------------------------------------------
    | Registry
    |--------------------------------------------------------------------------
    | Where hosts and credentials come from:
    | - config: use this config file
    | - db: use package tables (published migrations)
    | - custom: resolve HostRegistry / ApiKeyProvider from the Laravel container
    */
    'registry' => [
        'driver' => env('WAHA_REGISTRY_DRIVER', 'config'), // config|db|custom

        'custom' => [
            // FQCN implementing AfroTechnology\Waha\Contracts\HostRegistry.
            'host_registry' => env('WAHA_HOST_REGISTRY'),

            // Optional FQCN implementing AfroTechnology\Waha\Contracts\ApiKeyProvider.
            // When omitted, ConfigApiKeyProvider reads keys from the custom HostRegistry.
            'api_key_provider' => env('WAHA_API_KEY_PROVIDER'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    | Controls how SDK resolves a host for a given session.
    | - none: you must choose a host explicitly (or default_host)
    | - pin: resolve session -> host via pin store (writes can happen on webhook)
    */
    'routing' => [
        'driver' => env('WAHA_ROUTING_DRIVER', 'none'), // none|pin
    ],

    /*
    |--------------------------------------------------------------------------
    | Pin Store
    |--------------------------------------------------------------------------
    | Where session->host mapping is stored.
    | - auto: prefer composite (redis+db) when possible, else redis, else db
    | - composite: write-through (redis+db), read redis then db
    | - redis: redis only
    | - db: database only
    */
    'pin_store' => [
        'driver' => env('WAHA_PIN_STORE_DRIVER', 'auto'), // auto|composite|redis|db
        'ttl_seconds' => (int) env('WAHA_PIN_TTL_SECONDS', 0), // 0 = no ttl
        'redis_connection' => env('WAHA_PIN_REDIS_CONNECTION', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Host
    |--------------------------------------------------------------------------
    */
    'default_host' => env('WAHA_DEFAULT_HOST', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Hosts (Config Registry)
    |--------------------------------------------------------------------------
    */
    'hosts' => [
        'primary' => [
            'base_url' => env('WAHA_PRIMARY_URL', 'http://localhost:3000'),
            'api_key_header' => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
            'admin_api_key' => env('WAHA_PRIMARY_ADMIN_KEY'),
            'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),
            'webhook_secret' => env('WAHA_PRIMARY_WEBHOOK_SECRET'),
            'webhooks' => [
                // host override
                'require_hmac' => env('WAHA_HOST_PRIMARY_WEBHOOK_REQUIRE_HMAC', null), // null = inherit
                'store_events' => env('WAHA_HOST_PRIMARY_WEBHOOK_STORE_EVENTS', null),
            ],
            'mode' => env('WAHA_PRIMARY_MODE', 'admin_fallback'), // admin_fallback|strict_session_key

            // Optional: session-scoped keys (WAHA 2026.1+)
            'session_keys' => [
                // 'default' => env('WAHA_PRIMARY_DEFAULT_SESSION_KEY'),
                // 'org_123' => env('WAHA_PRIMARY_ORG_123_SESSION_KEY'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAPI
    |--------------------------------------------------------------------------
    */
    'openapi' => [
        // Default URL for fetching spec (override with --url)
        'url' => env('WAHA_OPENAPI_URL', ''),

        // Optional Basic Auth for Swagger/OpenAPI endpoints (when protected)
        'basic_auth' => [
            'username' => env('WAHA_SWAGGER_USERNAME', ''),
            'password' => env('WAHA_SWAGGER_PASSWORD', ''),
        ],

        'timeout_seconds' => (int) env('WAHA_OPENAPI_TIMEOUT', 30),

        // Default locations used by artisan commands (override with --spec/--out)
        'spec_path' => PackagePath::path('resources/openapi/openapi.json'),
        'generated_path' => PackagePath::path('src'),

        'generator' => [
            // Preferred default: local first. 'auto' tries npx -> jar -> binary -> docker.
            'driver' => env('WAHA_OPENAPI_GENERATOR_DRIVER', 'auto'), // auto|npx|jar|binary|docker

            // Docker settings
            'docker_image' => env('WAHA_OPENAPI_GENERATOR_IMAGE', 'openapitools/openapi-generator-cli:v7.6.0'),

            // Local binary (if installed)
            'binary' => env('WAHA_OPENAPI_GENERATOR_BINARY', 'openapi-generator-cli'),

            // Java jar path (if you prefer jar driver)
            'jar' => env('WAHA_OPENAPI_GENERATOR_JAR', ''),

            'timeout_seconds' => (int) env('WAHA_OPENAPI_GENERATOR_TIMEOUT', 300),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    */
    'debug' => [
        'enabled' => (bool) env('WAHA_DEBUG', false),
        // request/response body truncation limit
        'max_body_kb' => (int) env('WAHA_DEBUG_MAX_BODY_KB', 64),
        // Laravel log channel
        'log_channel' => env('WAHA_DEBUG_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response formatting
    |--------------------------------------------------------------------------
    */
    'responses' => [
        'format' => env('WAHA_RESPONSE_FORMAT', 'model'),

        // Used only when format=json
        'json_flags' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ],

    'webhooks' => [
        // Global defaults
        'enabled' => env('WAHA_WEBHOOKS_ENABLED', true),

        // If true: missing HMAC => reject. If false: accept without verification.
        'require_hmac' => env('WAHA_WEBHOOKS_REQUIRE_HMAC', true),

        // Timestamp freshness window (ms). Example: 5 minutes.
        'max_clock_skew_ms' => (int) env('WAHA_WEBHOOKS_MAX_CLOCK_SKEW_MS', 300000),

        // Replay protection (request-id dedup)
        'replay' => [
            'enabled' => env('WAHA_WEBHOOKS_REPLAY_ENABLED', true),
            'ttl_seconds' => (int) env('WAHA_WEBHOOKS_REPLAY_TTL_SECONDS', 900), // 15 min
            'cache_prefix' => env('WAHA_WEBHOOKS_REPLAY_CACHE_PREFIX', 'waha:webhook:'),
        ],

        // Event store
        'store' => [
            'enabled' => env('WAHA_WEBHOOKS_STORE_ENABLED', false),
            'store_raw' => env('WAHA_WEBHOOKS_STORE_RAW', true),
            'retention_days' => (int) env('WAHA_WEBHOOKS_STORE_RETENTION_DAYS', 7),
        ],

        // Route
        'route' => [
            'prefix' => env('WAHA_WEBHOOKS_ROUTE_PREFIX', '/webhooks/waha'),
            'middleware' => ['api'], // keep stateless
        ],

        'processing' => [
            // 'sync' or 'queue'. Keep sync as the zero-worker default.
            'mode' => env('WAHA_WEBHOOKS_PROCESSING_MODE', 'sync'),

            // Only used when mode=queue
            'queue_connection' => env('WAHA_WEBHOOKS_QUEUE_CONNECTION', null),
            'queue_name' => env('WAHA_WEBHOOKS_QUEUE_NAME', 'default'),
        ],

        /**
         * Optional handler mapping by event name.
         * If an event is not mapped, SDK will still fire the Laravel event.
         *
         * Example:
         * 'handlers' => [
         *   'message.any' => \App\Waha\Handlers\AnyMessageHandler::class,
         *   'message.created' => \App\Waha\Handlers\MessageCreatedHandler::class,
         * ],
         */
        'handlers' => [
            // eventName => FQCN
        ],
    ],
];
