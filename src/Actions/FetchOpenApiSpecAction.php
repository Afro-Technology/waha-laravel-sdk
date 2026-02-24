<?php

namespace Vendor\Waha\Actions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class FetchOpenApiSpecAction
{
    private Client $http;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?? new Client;
    }

    /**
     * @param  non-empty-string  $url
     * @param  non-empty-string  $outPath
     * @param  array{username?:string, password?:string}  $basicAuth
     */
    public function execute(string $url, string $outPath, array $basicAuth = [], int $timeoutSeconds = 30): void
    {
        $dir = dirname($outPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $opts = [
            'timeout' => $timeoutSeconds,
            'http_errors' => true,
        ];

        $u = (string) ($basicAuth['username'] ?? '');
        $p = (string) ($basicAuth['password'] ?? '');
        if ($u !== '' || $p !== '') {
            $opts['auth'] = [$u, $p];
        }

        try {
            $res = $this->http->request('GET', $url, $opts);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch OpenAPI spec: '.$e->getMessage(), 0, $e);
        }

        $body = (string) $res->getBody();

        // Best-effort pretty-print if it's JSON (keeps diffs sane).
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($pretty)) {
                $body = $pretty."\n";
            }
        }

        $ok = @file_put_contents($outPath, $body);
        if ($ok === false) {
            throw new \RuntimeException("Failed to write spec to: {$outPath}");
        }
    }
}
