<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Support;

use Throwable;

final class ErrorFingerprint
{
    /**
     * Short, deterministic reference for an exception, keyed by where it was
     * thrown rather than its message, so the same bug dedupes across occurrences
     * even when the message contains variable data (ids, user input, etc).
     */
    public static function make(Throwable $e): string
    {
        return strtoupper(substr(sha1($e::class.'|'.$e->getFile().'|'.$e->getLine()), 0, 10));
    }
}
