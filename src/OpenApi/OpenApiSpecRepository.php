<?php

namespace Vendor\Waha\OpenApi;

final class OpenApiSpecRepository
{
    public function __construct(
        private readonly string $specPath,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        if (! is_file($this->specPath)) {
            throw new \RuntimeException("WAHA OpenAPI spec not found at: {$this->specPath}");
        }

        $json = file_get_contents($this->specPath);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new \RuntimeException("Invalid OpenAPI JSON at: {$this->specPath}");
        }

        return $data;
    }
}
