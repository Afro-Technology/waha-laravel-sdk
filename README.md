# WAHA Laravel SDK

Laravel-oriented SDK for **WAHA** (WhatsApp HTTP API / Web WhatsApp gateway).  
It provides:

- **Multi-host** support (primary/secondary, etc.)
- Optional **session → host pinning** (Redis/DB/composite)
- **OpenAPI-driven** routing (reads WAHA OpenAPI spec and calls the generated client)
- **Convenience DX layer** (Facade + tag proxies + top-level aliases)
- **IDE autocomplete generation** (PHPDoc for Facade/proxies)
- **Debug tools**: `Waha::lastHttp()` / `Waha::lastHttpCurl()` + scoped debug

> Repository is meant to be installed into Laravel apps via Composer.

---

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [Host selection](#host-selection)
  - [Tag proxies](#tag-proxies)
  - [Top-level convenience methods](#top-level-convenience-methods)
  - [Response formats](#response-formats)
  - [Debug & last HTTP](#debug--last-http)
- [Webhooks](#webhooks)
  - [Configuring webhook_secret](#configuring-webhook_secret)
  - [Verifying signatures (WebhookVerifier)](#verifying-signatures-webhookverifier)
  - [Example route/controller](#example-routecontroller)
- [OpenAPI workflow](#openapi-workflow)
- [IDE Helper](#ide-helper)
- [Testing & CI](#testing--ci)
- [Versioning](#versioning)
- [License](#license)

---

## Installation

### Requirements

- PHP **8.1+**
- Laravel **10 / 11 / 12**
- Guzzle **7.5+**

### Composer install

```bash
composer require afro-technology/waha-laravel-sdk
```

Laravel package discovery is enabled by default.

### Publish config (recommended)

```bash
php artisan vendor:publish --provider="Vendor\Waha\WahaServiceProvider" --tag="waha-config"
```

This creates `config/waha.php`.

---

## Configuration

Main config: `config/waha.php`

### Minimal `.env`

```env
WAHA_DEFAULT_HOST=primary

WAHA_PRIMARY_URL=https://waha.example.com
WAHA_PRIMARY_ADMIN_KEY=YOUR_ADMIN_API_KEY
WAHA_API_KEY_HEADER=X-Api-Key

WAHA_PRIMARY_DEFAULT_SESSION=default
```

### Hosts

Configure one or more hosts under `waha.hosts`:

```php
'hosts' => [
  'primary' => [
    'base_url' => env('WAHA_PRIMARY_URL', 'http://localhost:3000'),
    'api_key_header' => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
    'admin_api_key' => env('WAHA_PRIMARY_ADMIN_KEY'),
    'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),

    // Used for webhook signature verification (HMAC)
    'webhook_secret' => env('WAHA_PRIMARY_WEBHOOK_SECRET'),

    // admin_fallback | strict_session_key
    'mode' => env('WAHA_PRIMARY_MODE', 'admin_fallback'),

    // Optional: per-session keys (newer WAHA versions)
    'session_keys' => [
      // 'default' => env('WAHA_PRIMARY_DEFAULT_SESSION_KEY'),
    ],
  ],
],
```
### Host authentication: `admin_api_key` vs `session_keys`

WAHA can be protected either with a **single admin key** (simplest) or with **per-session keys** (safer when you want to scope access).

- **`admin_api_key`**: one key for the whole WAHA instance. The SDK sends it as `X-Api-Key` (or `api_key_header`).  
  Use this for typical server-to-server setups where your application fully trusts itself.

- **`session_keys`**: a map of `session => key`. Useful when you run multiple WhatsApp sessions and want to isolate them (e.g., different tenants / orgs) so one leaked key cannot control other sessions.

- **`mode`** controls how the SDK chooses a key:
  - **`admin_fallback`** (default): if a session key exists for the resolved session, use it; otherwise fall back to `admin_api_key`.
  - **`strict_session_key`**: require a session key for the session; if missing, the request fails instead of silently using the admin key.

⚠️ Note: the OpenAPI-generated client currently authenticates with the host’s `admin_api_key` (because the generator config supports one API key).  
Per-session keys are used by the internal `WahaHttpClient` / `ApiKeyProvider` layer (useful for custom calls and for future extensions).


### Debug

```env
WAHA_DEBUG=false
WAHA_DEBUG_MAX_BODY_KB=64
WAHA_DEBUG_LOG_CHANNEL=stack
```

- Last call capture (`lastHttp/lastHttpCurl`) is stored regardless of logging.
- When `WAHA_DEBUG=true`, requests/responses are also logged (masked + truncated).


### Registry, routing, and pin store (multi-host / “session → host” mapping)

This SDK can work in a simple **single-host** setup (no routing), but it also supports **multi-host** deployments where a *session* must consistently “stick” to the same WAHA host.

These three config blocks control that behavior:

#### `registry`
Where host definitions come from.

- `registry.driver=config` (default): hosts are read from `config/waha.php` (`waha.hosts.*`).
- `registry.driver=db`: hosts are stored in package tables (publish + run migrations). Use this if you want to manage hosts from an admin UI or the database.

#### `routing`
How the SDK chooses a host when you call methods *without* explicitly selecting one.

- `routing.driver=none` (default): no automatic routing. You call `Waha::host('primary')...` or rely on `waha.default_host`.
- `routing.driver=pin`: resolves **session → host** using the pin store. This is the recommended mode if you run **multiple WAHA nodes** and want stable routing.

#### `pin_store`
Where **session → host** mappings are stored (used by `routing.driver=pin`).

- `pin_store.driver=auto` (default): prefers `composite` when possible, else falls back to `redis`, else `db`.
- `pin_store.driver=composite`: write-through **redis + db**, read redis-first then db (best of both worlds).
- `pin_store.driver=redis`: fastest, but you may lose mappings if redis is cleared (unless you persist it).
- `pin_store.driver=db`: durable, but slower.

Other knobs:

- `pin_store.ttl_seconds`: optional TTL for mappings (`0` = no TTL).
- `pin_store.redis_connection`: which Laravel redis connection to use.

> Typical setup for production multi-host:
> - `routing.driver=pin`
> - `pin_store.driver=composite` (or `auto`)
> - redis enabled + migrations applied


### Response format

```env
WAHA_RESPONSE_FORMAT=model   # model|array|json
```

- `model`: generated OpenAPI model objects (default)
- `array`: normalized PHP arrays
- `json`: normalized array then `json_encode`

---

## Quick Start

```php
use Vendor\Waha\Facades\Waha;

$msg = Waha::sendText(
    chatId: '905xxxxxxxxx@c.us',
    text: 'Hello from WAHA!'
);
```

Equivalent via tag proxy (tag names come from the OpenAPI spec):

```php
$msg = Waha::chatting()->sendText(
    chatId: '905xxxxxxxxx@c.us',
    text: 'Hello via tag proxy!'
);
```

---

## Usage

### Host selection

```php
Waha::host('primary')->sendText(chatId: '905...@c.us', text: 'via primary');

Waha::host('secondary')->sendText(chatId: '905...@c.us', text: 'via secondary');
```

If you don’t call `host()`, `waha.default_host` is used.

### Tag proxies

Tags are exposed as methods (normalized to lowerCamel). Example:

```php
// "chatting" tag
$msg = Waha::chatting()->sendText(chatId: '905...@c.us', text: 'hi');

// "sessions" tag (example)
$status = Waha::sessions()->list();
```

### Top-level convenience methods

Some operations are also exposed at the top level (only when their aliases are unique globally):

```php
$msg = Waha::sendText(chatId: '905...@c.us', text: 'Convenience');
```

If an alias is not available at the top level (collision), use the tag:

```php
$msg = Waha::chatting()->sendText(...);
```

### Response formats

Global via `.env` (`WAHA_RESPONSE_FORMAT`) or per chain:

```php
$arr = Waha::asArray()->sendText(chatId:'905...@c.us', text:'array result');

$json = Waha::asJson()->sendText(chatId:'905...@c.us', text:'json result');

$model = Waha::asModel()->sendText(chatId:'905...@c.us', text:'model result');
```

Works with tag proxies too:

```php
$arr = Waha::chatting()->asArray()->sendText(...);
```

### Debug & last HTTP

Read the last captured request/response:

```php
Waha::sendText(chatId:'905...@c.us', text:'Hello');

$last = Waha::lastHttp();      // array with request/response/error
$curl = Waha::lastHttpCurl();  // curl string (if available)
```

Enable debug only for the next call:

```php
Waha::debug()->sendText(chatId:'905...@c.us', text:'debug for this call only');
```

Scoped debug:

```php
Waha::withDebug(function () {
    Waha::sendText(chatId:'905...@c.us', text:'debug scope 1');
    Waha::chatting()->sendText(chatId:'905...@c.us', text:'debug scope 2');
});
```

---

## Webhooks

WAHA can deliver inbound events (messages, status updates, etc.) to your Laravel app via HTTP webhooks.

### Configuring webhook_secret

This SDK expects a shared secret per host under:

```php
config('waha.hosts.<hostKey>.webhook_secret')
```

Example `.env`:

```env
WAHA_PRIMARY_WEBHOOK_SECRET=super-long-random-secret
```

**You must configure the same secret in WAHA** (how/where depends on your WAHA deployment).

### Verifying signatures (WebhookVerifier)

The package ships with:

- `Vendor\Waha\Security\WebhookVerifier`

It verifies an **HMAC signature** of the raw request body using the host’s `webhook_secret`.

Important details:

- The verifier accepts signature formats:
  - `sha256=<hex>` (common)
  - `<hex>` (raw)
- Header name is WAHA/deployment specific. Many setups use something like `X-Waha-Signature`.

### Example route/controller

**routes/api.php**

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Vendor\Waha\Security\WebhookVerifier;

Route::post('/webhooks/waha/{hostKey}', function (Request $request, string $hostKey, WebhookVerifier $verifier) {
    $raw = $request->getContent();

    // Adjust header name to match your WAHA webhook configuration
    $signature = $request->header('X-Waha-Signature');

    if (!$verifier->verify($hostKey, $raw, $signature)) {
        abort(401, 'Invalid webhook signature');
    }

    $payload = $request->json()->all(); // or $request->all()

    // WAHA payload structure depends on your deployment.
    // Inspect once with dd($payload), then branch by event type.
    $event = $payload['event'] ?? null;

    if ($event === 'message') {
        $message = $payload['payload'] ?? $payload;
        // persist message, download media, trigger workflows, etc.
    }

    return response()->json(['ok' => true]);
});
```

**Security note:** always verify signature *before* processing, and treat payload as untrusted input.

---

## OpenAPI workflow

This SDK uses WAHA’s OpenAPI spec to route calls and generate the underlying client.

Common commands (provided by this package):

- Fetch spec:
  ```bash
  php artisan waha:openapi:fetch
  ```

- Generate OpenAPI client (uses configured generator driver):
  ```bash
  php artisan waha:openapi:generate
  ```

- Update (fetch + generate + ide helper, depending on your setup):
  ```bash
  php artisan waha:openapi:update
  ```

Configuration lives under `config/waha.php` → `openapi`.

---

## IDE Helper

This package can generate PHPDoc for:
- Facade (`Waha`)
- Proxies (host chain, tag proxies, top-level operations)

Run:

```bash
php artisan waha:openapi:ide-helper
```

Then re-index your IDE if autocompletion does not update.

---

## Testing & CI

Local tests:

```bash
composer test
```

Recommended additions for public repos:
- Pint (formatting)
- PHPStan (static analysis)
- GitHub Actions workflow running:
  - `composer install`
  - `composer test`
  - `vendor/bin/pint --test`
  - `vendor/bin/phpstan analyse`

(Exact setup depends on your repo preferences.)

---

## Versioning

Use **SemVer**:

- `MAJOR`: breaking API changes
- `MINOR`: backward-compatible features
- `PATCH`: backward-compatible fixes

Tags are important for Packagist and for stable dependency resolution.

---

## License

MIT
