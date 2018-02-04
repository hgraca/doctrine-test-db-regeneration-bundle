<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HgracaDoctrineTestDbRegenerationExtension extends Extension
{
    /**
     * @var array
     */
    private $processedConfig;

    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $configuration = new Configuration();
        $this->processedConfig = $this->processConfiguration($configuration, $configs);

        $containerBuilder->setParameter(
            Configuration::ROOT . '.' . Configuration::FIXTURES_LOADER_SERVICE_ID,
            $this->getFixturesLoaderServiceId()
        );

        $containerBuilder->setParameter(
            Configuration::ROOT . '.' . Configuration::DOCTRINE_SERVICE_ID,
            $this->getDoctrineServiceId()
        );

        $containerBuilder->setParameter(
            Configuration::ROOT . '.' . Configuration::TEST_DB_BKP_PATH,
            $this->getTestDbBkpPath()
        );
    }

    public function getFixturesLoaderServiceId(): string
    {
        return $this->processedConfig[Configuration::FIXTURES_LOADER_SERVICE_ID];
    }

    public function getDoctrineServiceId(): string
    {
        return $this->processedConfig[Configuration::DOCTRINE_SERVICE_ID];
    }

    public function getTestDbBkpPath(): string
    {
        return $this->processedConfig[Configuration::TEST_DB_BKP_PATH];
    }

    private function getExtraServiceList(): array
    {
        return $this->processedConfig[Configuration::EXTRA_SERVICE_LIST] ?? [];
    }

    /**
     * @return string[]
     */
    public function getServicesIdList(): array
    {
        return array_merge(
            [
                $this->getFixturesLoaderServiceId(),
                $this->getDoctrineServiceId(),
            ],
            $this->getExtraServiceList()
        );
    }
}
