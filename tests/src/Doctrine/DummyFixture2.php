<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\Doctrine;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class DummyFixture2 implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}
