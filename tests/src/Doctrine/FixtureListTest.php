<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\Doctrine;

use Doctrine\Common\DataFixtures\Loader;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\FixtureList;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\Metadata;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;

/**
 * @covers \Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\FixtureList
 */
final class FixtureListTest extends AbstractUnitTest
{
    /**
     * @var Loader
     */
    private $loader;

    public function setUp(): void
    {
        $this->loader = new Loader();
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function constructFromFixturesLoader(): void
    {
        $originalFixtureList = [
            new DummyFixture1(),
            new DummyFixture2(),
        ];
        ReflectionHelper::setProtectedProperty($this->loader, 'fixtures', $originalFixtureList);
        $fixtureList = FixtureList::constructFromFixturesLoader($this->loader);

        self::assertEquals($originalFixtureList, $fixtureList->getFixtures());
        foreach ($fixtureList->getMetadata() as $metadata) {
            self::assertInstanceOf(Metadata::class, $metadata);
        }
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function constructFromFixturesLoader_throws_exception_if_there_are_no_fixtures(): void
    {
        FixtureList::constructFromFixturesLoader($this->loader);
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function getFixtures(): void
    {
        /** @var FixtureList $fixtureList */
        $fixtureList = ReflectionHelper::instantiateWithoutConstructor(FixtureList::class);
        ReflectionHelper::setProtectedProperty($fixtureList, 'fixtures', $expectedFixtures = ['aaa']);

        self::assertEquals($expectedFixtures, $fixtureList->getFixtures());
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function getMetadata(): void
    {
        /** @var FixtureList $fixtureList */
        $fixtureList = ReflectionHelper::instantiateWithoutConstructor(FixtureList::class);
        ReflectionHelper::setProtectedProperty($fixtureList, 'metadata', $expectedMetadata = ['aaa']);

        self::assertEquals($expectedMetadata, $fixtureList->getMetadata());
    }
}
