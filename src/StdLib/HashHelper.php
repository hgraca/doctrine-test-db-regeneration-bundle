<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

use Exception;
use RuntimeException;

final class HashHelper extends AbstractStaticClass
{
    /**
     * @throws Exception
     */
    public static function hashDirectory(string $path): string
    {
        if (!Filesystem::is_dir($path)) {
            throw new RuntimeException("The given directory '$path' can not be found.");
        }

        $files = [];
        $dir = Filesystem::dir($path);

        while (($file = $dir->read()) !== false) {
            if ($file !== '.' && $file !== '..') {
                $files[] = Filesystem::is_dir($path . '/' . $file)
                    ? self::hashDirectory($path . '/' . $file)
                    : self::hashFile($path . '/' . $file);
            }
        }

        $dir->close();

        return self::hashString(implode('', $files));
    }

    public static function hashFile(string $path): string
    {
        if (!Filesystem::is_file($path)) {
            throw new RuntimeException("The given file '$path' can not be found.");
        }

        return Md5::md5_file($path);
    }

    public static function hashString(string $string): string
    {
        return Md5::md5($string);
    }
}
