<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\Metadata;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Mockery;
use ReflectionClass;

final class MetadataTest extends AbstractUnitTest
{
    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function constructFromFixtures(): void
    {
        $originalFixtureList = [
            new DummyFixture1(),
            new DummyFixture2(),
        ];
        $metadataList = Metadata::constructFromFixtures($originalFixtureList);

        self::assertEquals(ReflectionHelper::getClassFilename(DummyFixture1::class), $metadataList[0]->filename);
        self::assertEquals(ReflectionHelper::getClassFilemtime(DummyFixture1::class), $metadataList[0]->lastModifiedAt);

        self::assertEquals(ReflectionHelper::getClassFilename(DummyFixture2::class), $metadataList[1]->filename);
        self::assertEquals(ReflectionHelper::getClassFilemtime(DummyFixture2::class), $metadataList[1]->lastModifiedAt);
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function constructFromEntities(): void
    {
        $mock1 = Mockery::mock(ClassMetadata::class);
        $mock1->shouldReceive('getReflectionClass')->andReturn(new ReflectionClass(DummyFixture1::class));
        $mock2 = Mockery::mock(ClassMetadata::class);
        $mock2->shouldReceive('getReflectionClass')->andReturn(new ReflectionClass(DummyFixture2::class));
        $entityMetadataList = [$mock1, $mock2];
        $metadataList = Metadata::constructFromEntities($entityMetadataList);

        self::assertEquals(ReflectionHelper::getClassFilename(DummyFixture1::class), $metadataList[0]->filename);
        self::assertEquals(ReflectionHelper::getClassFilemtime(DummyFixture1::class), $metadataList[0]->lastModifiedAt);

        self::assertEquals(ReflectionHelper::getClassFilename(DummyFixture2::class), $metadataList[1]->filename);
        self::assertEquals(ReflectionHelper::getClassFilemtime(DummyFixture2::class), $metadataList[1]->lastModifiedAt);
    }
}
