<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

interface MigrationsExecutorInterface
{
    /**
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     * @throws \Exception
     */
    public function execute(string ...$versionList): void;
}
