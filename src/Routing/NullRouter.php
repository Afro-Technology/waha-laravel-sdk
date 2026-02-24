<?php

namespace Vendor\Waha\Routing;

use Vendor\Waha\Contracts\SessionRouter;

class NullRouter implements SessionRouter
{
    public function __construct(private string $defaultHostKey) {}

    public function resolveHostKey(?string $hostKey, ?string $sessionName): string
    {
        return $hostKey ?: $this->defaultHostKey;
    }
}
