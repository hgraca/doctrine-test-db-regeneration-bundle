<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Md5;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

final class Md5Test extends AbstractUnitTest
{
    protected function tearDown(): void
    {
        Md5::resetOverrides();
    }

    /**
     * @test
     */
    public function md5_file_works_without_overriding(): void
    {
        self::assertEquals(Md5::md5_file(__FILE__), md5_file(__FILE__));
    }

    /**
     * @test
     */
    public function md5_file_works_with_overriding(): void
    {
        $expected = '123456';

        Md5::override_md5_file(
            function () use ($expected) {
                return $expected;
            }
        );

        self::assertEquals($expected, Md5::md5_file(__FILE__));
    }

    /**
     * @test
     */
    public function md5_works_without_overriding(): void
    {
        self::assertEquals(Md5::md5(__FILE__), md5(__FILE__));
    }

    /**
     * @test
     */
    public function md5_works_with_overriding(): void
    {
        $expected = '123456';

        Md5::override_md5(
            function () use ($expected) {
                return $expected;
            }
        );

        self::assertEquals($expected, Md5::md5(__FILE__));
    }
}
