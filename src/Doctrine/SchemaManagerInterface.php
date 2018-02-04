<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Hgraca\DoctrineTestDbRegenerationBundle\Symfony\TestContainer;

interface SchemaManagerInterface
{
    public static function constructUsingTestContainer(TestContainer $testContainer = null): self;

    public function createTestDatabaseBackup(bool $shouldReuseExistingDbBkp = false): void;

    public function restoreTestDatabase(): void;

    public function removeTestDatabase(): void;

    public function getLoadedReferenceRepository(): ProxyReferenceRepository;
}
