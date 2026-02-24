<?php

namespace Vendor\Waha\Debug;

final class WahaDebugStore
{
    /** @var array<string, mixed>|null */
    private ?array $last = null;

    /**
     * @param  array<string, mixed>  $entry
     */
    public function setLast(array $entry): void
    {
        $this->last = $entry;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function last(): ?array
    {
        return $this->last;
    }

    public function lastCurl(): ?string
    {
        $e = $this->last;
        if (! $e || ! isset($e['request']) || ! is_array($e['request'])) {
            return null;
        }

        $req = $e['request'];
        $method = strtoupper((string) ($req['method'] ?? 'GET'));
        $url = (string) ($req['url'] ?? '');
        $headers = $req['headers'] ?? [];
        $body = $req['body'] ?? null;

        $parts = [];
        $parts[] = 'curl -i';
        $parts[] = '-X '.escapeshellarg($method);
        $parts[] = escapeshellarg($url);

        if (is_array($headers)) {
            foreach ($headers as $k => $vals) {
                if (! is_array($vals)) {
                    $vals = [$vals];
                }
                foreach ($vals as $v) {
                    $parts[] = '-H '.escapeshellarg($k.': '.(string) $v);
                }
            }
        }

        if ($body !== null && $body !== '') {
            $parts[] = '--data '.escapeshellarg((string) $body);
        }

        return implode(' \\n  ', $parts);
    }

    public function clear(): void
    {
        $this->last = null;
    }
}
