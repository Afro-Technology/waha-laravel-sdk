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
        if (! is_file($specPath)) {
            throw new \RuntimeException("Spec file not found: {$specPath}");
        }

        if (! is_dir($outDir)) {
            @mkdir($outDir, 0777, true);
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
            'npx' => $this->makeNpxProcess($specPath, $outDir),
            'jar' => $this->makeJarProcess($specPath, $outDir, (string) ($options['jar'] ?? '')),
            'binary' => $this->makeBinaryProcess($specPath, $outDir, (string) ($options['binary'] ?? 'openapi-generator-cli')),
            'docker' => $this->makeDockerProcess($specPath, $outDir, (string) ($options['docker_image'] ?? 'openapitools/openapi-generator-cli:v7.6.0')),
            default => null,
        };

        if (! $process) {
            throw new \RuntimeException("Unknown or unsupported driver: {$driver}");
        }

        $process->setTimeout((int) ($options['timeout_seconds'] ?? 300));
        $process->run(function ($type, $buffer) use ($logger) {
            $logger && $logger((string) $buffer);
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('OpenAPI generation failed.');
        }

        $logger && $logger("Generated client into: {$outDir}\n");
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
            '--global-property=apiTests=false,modelTests=false,apiDocs=false,modelDocs=false',
            '--additional-properties=invokerPackage=Vendor\\Waha\\Generated,packageName=VendorWahaGenerated,srcBasePath=src/Generated',
        ];
    }

    private function makeDockerProcess(string $specPath, string $outDir, string $image): Process
    {
        $cmd = array_merge(
            [
                'docker', 'run', '--rm',
                '-v', dirname($specPath).':/spec',
                '-v', $outDir.':/out',
                $image,
                'generate',
            ],
            $this->commonArgs('/spec/'.basename($specPath), '/out')
        );

        return new Process($cmd);
    }

    private function makeBinaryProcess(string $specPath, string $outDir, string $binary): Process
    {
        $cmd = array_merge([$binary, 'generate'], $this->commonArgs($specPath, $outDir));

        return new Process($cmd);
    }

    private function makeJarProcess(string $specPath, string $outDir, string $jar): Process
    {
        if ($jar === '' || ! is_file($jar)) {
            throw new \RuntimeException('JAR driver selected but jar path is missing. Provide --jar or set waha.openapi.generator.jar');
        }

        $cmd = array_merge(['java', '-jar', $jar, 'generate'], $this->commonArgs($specPath, $outDir));

        return new Process($cmd);
    }

    private function makeNpxProcess(string $specPath, string $outDir): Process
    {
        $cmd = array_merge(
            ['npx', '--yes', '@openapitools/openapi-generator-cli', 'generate'],
            $this->commonArgs($specPath, $outDir)
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
}
