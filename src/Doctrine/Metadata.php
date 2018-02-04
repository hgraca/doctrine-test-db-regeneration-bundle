<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Filesystem;
use ReflectionClass;

final class Metadata
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var int
     */
    public $lastModifiedAt;

    private function __construct(string $filename, int $lastModifiedAt)
    {
        $this->filename = $filename;
        $this->lastModifiedAt = $lastModifiedAt;
    }

    /**
     * @return Metadata[]
     */
    public static function constructFromFixtures(array $fixtures): array
    {
        return static::sortList(
            array_map(
                function (FixtureInterface $fixture): Metadata {
                    return static::constructFromReflection(new ReflectionClass($fixture));
                },
                $fixtures
            )
        );
    }

    /**
     * @param ClassMetadata[] $entities
     *
     * @return Metadata[]
     */
    public static function constructFromEntities(array $entities): array
    {
        return static::sortList(
            array_map(
                function (ClassMetadata $entity): Metadata {
                    return static::constructFromReflection($entity->getReflectionClass());
                },
                $entities
            )
        );
    }

    private static function constructFromReflection(ReflectionClass $classInfo): self
    {
        $filename = $classInfo->getFileName();

        return new self($filename, Filesystem::filemtime($filename));
    }

    private static function sortList(array $list): array
    {
        usort(
            $list,
            function (Metadata $one, Metadata $other): int {
                return $one->compare($other);
            }
        );

        return $list;
    }

    private function compare(self $other): int
    {
        $dateDiff = $this->lastModifiedAt <=> $other->lastModifiedAt;

        if ($dateDiff === 0) {
            return strcmp($this->filename, $other->filename);
        }

        return $dateDiff;
    }
}
