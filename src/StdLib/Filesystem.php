<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

use Directory;

final class Filesystem extends AbstractStaticClass
{
    use NativeOverride;

    public static function filemtime(string $filePath): int
    {
        if (isset(self::$overrideList['filemtime'])) {
            return (self::$overrideList['filemtime'])($filePath);
        }

        return \filemtime($filePath);
    }

    public static function override_filemtime(callable $callable): void
    {
        self::override('filemtime', $callable);
    }

    public static function is_dir(string $filePath): bool
    {
        if (isset(self::$overrideList['is_dir'])) {
            return (self::$overrideList['is_dir'])($filePath);
        }

        return \is_dir($filePath);
    }

    public static function override_is_dir(callable $callable): void
    {
        self::override('is_dir', $callable);
    }

    public static function is_file(string $filePath): bool
    {
        if (isset(self::$overrideList['is_file'])) {
            return (self::$overrideList['is_file'])($filePath);
        }

        return \is_file($filePath);
    }

    public static function override_is_file(callable $callable): void
    {
        self::override('is_file', $callable);
    }

    public static function dir(string $filePath): Directory
    {
        if (isset(self::$overrideList['dir'])) {
            return (self::$overrideList['dir'])($filePath);
        }

        return \dir($filePath);
    }

    public static function override_dir(callable $callable): void
    {
        self::override('dir', $callable);
    }

    public static function file_exists(string $filePath): bool
    {
        if (isset(self::$overrideList['file_exists'])) {
            return (self::$overrideList['file_exists'])($filePath);
        }

        return \file_exists($filePath);
    }

    public static function override_file_exists(callable $callable): void
    {
        self::override('file_exists', $callable);
    }

    public static function rename(string $filePath, string $newFilePath): bool
    {
        if (isset(self::$overrideList['rename'])) {
            return (self::$overrideList['rename'])($filePath, $newFilePath);
        }

        return \rename($filePath, $newFilePath);
    }

    public static function override_rename(callable $callable): void
    {
        self::override('rename', $callable);
    }

    public static function copy(string $filePath, string $newFilePath): bool
    {
        if (isset(self::$overrideList['copy'])) {
            return (self::$overrideList['copy'])($filePath, $newFilePath);
        }

        return \copy($filePath, $newFilePath);
    }

    public static function override_copy(callable $callable): void
    {
        self::override('copy', $callable);
    }

    public static function unlink(string $filePath): bool
    {
        if (isset(self::$overrideList['unlink'])) {
            return (self::$overrideList['unlink'])($filePath);
        }

        return \unlink($filePath);
    }

    public static function override_unlink(callable $callable): void
    {
        self::override('unlink', $callable);
    }
}
