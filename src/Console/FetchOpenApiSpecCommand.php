<?php

namespace Vendor\Waha\Console;

use Illuminate\Console\Command;
use Vendor\Waha\Actions\FetchOpenApiSpecAction;
use Vendor\Waha\Support\PackagePath;

final class FetchOpenApiSpecCommand extends Command
{
    protected $signature = 'waha:openapi:fetch
        {--url= : OpenAPI URL (defaults to waha.openapi.url)}
        {--out= : Output path for openapi.json (defaults to waha.openapi.spec_path)}
        {--spec= : Alias of --out}
        {--user= : Basic Auth username (Swagger protection)}
        {--pass= : Basic Auth password (Swagger protection)}
    ';

    protected $description = 'Fetch WAHA OpenAPI spec JSON into the configured spec path.';

    public function handle(FetchOpenApiSpecAction $action): int
    {
        $url = (string) ($this->option('url') ?: config('waha.openapi.url', ''));
        if ($url === '') {
            $this->error('OpenAPI URL missing. Set WAHA_OPENAPI_URL or pass --url');

            return self::FAILURE;
        }

        $specPath = (string) ($this->option('out')
            ?: $this->option('spec')
                ?: config('waha.openapi.spec_path', PackagePath::path('resources/openapi/openapi.json')));

        if ($specPath !== '' && ! str_starts_with($specPath, '/')) {
            $specPath = base_path($specPath);
        }

        $basicAuth = [
            'username' => (string) ($this->option('user') ?: config('waha.openapi.basic_auth.username', '')),
            'password' => (string) ($this->option('pass') ?: config('waha.openapi.basic_auth.password', '')),
        ];

        try {
            $action->execute(
                $url,
                $specPath,
                $basicAuth,
                (int) config('waha.openapi.timeout_seconds', 30),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Fetched spec into: {$specPath}");

        return self::SUCCESS;
    }
}
