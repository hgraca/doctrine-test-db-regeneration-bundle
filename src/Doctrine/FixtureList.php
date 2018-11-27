<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use InvalidArgumentException;

final class FixtureList
{
    /**
     * @var FixtureInterface[]
     */
    private $fixtures;

    /**
     * @var Metadata[]
     */
    private $metadata;

    /**
     * @param Metadata[] $metadata
     * @param FixtureInterface[] $fixtures
     *
     * @throws InvalidArgumentException
     */
    private function __construct(array $metadata, FixtureInterface ...$fixtures)
    {
        $this->fixtures = $fixtures;
        $this->metadata = $metadata;
    }

    public static function constructFromFixturesLoader(Loader $fixturesLoader): self
    {
        $fixtures = $fixturesLoader->getFixtures();

        return new self(Metadata::constructFromFixtures($fixtures), ...$fixtures);
    }

    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
