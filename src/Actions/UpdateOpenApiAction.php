<?php

namespace Vendor\Waha\Actions;

final class UpdateOpenApiAction
{
    public function __construct(
        private readonly FetchOpenApiSpecAction $fetch,
        private readonly GenerateOpenApiClientAction $generate,
        private readonly GenerateIdeHelperAction $ideHelper,
    ) {}

    /**
     * @param  non-empty-string  $url
     * @param  non-empty-string  $specPath
     * @param  non-empty-string  $outDir
     * @param  non-empty-string  $projectRoot
     * @param array{
     *   basic_auth?: array{username?:string, password?:string},
     *   fetch_timeout_seconds?: int,
     *   generator?: array{
     *     driver?: 'auto'|'npx'|'jar'|'docker'|'binary',
     *     docker_image?: string,
     *     jar?: string,
     *     binary?: string,
     *     timeout_seconds?: int
     *   }
     * } $options
     * @param  callable(string):void|null  $logger
     */
    public function execute(string $url, string $specPath, string $outDir, string $projectRoot, array $options = [], ?callable $logger = null): void
    {
        $this->fetch->execute(
            $url,
            $specPath,
            (array) ($options['basic_auth'] ?? []),
            (int) ($options['fetch_timeout_seconds'] ?? 30),
        );

        $this->generate->execute(
            $specPath,
            $outDir,
            (array) ($options['generator'] ?? []),
            $logger,
        );

        $this->ideHelper->execute($specPath, $projectRoot);
        $logger && $logger("IDE helper updated.\n");
    }
}
