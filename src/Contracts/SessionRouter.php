<?php

namespace Vendor\Waha\Contracts;

/**
 * Resolves which host should be used for a given WAHA session.
 */
interface SessionRouter
{
    public function resolveHostKey(?string $hostKey, ?string $sessionName): string;
}
