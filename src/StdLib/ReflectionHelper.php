<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

final class ReflectionHelper extends AbstractStaticClass
{
    /**
     * @throws ReflectionException
     */
    public static function getProtectedProperty($object, string $propertyName)
    {
        $class = new ReflectionClass(\get_class($object));

        $property = static::getReflectionProperty($class, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @throws ReflectionException
     */
    public static function setProtectedProperty($object, string $propertyName, $value): void
    {
        $class = new ReflectionClass(\get_class($object));

        $property = static::getReflectionProperty($class, $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * @throws ReflectionException
     *
     * @return mixed
     */
    public static function instantiateWithoutConstructor(string $classFqcn)
    {
        $class = new ReflectionClass($classFqcn);

        return $class->newInstanceWithoutConstructor();
    }

    /**
     * @throws ReflectionException
     */
    public static function getClassFilename(string $classFqcn): string
    {
        $class = new ReflectionClass($classFqcn);

        return $class->getFileName();
    }

    /**
     * @throws ReflectionException
     */
    public static function getClassFilemtime(string $classFqcn): int
    {
        $class = new ReflectionClass($classFqcn);

        return filemtime($class->getFileName());
    }

    /**
     * @throws ReflectionException
     */
    public static function getStaticProtectedProperty(string $classFqcn, string $propertyName)
    {
        $class = new ReflectionClass($classFqcn);

        return $class->getStaticProperties()[$propertyName];
    }

    /**
     * @throws ReflectionException
     */
    public static function setStaticProtectedProperty(string $classFqcn, string $propertyName, $value): void
    {
        $class = new ReflectionClass($classFqcn);

        $reflectedProperty = $class->getProperty($propertyName);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);
    }

    /**
     * @throws ReflectionException
     */
    private static function getReflectionProperty(ReflectionClass $class, string $propertyName): ReflectionProperty
    {
        try {
            return $class->getProperty($propertyName);
        } catch (ReflectionException $e) {
            $parentClass = $class->getParentClass();
            if ($parentClass === false) {
                throw $e;
            }

            return static::getReflectionProperty($parentClass, $propertyName);
        }
    }
}
