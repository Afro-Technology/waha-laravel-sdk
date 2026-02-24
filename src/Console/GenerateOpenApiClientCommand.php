<?php

namespace Vendor\Waha\Console;

use Illuminate\Console\Command;
use Vendor\Waha\Actions\GenerateOpenApiClientAction;
use Vendor\Waha\Support\PackagePath;

final class GenerateOpenApiClientCommand extends Command
{
    protected $signature = 'waha:openapi:generate
        {--spec= : Path to openapi.json}
        {--out= : Output directory for generated client}
        {--driver= : auto|npx|jar|docker|binary}
        {--docker-image= : Docker image for openapi-generator-cli}
        {--jar= : Path to openapi-generator-cli.jar}
        {--binary= : openapi-generator-cli binary path (if installed)}
    ';

    protected $description = 'Generate PHP client from WAHA OpenAPI spec using openapi-generator (supports local or docker drivers)';

    public function handle(GenerateOpenApiClientAction $action): int
    {
        $spec = (string) ($this->option('spec')
            ?: config('waha.openapi.spec_path', PackagePath::path('resources/openapi/openapi.json')));

        if ($spec !== '' && ! str_starts_with($spec, '/')) {
            $spec = base_path($spec);
        }

        $out = (string) ($this->option('out')
            ?: config('waha.openapi.generated_path', PackagePath::path('src/Generated')));

        if ($out !== '' && ! str_starts_with($out, '/')) {
            $out = base_path($out);
        }

        $opts = [
            'driver' => (string) ($this->option('driver') ?: config('waha.openapi.generator.driver', 'auto')),
            'docker_image' => (string) ($this->option('docker-image') ?: config('waha.openapi.generator.docker_image', 'openapitools/openapi-generator-cli:v7.6.0')),
            'jar' => (string) ($this->option('jar') ?: config('waha.openapi.generator.jar', '')),
            'binary' => (string) ($this->option('binary') ?: config('waha.openapi.generator.binary', 'openapi-generator-cli')),
            'timeout_seconds' => (int) config('waha.openapi.generator.timeout_seconds', 300),
        ];

        try {
            $action->execute($spec, $out, $opts, function (string $msg): void {
                $this->output->write($msg);
            });
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Generated client into: {$out}");
        $this->info('If classes are not found, run: composer dump-autoload');

        return self::SUCCESS;
    }
}
