# WAHA Laravel SDK

WAHA (WhatsApp HTTP API / Web WhatsApp gateway) için Laravel odaklı bir SDK.  
Şunları sağlar:

- **Multi-host** desteği (primary/secondary vb.)
- Opsiyonel **session → host pinleme** (redis/db/composite)
- **OpenAPI tabanlı** yönlendirme (WAHA OpenAPI spec’ini okuyup generated client üzerinden çağırır)
- **Kolay kullanım katmanı** (Facade + tag proxy’ler + top-level alias’lar)
- **IDE autocomplete üretimi** (Facade/proxy PHPDoc)
- **Debug araçları**: `Waha::lastHttp()` / `Waha::lastHttpCurl()` + scope debug

> Repo, Laravel projelerine Composer ile eklenmek üzere tasarlanmıştır.

---

## İçindekiler

- [Kurulum](#kurulum)
- [Konfigürasyon](#konfigürasyon)
- [Hızlı Başlangıç](#hızlı-başlangıç)
- [Kullanım](#kullanım)
  - [Host seçimi](#host-seçimi)
  - [Tag proxy kullanımı](#tag-proxy-kullanımı)
  - [Top-level convenience method’lar](#top-level-convenience-methodlar)
  - [Response formatları](#response-formatları)
  - [Debug & son HTTP çağrısı](#debug--son-http-çağrısı)
- [Webhook](#webhook)
  - [webhook_secret ayarı](#webhook_secret-ayarı)
  - [İmza doğrulama (WebhookVerifier)](#imza-doğrulama-webhookverifier)
  - [Örnek route/controller](#örnek-routecontroller)
- [OpenAPI süreci](#openapi-süreci)
- [IDE Helper](#ide-helper)
- [Test & CI](#test--ci)
- [Versiyonlama](#versiyonlama)
- [Lisans](#lisans)

---

## Kurulum

### Gereksinimler

- PHP **8.1+**
- Laravel **10 / 11 / 12**
- Guzzle **7.5+**

### Composer ile kurulum

```bash
composer require afro-technology/waha-laravel-sdk
```

Laravel auto-discovery varsayılan olarak açık.

### Config publish (önerilir)

```bash
php artisan vendor:publish --provider="Vendor\Waha\WahaServiceProvider" --tag="waha-config"
```

`config/waha.php` oluşur.

---

## Konfigürasyon

Ana config: `config/waha.php`

### Minimal `.env`

```env
WAHA_DEFAULT_HOST=primary

WAHA_PRIMARY_URL=https://waha.example.com
WAHA_PRIMARY_ADMIN_KEY=YOUR_ADMIN_API_KEY
WAHA_API_KEY_HEADER=X-Api-Key

WAHA_PRIMARY_DEFAULT_SESSION=default
```

### Hosts

Bir veya daha fazla WAHA instance’ı tanımlayın:

```php
'hosts' => [
  'primary' => [
    'base_url' => env('WAHA_PRIMARY_URL', 'http://localhost:3000'),
    'api_key_header' => env('WAHA_API_KEY_HEADER', 'X-Api-Key'),
    'admin_api_key' => env('WAHA_PRIMARY_ADMIN_KEY'),
    'default_session' => env('WAHA_PRIMARY_DEFAULT_SESSION', 'default'),

    // webhook imzası doğrulama için secret
    'webhook_secret' => env('WAHA_PRIMARY_WEBHOOK_SECRET'),

    // admin_fallback | strict_session_key
    'mode' => env('WAHA_PRIMARY_MODE', 'admin_fallback'),

    // Opsiyonel: session bazlı key (yeni WAHA versiyonları)
    'session_keys' => [
      // 'default' => env('WAHA_PRIMARY_DEFAULT_SESSION_KEY'),
    ],
  ],
],
```
### Host kimlik doğrulaması: `admin_api_key` ve `session_keys`

WAHA erişimini iki şekilde kısıtlayabilirsin: **tek bir admin anahtarı** (en basit) veya **session bazlı anahtarlar** (çok daha güvenli / izolasyonlu).

- **`admin_api_key`**: tüm WAHA instance’ı için tek anahtar. SDK bunu `X-Api-Key` (veya `api_key_header`) header’ı olarak gönderir.  
  Sunucu-sunucu senaryolarında, uygulamanın kendisine tamamen güvendiğin durumlarda ideal.

- **`session_keys`**: `session => key` haritası. Birden fazla WhatsApp session’ı (tenant/organizasyon) çalıştırıyorsan, erişimi izole etmek için mantıklı. Bir anahtar sızarsa sadece ilgili session etkilenir.

- **`mode`** SDK’nın hangi anahtarı seçeceğini belirler:
  - **`admin_fallback`** (varsayılan): ilgili session için `session_keys` varsa onu kullanır, yoksa `admin_api_key`’e düşer.
  - **`strict_session_key`**: ilgili session için session anahtarı zorunludur; yoksa “sessizce admin key kullanmak” yerine istek başarısız olur.

⚠️ Not: OpenAPI-generated client şu an host’un `admin_api_key` değerini kullanarak authenticate olur (generator config’i tek API key’e uygun).  
Session bazlı anahtar seçimi ise paket içindeki `WahaHttpClient` / `ApiKeyProvider` katmanında uygulanır (custom HTTP çağrıları ve ilerideki genişletmeler için).


### Debug

```env
WAHA_DEBUG=false
WAHA_DEBUG_MAX_BODY_KB=64
WAHA_DEBUG_LOG_CHANNEL=stack
```

- `lastHttp/lastHttpCurl` her zaman “son çağrı”yı tutar.
- `WAHA_DEBUG=true` olursa ayrıca log’a basar (maskelenmiş + truncate edilmiş).


### Registry, routing ve pin store (multi-host / “session → host” eşlemesi)

SDK basit bir **tek host** kurulumunda (routing kapalı) sorunsuz çalışır; ama **multi-host** kurulumlarda bir *session*’ın her zaman aynı WAHA host’una “yapışması” gerekir.

Bu davranışı üç config bloğu yönetir:

#### `registry`
Host tanımlarının nereden geldiğini belirler.

- `registry.driver=config` (varsayılan): host’lar `config/waha.php` içinden okunur (`waha.hosts.*`).
- `registry.driver=db`: host’lar paket tablolarında saklanır (migration publish + migrate). Host’ları bir admin panelinden / DB’den yönetmek istiyorsan doğru seçim.

#### `routing`
Host’u **otomatik** seçme mantığı (sen `host()` çağırmadığında).

- `routing.driver=none` (varsayılan): otomatik routing yok. `Waha::host('primary')...` ile çağırırsın veya `waha.default_host` devreye girer.
- `routing.driver=pin`: **session → host** çözümlemesini pin store üzerinden yapar. **Birden fazla WAHA node** çalıştırıyorsan ve stabil routing istiyorsan önerilen mod budur.

#### `pin_store`
**session → host** eşlemesinin nerede tutulacağını belirler (`routing.driver=pin` kullanırken devreye girer).

- `pin_store.driver=auto` (varsayılan): mümkünse `composite`, değilse `redis`, o da yoksa `db`.
- `pin_store.driver=composite`: write-through **redis + db**, okuma redis-öncelikli sonra db (en dengeli seçenek).
- `pin_store.driver=redis`: en hızlısı; ama redis temizlenirse mapping kaybolabilir (persist yoksa).
- `pin_store.driver=db`: kalıcıdır ama daha yavaştır.

Diğer ayarlar:

- `pin_store.ttl_seconds`: mapping için TTL (`0` = sınırsız).
- `pin_store.redis_connection`: kullanılacak Laravel redis bağlantısı.

> Production multi-host için tipik kurulum:
> - `routing.driver=pin`
> - `pin_store.driver=composite` (veya `auto`)
> - redis açık + migration’lar uygulanmış


### Response format

```env
WAHA_RESPONSE_FORMAT=model   # model|array|json
```

- `model`: generated OpenAPI model object’leri (default)
- `array`: normalize PHP array
- `json`: normalize array + `json_encode`

---

## Hızlı Başlangıç

```php
use Vendor\Waha\Facades\Waha;

$msg = Waha::sendText(
    chatId: '905xxxxxxxxx@c.us',
    text: 'Merhaba!'
);
```

Tag proxy ile aynı çağrı:

```php
$msg = Waha::chatting()->sendText(
    chatId: '905xxxxxxxxx@c.us',
    text: 'Merhaba (tag proxy)!'
);
```

---

## Kullanım

### Host seçimi

```php
Waha::host('primary')->sendText(chatId: '905...@c.us', text: 'primary');

Waha::host('secondary')->sendText(chatId: '905...@c.us', text: 'secondary');
```

`host()` çağırmazsan `waha.default_host` kullanılır.

### Tag proxy kullanımı

Tag isimleri OpenAPI spec’ten gelir ve lowerCamel olarak method’a çevrilir:

```php
// "chatting" tag
$msg = Waha::chatting()->sendText(chatId: '905...@c.us', text: 'selam');

// "sessions" tag (örnek)
$status = Waha::sessions()->list();
```

### Top-level convenience method’lar

Bazı operasyonlar top-level olarak da expose edilir (alias globalde tekilse):

```php
$msg = Waha::sendText(chatId: '905...@c.us', text: 'kolay kullanım');
```

Alias çakışıyorsa top-level’de görünmez; ilgili tag ile çağır:

```php
$msg = Waha::chatting()->sendText(...);
```

### Response formatları

Global olarak `.env` ile veya çağrı bazında override edebilirsin:

```php
$arr = Waha::asArray()->sendText(chatId:'905...@c.us', text:'array sonuç');

$json = Waha::asJson()->sendText(chatId:'905...@c.us', text:'json sonuç');

$model = Waha::asModel()->sendText(chatId:'905...@c.us', text:'model sonuç');
```

Tag proxy zincirinde de çalışır:

```php
$arr = Waha::chatting()->asArray()->sendText(...);
```

### Debug & son HTTP çağrısı

Son çağrıyı oku:

```php
Waha::sendText(chatId:'905...@c.us', text:'Hello');

$last = Waha::lastHttp();      // request/response/error array
$curl = Waha::lastHttpCurl();  // curl string (varsa)
```

Sadece tek çağrı için debug:

```php
Waha::debug()->sendText(chatId:'905...@c.us', text:'tek çağrı debug');
```

Scope debug:

```php
Waha::withDebug(function () {
    Waha::sendText(chatId:'905...@c.us', text:'scope 1');
    Waha::chatting()->sendText(chatId:'905...@c.us', text:'scope 2');
});
```

---

## Webhook

WAHA, gelen mesajlar / event’ler için webhook ile sistemine HTTP istekleri atabilir.

### webhook_secret ayarı

SDK, host bazında şu path’ten secret okur:

```php
config('waha.hosts.<hostKey>.webhook_secret')
```

Örnek `.env`:

```env
WAHA_PRIMARY_WEBHOOK_SECRET=super-long-random-secret
```

**Aynı secret’ı WAHA tarafında da tanımlamak zorundasın** (WAHA’nın kurulumuna göre yeri değişebilir).

### İmza doğrulama (WebhookVerifier)

Pakette şunu bulacaksın:

- `Vendor\Waha\Security\WebhookVerifier`

Bu sınıf, request’in **raw body**’sini alır ve host’un `webhook_secret` değeriyle HMAC üretip imzayı doğrular.

Notlar:

- İmza header formatı şu olabilir:
  - `sha256=<hex>`
  - `<hex>`
- Header adı WAHA kurulumuna göre değişebilir. Birçok setup’ta benzeri kullanılır: `X-Waha-Signature`.

### Örnek route/controller

**routes/api.php**

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Vendor\Waha\Security\WebhookVerifier;

Route::post('/webhooks/waha/{hostKey}', function (Request $request, string $hostKey, WebhookVerifier $verifier) {
    $raw = $request->getContent();

    // Header adını WAHA webhook ayarına göre değiştir
    $signature = $request->header('X-Waha-Signature');

    if (!$verifier->verify($hostKey, $raw, $signature)) {
        abort(401, 'Invalid webhook signature');
    }

    $payload = $request->json()->all();

    // WAHA payload yapısı deploy’a göre değişebilir.
    // İlk kurulumda dd($payload) ile şekli gör, sonra event bazlı yönlendir:
    $event = $payload['event'] ?? null;

    if ($event === 'message') {
        $message = $payload['payload'] ?? $payload;
        // db’ye yaz, media indir, workflow tetikle vb.
    }

    return response()->json(['ok' => true]);
});
```

**Güvenlik:** imzayı doğrulamadan payload’ı işleme.

---

## OpenAPI süreci

SDK, WAHA OpenAPI spec’ini kullanır ve generated client üretir.

Komutlar:

- Spec çek:
  ```bash
  php artisan waha:openapi:fetch
  ```

- Client generate et:
  ```bash
  php artisan waha:openapi:generate
  ```

- Update (fetch + generate + ide helper gibi):
  ```bash
  php artisan waha:openapi:update
  ```

Ayarlar: `config/waha.php` → `openapi`.

---

## IDE Helper

Facade (`Waha`) ve proxy’ler için PHPDoc üretir:

```bash
php artisan waha:openapi:ide-helper
```

Sonrasında IDE re-index gerekebilir.

---

## Test & CI

Local test:

```bash
composer test
```

Public repo için önerilen standartlar:
- Pint (format)
- PHPStan (static analysis)
- GitHub Actions ile:
  - `composer install`
  - `composer test`
  - `vendor/bin/pint --test`
  - `vendor/bin/phpstan analyse`

---

## Versiyonlama

**SemVer** kullan:

- `MAJOR`: breaking
- `MINOR`: geriye uyumlu feature
- `PATCH`: geriye uyumlu fix

Packagist ve Composer için git tag’leri kritik.

---

## Lisans

MIT
