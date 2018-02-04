<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

use Closure;

trait NativeOverride
{
    /**
     * @var closure[]
     */
    private static $overrideList = [];

    public static function resetOverrides(): void
    {
        self::$overrideList = [];
    }

    private static function override(string $functionName, callable $callable): void
    {
        self::$overrideList[$functionName] = $callable;
    }
}
