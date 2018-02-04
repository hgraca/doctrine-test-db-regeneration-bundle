<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

final class Md5 extends AbstractStaticClass
{
    use NativeOverride;

    public static function md5_file(string $filePath): string
    {
        if (isset(self::$overrideList['md5_file'])) {
            return (self::$overrideList['md5_file'])($filePath);
        }

        return md5_file($filePath);
    }

    public static function override_md5_file(callable $callable): void
    {
        self::override('md5_file', $callable);
    }

    public static function md5(string $filePath): string
    {
        if (isset(self::$overrideList['md5'])) {
            return (self::$overrideList['md5'])($filePath);
        }

        return md5($filePath);
    }

    public static function override_md5(callable $callable): void
    {
        self::override('md5', $callable);
    }
}
