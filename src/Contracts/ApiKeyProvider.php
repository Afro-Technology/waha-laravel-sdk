<?php

namespace Vendor\Waha\Contracts;

interface ApiKeyProvider
{
    public function headerName(string $hostKey): string;

    public function adminKey(string $hostKey): ?string;

    public function sessionKey(string $hostKey, string $sessionName): ?string;

    public function mode(string $hostKey): string;
}
