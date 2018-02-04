<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\AbstractStaticClass;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use ReflectionMethod;

final class AbstractStaticClassTest extends AbstractUnitTest
{
    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function instantiation_is_not_possible(): void
    {
        $reflectionMethod = new ReflectionMethod(AbstractStaticClass::class, '__construct');

        self::assertTrue($reflectionMethod->isProtected());
    }
}
