<?php

namespace Vendor\Waha\Console;

use Illuminate\Console\Command;
use Vendor\Waha\Actions\FetchOpenApiSpecAction;
use Vendor\Waha\Actions\GenerateIdeHelperAction;
use Vendor\Waha\Actions\GenerateOpenApiClientAction;
use Vendor\Waha\Actions\UpdateOpenApiAction;
use Vendor\Waha\Support\PackagePath;

final class UpdateOpenApiCommand extends Command
{
    protected $signature = 'waha:openapi:update
        {--url= : OpenAPI URL (defaults to waha.openapi.url)}
        {--user= : Basic Auth username (Swagger protection)}
        {--pass= : Basic Auth password (Swagger protection)}
        {--spec= : Path to write spec (defaults to waha.openapi.spec_path)}
        {--out= : Output directory for generated client (defaults to waha.openapi.generated_path)}
        {--driver= : auto|npx|jar|docker|binary}
        {--docker-image= : Docker image for openapi-generator-cli}
        {--jar= : Path to openapi-generator-cli.jar}
        {--binary= : openapi-generator-cli binary path}';

    protected $description = 'Fetch WAHA OpenAPI spec, generate client, and refresh IDE helper (fetch + generate + ide-helper).';

    public function handle(
        FetchOpenApiSpecAction $fetch,
        GenerateOpenApiClientAction $generate,
        GenerateIdeHelperAction $ideHelper,
    ): int {
        $url = (string) ($this->option('url') ?: config('waha.openapi.url', ''));
        if ($url === '') {
            $this->error('OpenAPI URL missing. Set WAHA_OPENAPI_URL or pass --url');

            return self::FAILURE;
        }

        $specPath = (string) ($this->option('spec')
            ?: config('waha.openapi.spec_path', PackagePath::path('resources/openapi/openapi.json')));

        if ($specPath !== '' && ! str_starts_with($specPath, '/')) {
            $specPath = base_path($specPath);
        }

        $outDir = (string) ($this->option('out')
            ?: config('waha.openapi.generated_path', PackagePath::path('src/Generated')));

        if ($outDir !== '' && ! str_starts_with($outDir, '/')) {
            $outDir = base_path($outDir);
        }

        $options = [
            'basic_auth' => [
                'username' => (string) ($this->option('user') ?: config('waha.openapi.basic_auth.username', '')),
                'password' => (string) ($this->option('pass') ?: config('waha.openapi.basic_auth.password', '')),
            ],
            'fetch_timeout_seconds' => (int) config('waha.openapi.timeout_seconds', 30),
            'generator' => [
                'driver' => (string) ($this->option('driver') ?: config('waha.openapi.generator.driver', 'auto')),
                'docker_image' => (string) ($this->option('docker-image') ?: config('waha.openapi.generator.docker_image', 'openapitools/openapi-generator-cli:v7.6.0')),
                'jar' => (string) ($this->option('jar') ?: config('waha.openapi.generator.jar', '')),
                'binary' => (string) ($this->option('binary') ?: config('waha.openapi.generator.binary', 'openapi-generator-cli')),
                'timeout_seconds' => (int) config('waha.openapi.generator.timeout_seconds', 300),
            ],
        ];

        $action = new UpdateOpenApiAction($fetch, $generate, $ideHelper);

        try {
            $action->execute(
                $url,
                $specPath,
                $outDir,
                function_exists('base_path') ? base_path() : PackagePath::root(),
                $options,
                function (string $msg): void {
                    $this->output->write($msg);
                }
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('OpenAPI updated successfully (spec + generated client + IDE helper).');

        return self::SUCCESS;
    }
}
