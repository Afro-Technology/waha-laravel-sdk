<?php

namespace Vendor\Waha\Actions;

use Illuminate\Support\Str;
use Vendor\Waha\OpenApi\OpenApiRouter;
use Vendor\Waha\OpenApi\OpenApiSpecRepository;

final class GenerateIdeHelperAction
{
    /**
     * @param  non-empty-string  $specPath
     * @param  non-empty-string  $projectRoot  Base directory to resolve package files from (Laravel base_path() or package root)
     */
    public function execute(string $specPath, string $projectRoot): void
    {
        if (! is_file($specPath)) {
            throw new \RuntimeException("OpenAPI spec path missing/not found: {$specPath}");
        }

        $spec = (new OpenApiSpecRepository($specPath))->load();
        $router = new OpenApiRouter($spec);

        $this->generateTagProxyClasses($router, $projectRoot);

        $facadePath = $this->firstExisting([
            $projectRoot.'/src/Facades/Waha.php',
            $projectRoot.'/packages/waha-laravel-sdk/src/Facades/Waha.php',
            $projectRoot.'/vendor/vendor/waha-laravel-sdk/src/Facades/Waha.php',
        ]);

        if ($facadePath !== null) {
            $this->updateDocblockFile($facadePath, $this->buildFacadeDoc($router), 'class\s+Waha\s+extends\s+Facade');
        }

        $proxyPath = $this->firstExisting([
            $projectRoot.'/src/OpenApi/WahaApiProxy.php',
            $projectRoot.'/packages/waha-laravel-sdk/src/OpenApi/WahaApiProxy.php',
            $projectRoot.'/vendor/vendor/waha-laravel-sdk/src/OpenApi/WahaApiProxy.php',
        ]);

        if ($proxyPath !== null) {
            $this->updateDocblockFile($proxyPath, $this->buildProxyDoc($router), 'final\s+class\s+WahaApiProxy');
        }

        $mgrProxyPath = $this->firstExisting([
            $projectRoot.'/src/OpenApi/WahaManagerProxy.php',
            $projectRoot.'/packages/waha-laravel-sdk/src/OpenApi/WahaManagerProxy.php',
            $projectRoot.'/vendor/vendor/waha-laravel-sdk/src/OpenApi/WahaManagerProxy.php',
        ]);

        if ($mgrProxyPath !== null) {
            $this->updateDocblockFile($mgrProxyPath, $this->buildManagerProxyDoc($router), 'final\s+class\s+WahaManagerProxy');
        }
    }

    private function generateTagProxyClasses(OpenApiRouter $router, string $projectRoot): void
    {
        $tagsDir = $this->firstExistingDir([
            $projectRoot.'/src/OpenApi/Tags',
            $projectRoot.'/packages/waha-laravel-sdk/src/OpenApi/Tags',
            $projectRoot.'/vendor/vendor/waha-laravel-sdk/src/OpenApi/Tags',
        ]);

        if ($tagsDir === null) {
            $tagsDir = $projectRoot.'/src/OpenApi/Tags';
            @mkdir($tagsDir, 0775, true);
        }

        foreach ($router->tagMethods() as $method => $tagName) {
            $class = Str::studly($method).'Tag';
            $doc = $this->buildTagProxyDoc($router, $tagName);

            $content = "<?php\n\nnamespace Vendor\\Waha\\OpenApi\\Tags;\n\nuse Vendor\\Waha\\OpenApi\\WahaTagProxy;\n\n{$doc}\nfinal class {$class} extends WahaTagProxy\n{\n}\n";
            file_put_contents($tagsDir.DIRECTORY_SEPARATOR."{$class}.php", $content);
        }
    }

    private function buildFacadeDoc(OpenApiRouter $router): string
    {
        $lines = [];
        $lines[] = '/**';
        $lines[] = ' * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)';
        $lines[] = ' *';

        $lines[] = ' * @method static \\Vendor\\Waha\\OpenApi\\WahaApiProxy host(string $hostKey)';
        $lines[] = ' * @method static \\Vendor\\Waha\\OpenApi\\WahaManagerProxy debug()';
        $lines[] = ' * @method static \\Vendor\\Waha\\OpenApi\\WahaManagerProxy asArray()';
        $lines[] = ' * @method static \\Vendor\\Waha\\OpenApi\\WahaManagerProxy asJson()';
        $lines[] = ' * @method static \\Vendor\\Waha\\OpenApi\\WahaManagerProxy asModel()';
        $lines[] = ' * @method static ?array lastHttp()';
        $lines[] = ' * @method static ?string lastHttpCurl()';
        $lines[] = ' * @method static mixed withDebug(callable $fn)';
        $lines[] = ' *';

        foreach ($router->tagMethods() as $method => $tagName) {
            $class = Str::studly($method).'Tag';
            $lines[] = " * @method static \\Vendor\\Waha\\OpenApi\\Tags\\{$class} {$method}()";
        }

        $lines[] = ' *';

        foreach ($router->topLevelAliases() as $alias => $op) {
            $sig = $this->buildConvenienceSignature($router, $op, $alias, true);
            $lines[] = ' * '.$sig;
        }

        $lines[] = ' */';

        return implode("\n", $lines);
    }

    private function buildProxyDoc(OpenApiRouter $router): string
    {
        $lines = [];
        $lines[] = '/**';
        $lines[] = ' * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)';
        $lines[] = ' *';

        foreach ($router->tagMethods() as $method => $tagName) {
            $class = Str::studly($method).'Tag';
            $lines[] = " * @method \\Vendor\\Waha\\OpenApi\\Tags\\{$class} {$method}()";
        }

        $lines[] = ' *';

        foreach ($router->topLevelAliases() as $alias => $op) {
            $sig = $this->buildConvenienceSignature($router, $op, $alias, false);
            $lines[] = ' * '.$sig;
        }

        $lines[] = ' */';

        return implode("\n", $lines);
    }

    private function buildManagerProxyDoc(OpenApiRouter $router): string
    {
        $lines = [];
        $lines[] = '/**';
        $lines[] = ' * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)';
        $lines[] = ' *';

        foreach ($router->tagMethods() as $method => $tagName) {
            $class = Str::studly($method).'Tag';
            $lines[] = " * @method \\Vendor\\Waha\\OpenApi\\Tags\\{$class} {$method}()";
        }

        $lines[] = ' *';

        foreach ($router->topLevelAliases() as $alias => $op) {
            $sig = $this->buildConvenienceSignature($router, $op, $alias, false);
            $lines[] = ' * '.$sig;
        }

        $lines[] = ' */';

        return implode("\n", $lines);
    }

    private function buildTagProxyDoc(OpenApiRouter $router, string $tagName): string
    {
        $lines = [];
        $lines[] = '/**';
        $lines[] = ' * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)';
        $lines[] = " * Tag: {$tagName}";
        $lines[] = ' *';

        foreach ($router->operationsByTag($tagName) as $op) {
            $alias = (string) ($op['alias'] ?? '');
            if ($alias === '') {
                continue;
            }

            $lines[] = ' * '.$this->buildConvenienceSignature($router, $op, $alias, false);
        }

        $lines[] = ' */';

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $op
     */
    private function buildConvenienceSignature(OpenApiRouter $router, array $op, string $alias, bool $static): string
    {
        $returnType = $this->inferReturnType($router, $op);
        $params = $this->inferConvenienceParams($router, $op);

        $paramSig = implode(', ', array_map(static fn (array $p): string => (string) $p['sig'], $params));
        $prefix = $static ? '@method static' : '@method';

        return "{$prefix} {$returnType} {$alias}({$paramSig})";
    }

    /**
     * @param  array<string, mixed>  $op
     */
    private function inferReturnType(OpenApiRouter $router, array $op): string
    {
        $responses = $op['responses'] ?? [];
        if (! is_array($responses)) {
            return 'mixed';
        }

        foreach (['200', '201', '202', 'default'] as $code) {
            $r = $responses[$code] ?? null;
            if (! is_array($r)) {
                continue;
            }

            $content = $r['content'] ?? null;
            if (! is_array($content)) {
                continue;
            }

            $json = $content['application/json'] ?? null;
            if (! is_array($json)) {
                $json = reset($content);
            }

            $schema = is_array($json) ? ($json['schema'] ?? null) : null;
            if (! is_array($schema)) {
                continue;
            }

            $ref = $schema['$ref'] ?? null;
            if (is_string($ref)) {
                $name = $this->refSchemaName($ref);

                return "\\Vendor\\Waha\\Generated\\Model\\{$name}";
            }

            $type = $schema['type'] ?? null;

            return match ($type) {
                'string' => 'string',
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                'array', 'object' => 'array',
                default => 'mixed',
            };
        }

        return 'mixed';
    }

    /**
     * @param  array<string, mixed>  $op
     * @return list<array{sig:string}>
     */
    private function inferConvenienceParams(OpenApiRouter $router, array $op): array
    {
        $out = [];
        /** @var list<string> $used */
        $used = [];

        $params = is_array($op['parameters'] ?? null) ? $op['parameters'] : [];
        foreach ($params as $p) {
            if (! is_array($p)) {
                continue;
            }
            if (($p['in'] ?? null) !== 'path') {
                continue;
            }

            $name = (string) ($p['name'] ?? '');
            if ($name === '' || strtolower($name) === 'session') {
                continue;
            }

            $schema = is_array($p['schema'] ?? null) ? $p['schema'] : [];
            $phpType = $this->schemaToPhpType($schema);
            $paramName = $this->niceParamName($name);
            $paramName = $this->dedupe($paramName, $used);

            $out[] = ['sig' => "{$phpType} \${$paramName}"];
        }

        $ref = $this->extractRequestBodyRef($op);
        if (is_string($ref)) {
            $schema = $router->schemaByRef($ref) ?? [];
            $properties = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
            $required = is_array($schema['required'] ?? null) ? $schema['required'] : [];

            $required = array_values(array_filter($required, static fn ($x): bool => is_string($x)));
            $all = array_keys($properties);

            $ordered = array_merge($required, array_values(array_diff($all, $required)));
            foreach ($ordered as $jsonName) {
                if (strtolower((string) $jsonName) === 'session') {
                    continue;
                }

                $propSchema = is_array($properties[$jsonName] ?? null) ? $properties[$jsonName] : [];
                $phpType = $this->schemaToPhpType($propSchema);

                $isRequired = in_array($jsonName, $required, true);
                $paramName = $this->niceParamName((string) $jsonName);
                $paramName = $this->dedupe($paramName, $used);

                if (! $isRequired) {
                    $defaultLiteral = $this->schemaDefaultLiteral($propSchema);

                    $phpType = $this->nullable($phpType);

                    if ($defaultLiteral !== null) {
                        $out[] = ['sig' => "{$phpType} \${$paramName} = {$defaultLiteral}"];
                    } else {
                        $out[] = ['sig' => "{$phpType} \${$paramName} = null"];
                    }
                } else {
                    $out[] = ['sig' => "{$phpType} \${$paramName}"];
                }
            }
        }

        foreach ($params as $p) {
            if (! is_array($p)) {
                continue;
            }
            if (($p['in'] ?? null) !== 'query') {
                continue;
            }

            $name = (string) ($p['name'] ?? '');
            if ($name === '' || strtolower($name) === 'session') {
                continue;
            }

            $schema = is_array($p['schema'] ?? null) ? $p['schema'] : [];
            $phpType = $this->nullable($this->schemaToPhpType($schema));
            $paramName = $this->niceParamName($name);
            $paramName = $this->dedupe($paramName, $used);

            $defaultLiteral = $this->schemaDefaultLiteral($schema);

            if ($defaultLiteral !== null) {
                $out[] = ['sig' => "{$phpType} \${$paramName} = {$defaultLiteral}"];
            } else {
                $out[] = ['sig' => "{$phpType} \${$paramName} = null"];
            }
        }

        if ($this->opUsesSession($router, $op)) {
            $out[] = ['sig' => '?string $session = null'];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $op
     */
    private function opUsesSession(OpenApiRouter $router, array $op): bool
    {
        $params = is_array($op['parameters'] ?? null) ? $op['parameters'] : [];
        foreach ($params as $p) {
            if (! is_array($p)) {
                continue;
            }
            if (strtolower((string) ($p['name'] ?? '')) === 'session') {
                return true;
            }
        }

        $ref = $this->extractRequestBodyRef($op);
        if (is_string($ref)) {
            $schema = $router->schemaByRef($ref) ?? [];
            $props = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
            if (array_key_exists('session', $props)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaToPhpType(array $schema): string
    {
        $ref = $schema['$ref'] ?? null;
        if (is_string($ref)) {
            $name = $this->refSchemaName($ref);

            return "\\Vendor\\Waha\\Generated\\Model\\{$name}";
        }

        $type = $schema['type'] ?? null;

        return match ($type) {
            'string' => 'string',
            'integer' => 'int',
            'number' => 'float',
            'boolean' => 'bool',
            'array', 'object' => 'array',
            default => 'mixed',
        };
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaDefaultLiteral(array $schema): ?string
    {
        if (! array_key_exists('default', $schema)) {
            return null;
        }

        $default = $schema['default'];

        if ($default === null) {
            return null;
        }

        return $this->phpLiteral($default);
    }

    private function phpLiteral(mixed $v): string
    {
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }

        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }

        if (is_string($v)) {
            $escaped = str_replace(['\\', "'"], ['\\\\', "\\'"], $v);

            return "'".$escaped."'";
        }

        if (is_array($v)) {
            if ($v === []) {
                return '[]';
            }

            $isAssoc = array_keys($v) !== range(0, count($v) - 1);
            $parts = [];

            foreach ($v as $k => $val) {
                $valLit = $this->phpLiteral($val);

                if ($isAssoc) {
                    $keyLit = is_int($k) ? (string) $k : $this->phpLiteral((string) $k);
                    $parts[] = "{$keyLit} => {$valLit}";
                } else {
                    $parts[] = $valLit;
                }
            }

            return '['.implode(', ', $parts).']';
        }

        if (is_object($v)) {
            return '[]';
        }

        return 'null';
    }

    /**
     * @param  array<string, mixed>  $op
     */
    private function extractRequestBodyRef(array $op): ?string
    {
        $rb = $op['requestBody'] ?? null;
        if (! is_array($rb)) {
            return null;
        }

        $content = $rb['content'] ?? null;
        if (! is_array($content)) {
            return null;
        }

        $json = $content['application/json'] ?? null;
        if (! is_array($json)) {
            $json = reset($content);
        }
        if (! is_array($json)) {
            return null;
        }

        $schema = $json['schema'] ?? null;
        if (! is_array($schema)) {
            return null;
        }

        $ref = $schema['$ref'] ?? null;

        return is_string($ref) ? $ref : null;
    }

    private function refSchemaName(string $ref): string
    {
        $parts = explode('/', $ref);

        return (string) end($parts);
    }

    private function nullable(string $type): string
    {
        if (str_starts_with($type, '?')) {
            return $type;
        }
        if ($type === 'mixed') {
            return 'mixed';
        }

        return '?'.$type;
    }

    private function niceParamName(string $name): string
    {
        $n = str_replace(['-', '.', '/'], '_', $name);

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $n) === 1) {
            return $n;
        }

        return Str::camel($n);
    }

    /**
     * @param  list<string>  $used
     *
     * @param-out list<string> $used
     */
    private function dedupe(string $name, array &$used): string
    {
        $base = $name;
        $i = 2;
        while (in_array($name, $used, true)) {
            $name = $base.$i;
            $i++;
        }
        $used[] = $name;

        return $name;
    }

    private function updateDocblockFile(string $path, string $doc, string $classPattern): void
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return;
        }

        $replaced = preg_replace(
            '/\/\*\*.*?\*\/\s*('.$classPattern.')/s',
            $doc."\n$1",
            $contents,
            1,
            $count
        );

        if ($count === 0) {
            $replaced = preg_replace(
                '/('.$classPattern.')/',
                $doc."\n$1",
                $contents,
                1
            );
        }

        if (! is_string($replaced)) {
            return;
        }

        file_put_contents($path, $replaced);
    }

    /**
     * @param  list<string>  $paths
     */
    private function firstExisting(array $paths): ?string
    {
        foreach ($paths as $p) {
            if (is_file($p)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $paths
     */
    private function firstExistingDir(array $paths): ?string
    {
        foreach ($paths as $p) {
            if (is_dir($p)) {
                return $p;
            }
        }

        return null;
    }
}
