<?php

namespace Vendor\Waha\Console;

use Illuminate\Console\Command;
use Vendor\Waha\Actions\GenerateIdeHelperAction;
use Vendor\Waha\Support\PackagePath;

final class GenerateIdeHelperCommand extends Command
{
    protected $signature = 'waha:openapi:ide-helper {--spec= : Override OpenAPI spec path}';

    protected $description = 'Generate IDE helper PHPDoc for Waha Facade / proxies (OpenAPI-driven).';

    public function handle(GenerateIdeHelperAction $action): int
    {
        $specPath = (string) ($this->option('spec') ?: config('waha.openapi.spec_path', PackagePath::path('resources/openapi/openapi.json')));
        if ($specPath !== '' && ! str_starts_with($specPath, '/')) {
            $specPath = base_path($specPath);
        }

        $projectRoot = function_exists('base_path') ? base_path() : PackagePath::root();

        try {
            $action->execute($specPath, $projectRoot);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Waha IDE helper updated (Facade + ManagerProxy + ApiProxy + Tag proxies). Re-index your IDE if autocomplete does not update immediately.');

        return self::SUCCESS;
    }
}
