<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\HashHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

final class HashHelperTest extends AbstractUnitTest
{
    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @throws \Exception
     */
    public function hashDirectory_throws_exception_if_dir_does_not_exist(): void
    {
        HashHelper::hashDirectory(__DIR__ . '/inexistent_dir');
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function hashDirectory_hashing_the_same_directory_twice_yields_the_same_result(): void
    {
        self::assertEquals(HashHelper::hashDirectory(__DIR__), HashHelper::hashDirectory(__DIR__));
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function hashDirectory_hashing_a_different_directory_yields_a_different_result(): void
    {
        self::assertNotEquals(HashHelper::hashDirectory(__DIR__), HashHelper::hashDirectory(__DIR__ . '/..'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @throws \Exception
     */
    public function hashFile_throws_exception_if_file_does_not_exist(): void
    {
        HashHelper::hashFile(__DIR__ . '/InexistentFile.php');
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function hashFile_hashing_the_same_file_twice_yields_the_same_result(): void
    {
        self::assertEquals(HashHelper::hashFile(__FILE__), HashHelper::hashFile(__FILE__));
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function hashDirectory_hashing_a_different_file_yields_a_different_result(): void
    {
        self::assertNotEquals(
            HashHelper::hashFile(__FILE__),
            HashHelper::hashFile(ReflectionHelper::getClassFilename(HashHelper::class))
        );
    }

    /**
     * @test
     */
    public function hashString_hashing_the_same_string_twice_yields_the_same_result(): void
    {
        self::assertEquals(HashHelper::hashString(__CLASS__), HashHelper::hashString(__CLASS__));
    }

    /**
     * @test
     */
    public function hashString_hashing_a_different_string_yields_a_different_result(): void
    {
        self::assertNotEquals(HashHelper::hashString(__CLASS__), HashHelper::hashString(__METHOD__));
    }
}
