<?php

namespace Vendor\Waha\OpenApi;

final class OpenApiRouter
{
    /** @var array<string, string> */
    private array $tagToMethod = [];      // methodName => tagName

    /** @var array<string, list<array<string, mixed>>> */
    private array $tagToOps = [];         // tagName => ops[]

    /** @var array<string, array<string, mixed>> */
    private array $topLevelAliases = [];  // alias => op

    /**
     * @param  array<string, mixed>  $spec
     */
    public function __construct(private readonly array $spec)
    {
        $this->build();
    }

    /**
     * @return array<string, string>
     */
    public function tagMethods(): array
    {
        return $this->tagToMethod;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function operationsByTag(string $tagName): array
    {
        return $this->tagToOps[$tagName] ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolveTopLevel(string $alias): ?array
    {
        // Exact match first
        if (array_key_exists($alias, $this->topLevelAliases)) {
            return $this->topLevelAliases[$alias];
        }

        // Case-insensitive fallback
        $lower = strtolower($alias);
        if (array_key_exists($lower, $this->topLevelAliases)) {
            return $this->topLevelAliases[$lower];
        }

        return null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function topLevelAliases(): array
    {
        return $this->topLevelAliases;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function schemaByRef(string $ref): ?array
    {
        // Supports local component refs like "#/components/schemas/MessageTextRequest"
        if (! str_starts_with($ref, '#/')) {
            return null;
        }

        $parts = explode('/', substr($ref, 2));
        $node = $this->spec;

        foreach ($parts as $p) {
            if (! is_array($node) || ! array_key_exists($p, $node)) {
                return null;
            }
            $node = $node[$p];
        }

        return is_array($node) ? $node : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function spec(): array
    {
        return $this->spec;
    }

    public static function normalizeTagToMethod(string $tag): string
    {
        // drop leading emoji / symbols
        $tag = preg_replace('/^[^\pL\pN]+/u', '', $tag) ?? $tag;
        $tag = trim($tag);

        // keep words, collapse whitespace
        $tag = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $tag);
        $tag = preg_replace('/\s+/', ' ', trim($tag));

        $parts = explode(' ', strtolower($tag));
        $first = array_shift($parts) ?? 'api';

        $camel = $first;
        foreach ($parts as $p) {
            $camel .= ucfirst($p);
        }

        return $camel;
    }

    private function build(): void
    {
        foreach (($this->spec['tags'] ?? []) as $t) {
            $name = $t['name'] ?? null;
            if (! $name) {
                continue;
            }

            $method = self::normalizeTagToMethod($name);
            $this->tagToMethod[$method] = $name;
        }

        $aliasGlobalCount = [];

        foreach (($this->spec['paths'] ?? []) as $path => $pathItem) {
            if (! is_array($pathItem)) {
                continue;
            }

            foreach ($pathItem as $httpMethod => $op) {
                if (! is_array($op)) {
                    continue;
                }

                $tags = $op['tags'] ?? [];
                $tag = $tags[0] ?? null;
                if (! $tag) {
                    continue;
                }

                $operationId = $op['operationId'] ?? null;
                $alias = $this->deriveAlias((string) $path, is_string($operationId) ? $operationId : null);

                $aliasGlobalCount[$alias] = ($aliasGlobalCount[$alias] ?? 0) + 1;

                $this->tagToOps[$tag][] = [
                    'alias' => $alias,
                    'httpMethod' => strtolower((string) $httpMethod),
                    'path' => (string) $path,
                    'operationId' => $operationId,
                    'tag' => $tag,
                    'parameters' => $op['parameters'] ?? [],
                    'requestBody' => $op['requestBody'] ?? null,
                    'requestBodyRef' => $this->extractRequestBodyRef($op['requestBody'] ?? null),
                    'requestBodyProps' => $this->extractRequestBodyProps($op['requestBody'] ?? null),
                    'requestBodyRequired' => $this->extractRequestBodyRequired($op['requestBody'] ?? null),
                    'responses' => $op['responses'] ?? [],
                ];
            }
        }

        $tagMethodNames = array_keys($this->tagToMethod);

        foreach ($this->tagToOps as $tag => $ops) {
            foreach ($ops as $o) {
                $alias = (string) ($o['alias'] ?? '');

                if ($alias === '') {
                    continue;
                }

                if (($aliasGlobalCount[$alias] ?? 0) !== 1) {
                    continue;
                }

                // If alias collides with a tag method (e.g. "status"), don't expose it top-level.
                if (in_array($alias, $tagMethodNames, true)) {
                    continue;
                }

                $this->topLevelAliases[$alias] = $o;
            }
        }
    }

    private function deriveAlias(string $path, ?string $operationId): string
    {
        if ($operationId) {
            $last = $operationId;
            if (str_contains($operationId, '_')) {
                $last = substr($operationId, strrpos($operationId, '_') + 1);
            }

            // Preserve inner capitals from OpenAPI (sendText != sendtext, getQR != getqr)
            if (preg_match('/[a-z][A-Z]/', $last) === 1 || preg_match('/[A-Z]{2,}/', $last) === 1) {
                return lcfirst($last);
            }

            return $this->toLowerCamel($last);
        }

        $p = trim($path, '/');
        $p = preg_replace('#^api/#', '', $p);
        $p = str_replace(['.', '-', '/'], ' ', (string) $p);

        return $this->toLowerCamel($p);
    }

    private function toLowerCamel(string $s): string
    {
        // normalize separators to spaces.
        $s = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim((string) $s));

        if ($s === '') {
            return 'call';
        }

        $parts = preg_split('/\s+/', $s) ?: [];
        $first = array_shift($parts);
        if ($first === null) {
            return 'call';
        }

        if (preg_match('/[a-z][A-Z]/', $first) === 1 || preg_match('/[A-Z]{2,}/', $first) === 1) {
            $out = lcfirst($first);
        } else {
            $out = strtolower($first);
        }

        foreach ($parts as $p) {
            if ($p === '') {
                continue;
            }

            if (preg_match('/[A-Z]/', $p) === 1) {
                $out .= ucfirst($p);
            } else {
                $out .= ucfirst(strtolower($p));
            }
        }

        return $out;
    }

    private function extractRequestBodyRef(mixed $requestBody): ?string
    {
        if (! is_array($requestBody)) {
            return null;
        }
        $content = $requestBody['content'] ?? null;
        if (! is_array($content)) {
            return null;
        }

        foreach (['application/json', 'application/*+json', '*/*'] as $ct) {
            if (! isset($content[$ct]) || ! is_array($content[$ct])) {
                continue;
            }
            $schema = $content[$ct]['schema'] ?? null;
            if (is_array($schema) && isset($schema['$ref']) && is_string($schema['$ref'])) {
                return $schema['$ref'];
            }
        }

        return null;
    }

    /**
     * Body properties'leri "sıralı alan listesi" olarak döndürür.
     *
     * @return list<string>
     */
    private function extractRequestBodyProps(mixed $requestBody): array
    {
        if (! is_array($requestBody)) {
            return [];
        }
        $content = $requestBody['content'] ?? null;
        if (! is_array($content)) {
            return [];
        }

        $schema = null;
        foreach (['application/json', 'application/*+json', '*/*'] as $ct) {
            if (! isset($content[$ct]) || ! is_array($content[$ct])) {
                continue;
            }
            $schema = $content[$ct]['schema'] ?? null;
            break;
        }

        if (! $schema) {
            return [];
        }

        // Resolve $ref
        if (is_array($schema) && isset($schema['$ref']) && is_string($schema['$ref'])) {
            $resolved = $this->schemaByRef($schema['$ref']);
            if (is_array($resolved)) {
                $schema = $resolved;
            }
        }

        if (! is_array($schema)) {
            return [];
        }

        $props = $schema['properties'] ?? null;
        if (! is_array($props)) {
            return [];
        }

        $all = array_values(array_filter(array_keys($props), fn ($k) => is_string($k) && $k !== ''));

        // required first
        $required = $schema['required'] ?? [];
        $required = array_values(array_filter($required, fn ($k) => is_string($k) && $k !== ''));

        $ordered = array_merge($required, array_values(array_diff($all, $required)));

        // session her zaman en sonda (SDK contract)
        if (in_array('session', $ordered, true)) {
            $ordered = array_values(array_filter($ordered, fn ($k) => $k !== 'session'));
            $ordered[] = 'session';
        }

        return $ordered;
    }

    /**
     * @return list<string>
     */
    private function extractRequestBodyRequired(mixed $requestBody): array
    {
        if (! is_array($requestBody)) {
            return [];
        }
        $content = $requestBody['content'] ?? null;
        if (! is_array($content)) {
            return [];
        }

        // schema seç
        $schema = null;
        foreach (['application/json', 'application/*+json', '*/*'] as $ct) {
            if (! isset($content[$ct]) || ! is_array($content[$ct])) {
                continue;
            }
            $schema = $content[$ct]['schema'] ?? null;
            break;
        }

        if (! is_array($schema)) {
            return [];
        }

        // $ref resolve
        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            $resolved = $this->schemaByRef($schema['$ref']);
            if (is_array($resolved)) {
                $schema = $resolved;
            }
        }

        $req = $schema['required'] ?? [];
        if (! is_array($req)) {
            return [];
        }

        return array_values(array_filter($req, fn ($v) => is_string($v) && $v !== ''));
    }
}
