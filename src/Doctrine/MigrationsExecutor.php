<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class MigrationsExecutor implements MigrationsExecutorInterface
{
    const UP = 'up';

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @throws \ErrorException
     */
    public function __construct(ContainerInterface $container, Connection $connection)
    {
        $this->config = new Configuration($connection);
        DoctrineCommand::configureMigrations($container, $this->config);
    }

    /**
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     * @throws \Exception
     */
    public function execute(string ...$versionList): void
    {
        foreach ($versionList as $version) {
            $this->config->getVersion($version)->execute(self::UP);
        }
    }
}
