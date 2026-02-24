<?php

namespace Vendor\Waha\Support;

final class PackagePath
{
    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function path(string $relative): string
    {
        return rtrim(self::root(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .ltrim($relative, DIRECTORY_SEPARATOR);
    }
}
