<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\StdLib;

use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

final class ReflectionHelperTest extends AbstractUnitTest
{
    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function getProtectedProperty_from_object_class(): void
    {
        $value = 7;
        $object = new DummyClass($value);

        self::assertEquals($value, ReflectionHelper::getProtectedProperty($object, 'var'));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function getProtectedProperty_from_object_parent_class(): void
    {
        $value = 7;
        $parentValue = 19;
        $object = new DummyClass($value, $parentValue);

        self::assertEquals($parentValue, ReflectionHelper::getProtectedProperty($object, 'parentVar'));
    }

    /**
     * @test
     *
     * @expectedException \ReflectionException
     *
     * @throws \ReflectionException
     */
    public function getProtectedProperty_throws_exception_if_not_found(): void
    {
        $object = new DummyClass();

        ReflectionHelper::getProtectedProperty($object, 'inexistentVar');
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function setProtectedProperty(): void
    {
        $newValue = 'something new';
        $object = new DummyClass();
        $this->assertNotSame($newValue, $object->getTestProperty());

        ReflectionHelper::setProtectedProperty($object, 'testProperty', $newValue);
        $this->assertSame($newValue, $object->getTestProperty());
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function setProtectedProperty_defined_in_parent_class(): void
    {
        $newValue = 'something new';
        $object = new DummyClass();
        $this->assertNotSame($newValue, $object->getParentTestProperty());

        ReflectionHelper::setProtectedProperty($object, 'parentTestProperty', $newValue);
        $this->assertSame($newValue, $object->getParentTestProperty());
    }

    /**
     * @test
     * @expectedException \ReflectionException
     * @expectedExceptionMessage Property i_dont_exist does not exist
     */
    public function setProtectedProperty_fails_when_cant_find_the_property(): void
    {
        $object = new DummyClass();
        ReflectionHelper::setProtectedProperty($object, 'i_dont_exist', 'non existent');
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function instantiateWithoutConstructor_does_not_use_the_constructor(): void
    {
        $object = ReflectionHelper::instantiateWithoutConstructor(DummyClass::class);
        $this->assertNull($object->getAnotherVar());
    }
}
