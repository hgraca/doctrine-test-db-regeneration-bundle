<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\NativeOverride;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

final class NativeOverrideTest extends AbstractUnitTest
{
    /**
     * @throws \ReflectionException
     */
    protected function tearDown(): void
    {
        ReflectionHelper::setStaticProtectedProperty(NativeOverrideTestClass::class, 'overrideList', []);
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function override_sets_the_callable_as_an_override(): void
    {
        $overrideList = ReflectionHelper::getStaticProtectedProperty(NativeOverrideTestClass::class, 'overrideList');
        self::assertFalse(isset($overrideList[NativeOverrideTestClass::OVERRIDE_FUNCTION_NAME]));

        NativeOverrideTestClass::overrideSomething(
            function (): void {
            }
        );

        $overrideList = ReflectionHelper::getStaticProtectedProperty(NativeOverrideTestClass::class, 'overrideList');
        self::assertTrue(isset($overrideList[NativeOverrideTestClass::OVERRIDE_FUNCTION_NAME]));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function resetOverrides(): void
    {
        NativeOverrideTestClass::overrideSomething(
            function (): void {
            }
        );
        NativeOverrideTestClass::resetOverrides();
        $overrideList = ReflectionHelper::getStaticProtectedProperty(NativeOverrideTestClass::class, 'overrideList');
        self::assertFalse(isset($overrideList[NativeOverrideTestClass::OVERRIDE_FUNCTION_NAME]));
    }
}

final class NativeOverrideTestClass
{
    public const OVERRIDE_FUNCTION_NAME = 'something';

    use NativeOverride;

    public static function overrideSomething(callable $callable): void
    {
        self::override(self::OVERRIDE_FUNCTION_NAME, $callable);
    }
}
