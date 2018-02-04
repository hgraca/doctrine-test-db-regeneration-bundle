<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Directory;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Filesystem;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

final class FilesystemTest extends AbstractUnitTest
{
    protected function tearDown(): void
    {
        Filesystem::resetOverrides();
    }

    /**
     * @test
     */
    public function filemtime_works_without_overriding(): void
    {
        self::assertEquals(Filesystem::filemtime(__FILE__), filemtime(__FILE__));
    }

    /**
     * @test
     */
    public function filemtime_works_with_overriding(): void
    {
        $time = 123456;

        Filesystem::override_filemtime(
            function () use ($time) {
                return $time;
            }
        );

        self::assertEquals($time, Filesystem::filemtime(__FILE__));
    }

    /**
     * @test
     */
    public function dir_works_without_overriding(): void
    {
        self::assertEquals(Filesystem::dir(__DIR__)->path, dir(__DIR__)->path);
    }

    /**
     * @test
     */
    public function dir_works_with_overriding(): void
    {
        $directory = new Directory();

        Filesystem::override_dir(
            function () use ($directory) {
                return $directory;
            }
        );

        self::assertEquals($directory, Filesystem::dir(__DIR__));
    }

    /**
     * @test
     */
    public function is_dir_works_without_overriding(): void
    {
        self::assertEquals(Filesystem::is_dir(__DIR__), is_dir(__DIR__));
    }

    /**
     * @test
     */
    public function is_dir_works_with_overriding(): void
    {
        Filesystem::override_is_dir(
            function () {
                return false;
            }
        );

        self::assertFalse(Filesystem::is_dir(__DIR__));
    }

    /**
     * @test
     */
    public function is_file_works_without_overriding(): void
    {
        self::assertEquals(Filesystem::is_file(__FILE__), is_file(__FILE__));
    }

    /**
     * @test
     */
    public function is_file_works_with_overriding(): void
    {
        Filesystem::override_is_file(
            function () {
                return false;
            }
        );

        self::assertFalse(Filesystem::is_file(__FILE__));
    }

    /**
     * @test
     */
    public function file_exists_works_without_overriding(): void
    {
        self::assertEquals(Filesystem::file_exists(__FILE__), file_exists(__FILE__));
    }

    /**
     * @test
     */
    public function file_exists_works_with_overriding(): void
    {
        Filesystem::override_file_exists(
            function () {
                return false;
            }
        );

        self::assertFalse(Filesystem::file_exists(__FILE__));
    }

    /**
     * @test
     */
    public function rename_works_without_overriding(): void
    {
        self::assertTrue(Filesystem::rename(__FILE__, __FILE__));
    }

    /**
     * @test
     */
    public function rename_works_with_overriding(): void
    {
        Filesystem::override_rename(
            function () {
                return false;
            }
        );

        self::assertFalse(Filesystem::rename(__FILE__, __FILE__));
    }

    /**
     * @test
     */
    public function copy_works_without_overriding(): void
    {
        $destinationFile = sys_get_temp_dir() . '/' . time();
        self::assertTrue(Filesystem::copy(__FILE__, $destinationFile));
        self::assertFileExists($destinationFile);
    }

    /**
     * @test
     */
    public function copy_works_with_overriding(): void
    {
        Filesystem::override_copy(
            function ($source, $destination) {
                return copy($source, $destination . '.override');
            }
        );

        $destinationFile = sys_get_temp_dir() . '/' . time();
        self::assertTrue(Filesystem::copy(__FILE__, $destinationFile));
        self::assertFileExists($destinationFile . '.override');
    }

    /**
     * @test
     */
    public function unlink_works_without_overriding(): void
    {
        $destinationFile = sys_get_temp_dir() . '/' . time();
        file_put_contents($destinationFile, '');
        self::assertFileExists($destinationFile);
        self::assertTrue(Filesystem::unlink($destinationFile));
        self::assertFileNotExists($destinationFile);
    }

    /**
     * @test
     */
    public function unlink_works_with_overriding(): void
    {
        Filesystem::override_unlink(
            function () {
                return true;
            }
        );

        $destinationFile = sys_get_temp_dir() . '/' . time();
        file_put_contents($destinationFile, '');
        self::assertFileExists($destinationFile);
        self::assertTrue(Filesystem::unlink($destinationFile));
        self::assertFileExists($destinationFile);
    }
}
