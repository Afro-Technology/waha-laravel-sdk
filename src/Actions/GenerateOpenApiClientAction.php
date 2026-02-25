<?php

namespace Vendor\Waha\Actions;

use Symfony\Component\Process\Process;

final class GenerateOpenApiClientAction
{
    /**
     * @param  non-empty-string  $specPath
     * @param  non-empty-string  $outDir
     * @param array{
     *   driver?: 'auto'|'npx'|'jar'|'docker'|'binary',
     *   docker_image?: string,
     *   jar?: string,
     *   binary?: string,
     *   timeout_seconds?: int
     * } $options
     * @param  callable(string):void|null  $logger
     */
    public function execute(string $specPath, string $outDir, array $options = [], ?callable $logger = null): void
    {
        $specAbs = $this->toAbsolutePath($specPath);
        if (! is_file($specAbs)) {
            throw new \RuntimeException("Spec file not found: {$specAbs}");
        }

        $outAbs = $this->toAbsolutePath($outDir);
        if (! is_dir($outAbs)) {
            @mkdir($outAbs, 0777, true);
        }

        $driver = (string) ($options['driver'] ?? 'auto');
        if ($driver === 'auto') {
            $driver = $this->detectBestDriver($options);
            if ($driver === '') {
                throw new \RuntimeException('No OpenAPI generator driver available. Install one of: Docker, Node (npx openapi-generator-cli), or Java (openapi-generator-cli.jar) / binary.');
            }
            $logger && $logger("Using generator driver: {$driver}\n");
        }

        $process = match ($driver) {
            'npx' => $this->makeNpxProcess($specAbs, $outAbs),
            'jar' => $this->makeJarProcess($specAbs, $outAbs, (string) ($options['jar'] ?? '')),
            'binary' => $this->makeBinaryProcess($specAbs, $outAbs, (string) ($options['binary'] ?? 'openapi-generator-cli')),
            'docker' => $this->makeDockerProcess($specAbs, $outAbs, (string) ($options['docker_image'] ?? 'openapitools/openapi-generator-cli:v7.6.0')),
            default => null,
        };

        if (! $process) {
            throw new \RuntimeException("Unknown or unsupported driver: {$driver}");
        }

        $process->setTimeout((int) ($options['timeout_seconds'] ?? 300));
        $process->run(function ($type, $buffer) use ($logger): void {
            $logger && $logger((string) $buffer);
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('OpenAPI generation failed.');
        }

        $logger && $logger("Generated client into: {$outAbs}\n");
    }

    /**
     * @return list<string>
     */
    private function commonArgs(string $spec, string $out): array
    {
        return [
            '-i', $spec,
            '-g', 'php',
            '-o', $out,
            '--skip-validate-spec',
            '--global-property=apis,models,apiTests=false,modelTests=false,apiDocs=false,modelDocs=false'
            .',supportingFiles=ApiException.php:Configuration.php:HeaderSelector.php:ObjectSerializer.php',
            '--additional-properties=invokerPackage=Vendor\\Waha\\Generated,packageName=VendorWahaGenerated,srcBasePath=Generated',
        ];
    }

    private function makeDockerProcess(string $specAbs, string $outAbs, string $image): Process
    {
        // Docker volume mount için host path absolute olmak zorunda.
        $specDir = dirname($specAbs);

        $cmd = array_merge(
            [
                'docker', 'run', '--rm',
                '-v', $specDir.':/spec',
                '-v', $outAbs.':/out',
                $image,
                'generate',
            ],
            $this->commonArgs('/spec/'.basename($specAbs), '/out')
        );

        return new Process($cmd);
    }

    private function makeBinaryProcess(string $specAbs, string $outAbs, string $binary): Process
    {
        $cmd = array_merge([$binary, 'generate'], $this->commonArgs($specAbs, $outAbs));

        return new Process($cmd);
    }

    private function makeJarProcess(string $specAbs, string $outAbs, string $jar): Process
    {
        if ($jar === '' || ! is_file($jar)) {
            throw new \RuntimeException('JAR driver selected but jar path is missing. Provide --jar or set waha.openapi.generator.jar');
        }

        $cmd = array_merge(['java', '-jar', $jar, 'generate'], $this->commonArgs($specAbs, $outAbs));

        return new Process($cmd);
    }

    private function makeNpxProcess(string $specAbs, string $outAbs): Process
    {
        $cmd = array_merge(
            ['npx', '--yes', '@openapitools/openapi-generator-cli', 'generate'],
            $this->commonArgs($specAbs, $outAbs)
        );

        return new Process($cmd);
    }

    /**
     * @param  array<string,mixed>  $options
     */
    private function detectBestDriver(array $options): string
    {
        if ($this->commandAvailable('npx') && $this->npxGeneratorAvailable()) {
            return 'npx';
        }

        $jar = (string) ($options['jar'] ?? '');
        if ($jar !== '' && is_file($jar) && $this->commandAvailable('java')) {
            return 'jar';
        }

        $bin = (string) ($options['binary'] ?? 'openapi-generator-cli');
        if ($bin !== '' && $this->commandAvailable($bin)) {
            return 'binary';
        }

        if ($this->commandAvailable('docker') && $this->dockerDaemonAvailable()) {
            return 'docker';
        }

        return '';
    }

    private function commandAvailable(string $cmd): bool
    {
        $process = Process::fromShellCommandline(PHP_OS_FAMILY === 'Windows' ? "where {$cmd}" : "command -v {$cmd}");
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful();
    }

    private function dockerDaemonAvailable(): bool
    {
        $process = new Process(['docker', 'info']);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful();
    }

    private function npxGeneratorAvailable(): bool
    {
        $process = new Process(['npx', '--yes', '@openapitools/openapi-generator-cli', 'version']);
        $process->setTimeout(30);
        $process->run();

        return $process->isSuccessful();
    }

    private function toAbsolutePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        // Unix absolute
        if (str_starts_with($path, '/')) {
            return $path;
        }

        // Windows absolute (C:\ or C:/)
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1) {
            return $path;
        }

        $cwd = getcwd();
        if ($cwd === false) {
            return $path;
        }

        return rtrim($cwd, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }
}
