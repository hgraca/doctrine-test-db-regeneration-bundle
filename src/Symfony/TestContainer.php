<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Symfony;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\EntityManagerInterface;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Configuration;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\CreateDbRegenerationServiceLocatorCompilerPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\KernelInterface;

final class TestContainer
{
    public const KEY_FIXTURES_LOADER = Configuration::ROOT . '.' . Configuration::FIXTURES_LOADER_SERVICE_ID;
    public const KEY_DOCTRINE_SERVICE_ID = Configuration::ROOT . '.' . Configuration::DOCTRINE_SERVICE_ID;
    public const KEY_TEST_DB_BKP_PATH = Configuration::ROOT . '.' . Configuration::TEST_DB_BKP_PATH;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?? $this->createContainer();
    }

    public function getFixturesLoader(): Loader
    {
        return $this->getService(
            $this->getParameter(self::KEY_FIXTURES_LOADER)
        );
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->getService($this->getParameter(self::KEY_DOCTRINE_SERVICE_ID))
            ->getManager();
    }

    public function getDbBkpDir(): string
    {
        return $this->getParameter(self::KEY_TEST_DB_BKP_PATH);
    }

    /**
     * @return mixed
     */
    public function getParameter(string $parameter)
    {
        return $this->getContainer()->getParameter($parameter);
    }

    /**
     * @return mixed
     */
    public function getService(string $service)
    {
        return $this->getServiceLocator()->get($service);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function getServiceLocator(): ServiceLocator
    {
        /** @var ServiceLocator $serviceLocator */
        $serviceLocator = $this->getContainer()
            ->get(CreateDbRegenerationServiceLocatorCompilerPass::TEST_DB_REGENERATION_SERVICE_LOCATOR);

        return $serviceLocator;
    }

    private function createContainer(): ContainerInterface
    {
        return $this->createTestContainer()->getContainer();
    }

    private function createTestContainer(): KernelInterface
    {
        $kernelTestCase = new class() extends KernelTestCase {
            public function getKernel(): KernelInterface
            {
                $kernel = $this->bootKernel();

                self::$kernel = null;

                return $kernel;
            }
        };

        return $kernelTestCase->getKernel();
    }
}
