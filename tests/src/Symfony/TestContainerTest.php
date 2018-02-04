<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\Symfony;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\EntityManagerInterface;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Configuration;
use Hgraca\DoctrineTestDbRegenerationBundle\Symfony\TestContainer;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\Stub\ContainerStub;
use Mockery;
use stdClass;

final class TestContainerTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function getParameter(): void
    {
        $parameterName = 'a_parameter_name';
        $parameterValue = 'a_parameter_value';

        $containerStub = new ContainerStub(
            [],
            [
                $parameterName => $parameterValue,
            ]
        );
        $testContainer = new TestContainer($containerStub);

        self::assertSame($parameterValue, $testContainer->getParameter($parameterName));
    }

    /**
     * @test
     */
    public function getService(): void
    {
        $serviceName = 'a_service_name';
        $serviceValue = new stdClass();

        $containerStub = new ContainerStub(
            [
                $serviceName => $serviceValue,
            ],
            []
        );
        $testContainer = new TestContainer($containerStub);

        self::assertSame($serviceValue, $testContainer->getService($serviceName));
    }

    /**
     * @test
     */
    public function getFixturesLoader(): void
    {
        $containerStub = new ContainerStub(
            [
                Configuration::DEFAULT_FIXTURES_LOADER_SERVICE_ID => $stub = Mockery::mock(Loader::class),
            ],
            [
                TestContainer::KEY_FIXTURES_LOADER => Configuration::DEFAULT_FIXTURES_LOADER_SERVICE_ID,
            ]
        );
        $testContainer = new TestContainer($containerStub);

        self::assertSame($stub, $testContainer->getFixturesLoader());
    }

    /**
     * @test
     */
    public function getEntityManager(): void
    {
        $entityManagerMock = Mockery::mock(EntityManagerInterface::class);
        $doctrineStub = new class($entityManagerMock) {
            /**
             * @var EntityManagerInterface
             */
            private $entityManager;

            public function __construct($entityManagerStub)
            {
                $this->entityManager = $entityManagerStub;
            }

            public function getManager(): EntityManagerInterface
            {
                return $this->entityManager;
            }
        };

        $containerStub = new ContainerStub(
            [
                Configuration::DEFAULT_DOCTRINE_SERVICE_ID => $doctrineStub,
            ],
            [
                TestContainer::KEY_DOCTRINE_SERVICE_ID => Configuration::DEFAULT_DOCTRINE_SERVICE_ID,
            ]
        );
        $testContainer = new TestContainer($containerStub);

        self::assertSame($entityManagerMock, $testContainer->getEntityManager());
    }

    /**
     * @test
     */
    public function getDbBkpDir(): void
    {
        $containerStub = new ContainerStub(
            [],
            [
                TestContainer::KEY_TEST_DB_BKP_PATH => Configuration::DEFAULT_TEST_DB_BKP_PATH,
            ]
        );
        $testContainer = new TestContainer($containerStub);

        self::assertSame(Configuration::DEFAULT_TEST_DB_BKP_PATH, $testContainer->getDbBkpDir());
    }
}
