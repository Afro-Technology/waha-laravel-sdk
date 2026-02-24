<?php

namespace Vendor\Waha\OpenApi;

use GuzzleHttp\Client;
use Vendor\Waha\Debug\WahaDebugManager;

class WahaTagProxy
{
    private bool $nextCallDebug = false;

    private ?string $responseFormatOverride = null;

    public function __construct(
        protected readonly OpenApiRouter $router,
        protected readonly GeneratedClientFactory $clientFactory,
        protected readonly string $hostKey,
        protected readonly string $tagName,
        protected readonly ?WahaDebugManager $debug = null,
        ?string $responseFormatOverride = null,
    ) {
        $this->responseFormatOverride = $responseFormatOverride;
    }

    /** Enable debug capture for the next HTTP call only (scoped). */
    public function debug(): static
    {
        $clone = clone $this;
        $clone->nextCallDebug = true;

        return $clone;
    }

    /** Force response normalization to array for this chain. */
    public function asArray(): static
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'array';

        return $clone;
    }

    /** Force response normalization to JSON string for this chain. */
    public function asJson(): static
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'json';

        return $clone;
    }

    /** Force response to be returned as generated OpenAPI model objects (default behavior). */
    public function asModel(): static
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'model';

        return $clone;
    }

    public function __call(string $name, array $arguments)
    {
        $op = $this->resolveOperation($name);
        if (! $op) {
            throw new \BadMethodCallException("Unknown WAHA method '{$name}' for tag '{$this->tagName}'.");
        }

        if ($this->debug && $this->nextCallDebug) {
            $this->debug->pushEnabled(true);
            try {
                return $this->invokeOperation($op, $arguments);
            } finally {
                $this->debug->popEnabled();
            }
        }

        return $this->invokeOperation($op, $arguments);
    }

    private function resolveOperation(string $name): ?array
    {
        $ops = $this->router->operationsByTag($this->tagName);
        if (! $ops) {
            return null;
        }

        foreach ($ops as $op) {
            if (($op['alias'] ?? null) === $name) {
                return $op;
            }
        }

        // case-insensitive fallback
        $lower = strtolower($name);
        foreach ($ops as $op) {
            if (strtolower((string) ($op['alias'] ?? '')) === $lower) {
                return $op;
            }
        }

        return null;
    }

    private function invokeOperation(array $op, array $arguments)
    {
        $api = $this->clientFactory->makeTagApi($this->hostKey, $this->tagName);

        $method = $this->clientFactory->resolveGeneratedMethod(
            api: $api,
            operationId: $op['operationId'] ?? null,
            alias: (string) ($op['alias'] ?? ''),
        );

        $inputs = $this->buildOperationInputs($op, $arguments);
        $callArgs = $this->buildCallArgsByReflection($api, $method, $op, $inputs);

        // If caller wants non-model output, bypass OpenAPI deserialization entirely.
        // This makes the SDK resilient to upstream schema drift (wrong types, oneOf missing, etc.).
        $format = $this->responseFormatOverride
            ?? (string) ($this->clientFactory->responsesConfig()['format'] ?? 'model');

        $format = strtolower(trim($format));
        if ($format !== '' && $format !== 'model') {
            // rawFallback uses the same factory HTTP client, so debug + lastHttp capture still works.
            return $this->formatResponse($this->rawFallback($op, $inputs));
        }

        try {
            // If spec lacks a JSON response schema, OpenAPI generator may return null.
            // In that case, bypass generated deserialization and do a raw JSON request.
            if (! $this->operationHasJsonSchema($op)) {
                return $this->formatResponse($this->rawFallback($op, $inputs));
            }

            $result = $api->{$method}(...$callArgs);
            // Safety net: some generator versions still return null for GET endpoints even with partial schemas.
            if ($result === null && strtoupper((string) ($op['httpMethod'] ?? 'GET')) === 'GET') {
                return $this->formatResponse($this->rawFallback($op, $inputs));
            }

            return $this->formatResponse($result);
        } catch (\RuntimeException $e) {
            // Fallback to raw request if generator method mapping ever drifts.
            return $this->formatResponse($this->rawFallback($op, $inputs));
        }
    }

    /**
     * Build normalized inputs for an operation from the SDK convenience signature.
     *
     * This SDK exposes methods like:
     *   createGroup(string $name, array $participants, ?string $session = null)
     *
     * But OpenAPI-generated methods usually expect:
     *   createGroup(string $session, CreateGroupRequest $body)
     * (or the reverse order).
     */
    private function buildOperationInputs(array $op, array $arguments): array
    {
        $host = $this->clientFactory->hostConfig($this->hostKey);
        $defaultSession = (string) ($host['default_session'] ?? 'default');

        // Normalize named args (PHP passes named args as associative keys in $arguments)
        $named = [];
        $positional = [];
        foreach ($arguments as $k => $v) {
            if (is_string($k)) {
                $named[$k] = $v;
            } else {
                $positional[] = $v;
            }
        }

        $params = $op['parameters'] ?? [];
        $pathParams = [];
        $queryParams = [];
        $hasSessionParam = false;

        // session may live in request body (most WAHA endpoints) or in path/query (some endpoints)
        $bodyProps = is_array($op['requestBodyProps'] ?? null) ? $op['requestBodyProps'] : [];
        $bodyHasSessionProp = in_array('session', $bodyProps, true);

        foreach ($params as $p) {
            $pName = $p['name'] ?? null;
            $in = $p['in'] ?? null;
            if (! $pName || ! $in) {
                continue;
            }

            if ($pName === 'session') {
                $hasSessionParam = true;

                continue;
            }

            // Prefer named params for non-session path/query params.
            if (array_key_exists($pName, $named)) {
                if ($in === 'path') {
                    $pathParams[$pName] = $named[$pName];
                }
                if ($in === 'query') {
                    $queryParams[$pName] = $named[$pName];
                }
            }
        }

        // Build request body.
        $body = null;
        if (array_key_exists('body', $named)) {
            $body = $named['body'];
        } elseif (($op['requestBody'] ?? null) !== null) {
            $props = $op['requestBodyProps'] ?? [];

            // If the first positional arg is already an array/object and we don't have a prop list,
            // treat it as the full body.
            if ($props === [] && isset($positional[0]) && (is_array($positional[0]) || is_object($positional[0]))) {
                $body = $positional[0];
                array_shift($positional);
            } elseif (is_array($props) && $props !== []) {
                $assoc = [];
                foreach ($props as $prop) {
                    if (! is_string($prop) || $prop === '') {
                        continue;
                    }

                    if (array_key_exists($prop, $named)) {
                        $assoc[$prop] = $named[$prop];

                        continue;
                    }

                    if ($positional !== []) {
                        $assoc[$prop] = array_shift($positional);

                        continue;
                    }
                }

                // If nothing matched, but we still have a positional value, treat it as body.
                if ($assoc === [] && $positional !== []) {
                    $body = array_shift($positional);
                } else {
                    $body = $assoc;
                }
            } else {
                // No props info - take next positional as body.
                if ($positional !== []) {
                    $body = array_shift($positional);
                }
            }
        }

        // Session: prefer named, then leftover positional (convenience signature puts session last), then default.
        $session = $named['session'] ?? null;
        if (! is_string($session) || $session === '') {
            if ($hasSessionParam && $positional !== []) {
                $maybeSession = array_pop($positional);
                if (is_string($maybeSession) && $maybeSession !== '') {
                    $session = $maybeSession;
                }
            }
        }
        if (! is_string($session) || $session === '') {
            $session = $defaultSession;
        }
        if ($hasSessionParam) {
            $pathParams['session'] = $session;
        }

        // If the endpoint expects session in the BODY (common in WAHA), enforce default session
        // when the caller did not provide one.
        if ($bodyHasSessionProp) {
            if (is_array($body)) {
                if (! array_key_exists('session', $body) || ! is_string($body['session']) || $body['session'] === '') {
                    $body['session'] = $session;
                }
            } elseif (is_object($body)) {
                // Best-effort: generated models often provide setSession().
                if (method_exists($body, 'setSession')) {
                    try {
                        $current = method_exists($body, 'getSession') ? $body->getSession() : null;
                        if (! is_string($current) || $current === '') {
                            $body->setSession($session);
                        }
                    } catch (\Throwable) {
                        // ignore
                    }
                }
            }
        }

        // If request body has a $ref, try to instantiate the generated model with the assoc data.
        if (($op['requestBody'] ?? null) !== null) {
            $ref = $op['requestBodyRef'] ?? null;
            if (is_string($ref) && $ref !== '' && is_array($body)) {
                $modelClass = $this->modelClassFromRef($ref);
                if ($modelClass && class_exists($modelClass)) {
                    // OpenAPI generator models often use snake_case "local" property names
                    // (e.g. chat_id) while OpenAPI schema properties use JSON/original names
                    // (e.g. chatId). Constructor expects local keys.
                    $body = $this->instantiateGeneratedModel($modelClass, $body);
                }
            }
        }

        return [
            'path' => $pathParams,
            'query' => $queryParams,
            'body' => $body,
        ];
    }

    private function modelClassFromRef(string $ref): ?string
    {
        // "#/components/schemas/CreateGroupRequest" => Vendor\Waha\Generated\Model\CreateGroupRequest
        $parts = explode('/', $ref);
        $name = end($parts);
        if (! is_string($name) || $name === '') {
            return null;
        }

        return 'Vendor\\Waha\\Generated\\Model\\'.$name;
    }

    /**
     * Instantiate a generated model using the correct constructor keys.
     *
     * @param  class-string  $modelClass
     */
    private function instantiateGeneratedModel(string $modelClass, array $data): object
    {
        // If model exposes attributeMap(), flip it to map original(JSON) => local.
        if (method_exists($modelClass, 'attributeMap')) {
            try {
                /** @var array<string,string> $map */
                $map = $modelClass::attributeMap();
                if (is_array($map) && $map !== []) {
                    $origToLocal = array_flip($map);
                    $converted = [];
                    foreach ($data as $k => $v) {
                        $key = is_string($k) ? ($origToLocal[$k] ?? $k) : $k;
                        $converted[$key] = $v;
                    }
                    $data = $converted;
                }
            } catch (\Throwable) {
                // If anything goes wrong, fall back to raw data.
            }
        }

        return new $modelClass($data);
    }

    private function buildCallArgsByReflection(object $api, string $method, array $op, array $inputs): array
    {
        $rm = new \ReflectionMethod($api, $method);
        $args = [];

        $path = $inputs['path'] ?? [];
        $query = $inputs['query'] ?? [];
        $body = $inputs['body'] ?? null;

        $bodyParamNames = $this->possibleRequestBodyParamNames($op);

        foreach ($rm->getParameters() as $p) {
            $pName = $p->getName();

            if (is_array($path) && array_key_exists($pName, $path)) {
                $args[] = $path[$pName];

                continue;
            }

            if (is_array($query) && array_key_exists($pName, $query)) {
                $args[] = $query[$pName];

                continue;
            }

            // Common generated names for request body param.
            if ($body !== null) {
                $type = $p->getType();
                if ($pName === 'body') {
                    $args[] = $body;

                    continue;
                }

                // Some OpenAPI generator templates do NOT type-hint the body param.
                // In that case, the param name is usually snake_case of the schema name,
                // e.g. MessageTextRequest => $message_text_request.
                if (in_array($pName, $bodyParamNames, true)) {
                    $args[] = $body;

                    continue;
                }

                if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                    $class = $type->getName();
                    if (is_string($class) && class_exists($class)) {
                        // Most generated models implement JsonSerializable / ModelInterface.
                        if (is_a($body, $class, true)) {
                            $args[] = $body;

                            continue;
                        }
                    }
                }

                if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                    if ($type->getName() === 'array' && is_array($body)) {
                        $args[] = $body;

                        continue;
                    }
                }
            }

            // Use default/null.
            if ($p->isDefaultValueAvailable()) {
                $args[] = $p->getDefaultValue();

                continue;
            }

            $args[] = null;
        }

        return $args;
    }

    private function possibleRequestBodyParamNames(array $op): array
    {
        $names = ['body'];
        $ref = $op['requestBodyRef'] ?? null;
        if (is_string($ref) && $ref !== '') {
            $parts = explode('/', $ref);
            $schema = end($parts);
            if (is_string($schema) && $schema !== '') {
                $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $schema));
                $names[] = $snake;
                $names[] = lcfirst($schema);
            }
        }

        return array_values(array_unique(array_filter($names, fn ($v) => is_string($v) && $v !== '')));
    }

    private function bodyParamAliases(string $prop): array
    {
        // Intentionally empty: we do NOT alias OpenAPI schema property names.
        // (e.g. chatId must be chatId; reply_to must be reply_to)
        return [];
    }

    private function operationHasJsonSchema(array $op): bool
    {
        $responses = $op['responses'] ?? [];
        if (! is_array($responses) || $responses === []) {
            return false;
        }

        foreach ($responses as $code => $resp) {
            // only 2xx
            $codeStr = (string) $code;
            if (! preg_match('/^2\d\d$/', $codeStr)) {
                continue;
            }
            if (! is_array($resp)) {
                continue;
            }
            $content = $resp['content'] ?? null;
            if (! is_array($content)) {
                continue;
            }

            foreach (['application/json', 'application/*+json', '*/*'] as $ct) {
                if (! isset($content[$ct]) || ! is_array($content[$ct])) {
                    continue;
                }
                $schema = $content[$ct]['schema'] ?? null;
                if (is_array($schema)) {
                    return true;
                }
                if (is_string($schema) && $schema !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    private function rawFallback(array $op, array $inputs)
    {
        $host = $this->clientFactory->hostConfig($this->hostKey);

        $baseUrl = rtrim((string) ($host['base_url'] ?? $host['url'] ?? ''), '/');
        $timeout = (int) ($host['timeout_seconds'] ?? $host['timeout'] ?? 30);

        $apiKey = $host['admin_api_key'] ?? $host['token'] ?? null;
        $headerName = $host['api_key_header'] ?? 'X-Api-Key';

        $headers = ['Accept' => 'application/json'];
        if (! empty($apiKey)) {
            $headers[$headerName] = $apiKey;
        }

        $path = (string) ($op['path'] ?? '');
        $method = strtoupper((string) ($op['httpMethod'] ?? 'get'));

        $pathParams = is_array($inputs['path'] ?? null) ? $inputs['path'] : [];
        $queryParams = is_array($inputs['query'] ?? null) ? $inputs['query'] : [];

        foreach ($pathParams as $k => $v) {
            $path = str_replace('{'.$k.'}', rawurlencode((string) $v), $path);
        }

        $url = $baseUrl.$path;

        $opts = [
            'timeout' => $timeout,
            'headers' => $headers,
            'query' => array_filter($queryParams, fn ($v) => $v !== null),
        ];

        if (($op['requestBody'] ?? null) !== null) {
            $body = $inputs['body'] ?? null;
            if ($body !== null) {
                // allow model objects from generator (they serialize via jsonSerialize)
                $opts['json'] = $body;
            }
        }

        // Use factory-provided client so debug middleware and "last call" capture works
        // for raw fallback requests too.
        $client = $this->clientFactory->makeRawHttpClient($this->hostKey);

        $resp = $client->request($method, $url, $opts);
        $raw = (string) $resp->getBody();

        $decoded = json_decode($raw, true);

        return $decoded ?? $raw;
    }

    private function formatResponse(mixed $value): mixed
    {
        $format = $this->responseFormatOverride
            ?? (string) ($this->clientFactory->responsesConfig()['format'] ?? 'model');

        $format = strtolower(trim($format));
        if ($format === '') {
            $format = 'model';
        }

        if ($format === 'model') {
            return $value;
        }

        if ($format === 'array') {
            return $this->normalizeToArray($value);
        }

        if ($format === 'json') {
            if (is_string($value)) {
                $t = trim($value);
                if ($t !== '' && (str_starts_with($t, '{') || str_starts_with($t, '['))) {
                    return $value;
                }
            }

            $flags = (int) ($this->clientFactory->responsesConfig()['json_flags']
                ?? (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $encoded = json_encode($this->normalizeToArray($value), $flags);
            if ($encoded === false) {
                throw new \RuntimeException('WAHA response json_encode failed: '.json_last_error_msg());
            }

            return $encoded;
        }

        throw new \InvalidArgumentException(
            "Invalid waha.responses.format '{$format}'. Allowed: model, array, json."
        );
    }

    private function normalizeToArray(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_string($value)) {
            $t = trim($value);
            if ($t !== '' && (str_starts_with($t, '{') || str_starts_with($t, '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return ['raw' => $value];
        }

        if ($value instanceof \JsonSerializable) {
            return $this->normalizeToArray($value->jsonSerialize());
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_object($value)) {
            return array_map(
                fn ($v) => $this->normalizeToArray($v),
                get_object_vars($value)
            );
        }

        return $value;
    }
}
