<?php

namespace Vendor\Waha\Routing;

use Vendor\Waha\Contracts\PinStore;
use Vendor\Waha\Contracts\SessionRouter;

class PinningRouter implements SessionRouter
{
    public function __construct(private PinStore $pins, private string $defaultHostKey) {}

    public function resolveHostKey(?string $hostKey, ?string $sessionName): string
    {
        if ($hostKey) {
            return $hostKey;
        }
        if ($sessionName) {
            $pinned = $this->pins->getHostForSession($sessionName);
            if ($pinned) {
                return $pinned;
            }
        }

        return $this->defaultHostKey;
    }
}
