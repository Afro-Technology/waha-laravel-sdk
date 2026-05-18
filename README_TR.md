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
  - [Endpoint ve secret](#endpoint-ve-secret)
  - [Processing modu](#processing-modu)
  - [Handlerlar](#handlerlar)
  - [Event storage](#event-storage)
- [OpenAPI süreci](#openapi-süreci)
- [IDE Helper](#ide-helper)
- [Test & CI](#test--ci)
- [Versiyonlama](#versiyonlama)
- [Lisans](#lisans)

---

## Kurulum

### Gereksinimler

- PHP **8.1+** (Laravel 13 için PHP **8.3+** gerekir)
- Laravel **10 / 11 / 12 / 13**
- Guzzle **7.5+**

### Composer ile kurulum

```bash
composer require afro-technology/waha-laravel-sdk
```

Laravel auto-discovery varsayılan olarak açık.

### Config publish (önerilir)

```bash
php artisan vendor:publish --provider="AfroTechnology\Waha\WahaServiceProvider" --tag="waha-config"
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
use AfroTechnology\Waha\Facades\Waha;

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

### Endpoint ve secret

`waha.webhooks.enabled` true olduğunda paket şu route’u otomatik register eder:

```text
POST /webhooks/waha/{hostKey}
```

Route stateless çalışır ve `{hostKey}` ile ilgili host’un webhook secret değerini çözer:

```env
WAHA_PRIMARY_WEBHOOK_SECRET=super-long-random-secret
```

Aynı secret’ı WAHA tarafında da tanımlamalısın. Paket WAHA webhook HMAC header’larını event, handler, job veya storage çalıştırmadan önce doğrular.

Beklenen WAHA header’ları:

- `X-Webhook-Hmac`
- `X-Webhook-Hmac-Algorithm`
- `X-Webhook-Request-Id`
- `X-Webhook-Timestamp`

### Processing modu

Default processing modu `sync`; böylece yeni kurulum queue worker olmadan da çalışır:

```env
WAHA_WEBHOOKS_PROCESSING_MODE=sync
```

Production için `queue` modu önerilir. Böylece WAHA hızlıca cevap alır, uygulama içi işler Laravel worker içinde yürür:

```env
WAHA_WEBHOOKS_PROCESSING_MODE=queue
WAHA_WEBHOOKS_QUEUE_CONNECTION=redis
WAHA_WEBHOOKS_QUEUE_NAME=waha-webhooks
```

Queue modunda paket request’i doğrular, gerekiyorsa inbox event olarak kaydeder, `ProcessWahaWebhookJob` dispatch eder ve JSON cevabı hemen döner. Sync modunda handler HTTP request içinde çalışır; bu da WAHA’nın uygulama işini beklemesine neden olabilir.

### Handlerlar

WAHA event isimlerini `config/waha.php` içinde handler class’larına map edebilirsin:

```php
'webhooks' => [
    'handlers' => [
        'message.any' => \App\Waha\Handlers\AnyMessageHandler::class,
        'message.*' => \App\Waha\Handlers\MessageHandler::class,
    ],
],
```

Handler class şu contract’ı implement etmeli:

```php
use AfroTechnology\Waha\Webhooks\Contracts\WahaWebhookHandler;
use AfroTechnology\Waha\Webhooks\Events\WahaWebhookReceived;

final class AnyMessageHandler implements WahaWebhookHandler
{
    public function handle(WahaWebhookReceived $event): void
    {
        $payload = $event->payload;
    }
}
```

### Event storage

Event storage default kapalıdır. Açmak istiyorsan önce paket migration’larını publish edip migrate et:

```bash
php artisan vendor:publish --provider="AfroTechnology\Waha\WahaServiceProvider" --tag="waha-migrations"
php artisan migrate
```

Sonra storage’ı aç:

```env
WAHA_WEBHOOKS_STORE_ENABLED=true
WAHA_WEBHOOKS_STORE_RAW=true
WAHA_WEBHOOKS_STORE_RETENTION_DAYS=7
```

Stored event kayıtları payload metadata’sının yanında `queued`, `processing`, `processed`, `failed`, attempt sayısı, timestamp’ler ve son hata bilgisini tutar. Retry için Laravel’in normal queue retry araçlarını kullan.

**Güvenlik:** webhook payload’ını güvenilmeyen input olarak ele al. Paket kabul edilen request’leri işlemeden önce doğrular.

---

## OpenAPI süreci

SDK, WAHA OpenAPI spec’ini kullanır ve generated client üretir.
Generated client sınıfları `AfroTechnology\Waha\Generated` namespace’i altında
üretilir; README’deki facade/tag örnekleri paket içinde expose edilen proxy surface
ile uyumludur.

Paketi kullanan Laravel uygulaması içindeki komutlar:

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

Bu paket reposunda ve zamanlanmış GitHub Actions sync sürecinde paket-local
entrypoint `php bin/openapi-sync.php` komutudur; spec’i çeker, client/proxy
surface’i paket namespace’iyle yeniden üretir ve diff’i review için bırakır.

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
