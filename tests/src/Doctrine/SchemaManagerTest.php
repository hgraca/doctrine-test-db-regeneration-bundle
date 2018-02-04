<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\Doctrine;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Configuration;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\FixtureList;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\SchemaManager;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Filesystem;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Md5;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\Symfony\TestContainer;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\Stub\ContainerStub;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Symfony\Bridge\Doctrine\ContainerAwareEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SchemaManagerTest extends AbstractUnitTest
{
    private const TEST_DB_BKP_FILE = '%kernel.cache_dir%/test.bkp.db';
    private const TEST_DB_SERIALIZED_FIXTURES_FILE = self::TEST_DB_BKP_FILE . '.ser';
    private const TEST_DB_FILE = 'some/path/to/db.sqlite';
    private const EVENT_MANAGER_LISTENERS = ['a', 'b'];

    /**
     * @var MockInterface|ContainerInterface
     */
    private $containerStub;

    /**
     * @var FixtureInterface[]
     */
    private $fixturesList;

    /**
     * @var MockInterface|Loader
     */
    private $fixturesLoader;

    /**
     * @var MockInterface|EntityManagerInterface
     */
    private $entityManagerMock;

    /**
     * @var TestContainer
     */
    private $testContainer;

    /**
     * @var MockInterface|Connection
     */
    private $connectionMock;

    /**
     * @var MockInterface|EventManager
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->fixturesList = [new DummyFixture1(), new DummyFixture2()];
        $this->fixturesLoader = Mockery::mock(Loader::class);
        $this->fixturesLoader->shouldReceive('getFixtures')->andReturn($this->fixturesList);
        $this->connectionMock = Mockery::mock(Connection::class);
        $this->eventManagerMock = Mockery::mock(EventManager::class);
        $this->entityManagerMock = Mockery::mock(EntityManagerInterface::class);
        $this->entityManagerMock->shouldReceive('getConnection')->andReturn($this->connectionMock);
        $this->entityManagerMock->shouldReceive('getEventManager')->andReturn($this->eventManagerMock);
        $entityManagerMock = $this->entityManagerMock;
        $doctrineStub = new class($entityManagerMock) {
            /**
             * @var EntityManagerInterface
             */
            private $entityManager;

            public function __construct($entityManager)
            {
                $this->entityManager = $entityManager;
            }

            public function getManager(): EntityManagerInterface
            {
                return $this->entityManager;
            }
        };
        $this->containerStub = new ContainerStub(
            [
                Configuration::DEFAULT_FIXTURES_LOADER_SERVICE_ID => $this->fixturesLoader,
                Configuration::DEFAULT_DOCTRINE_SERVICE_ID => $doctrineStub,
            ],
            [
                TestContainer::KEY_FIXTURES_LOADER => Configuration::DEFAULT_FIXTURES_LOADER_SERVICE_ID,
                TestContainer::KEY_DOCTRINE_SERVICE_ID => Configuration::DEFAULT_DOCTRINE_SERVICE_ID,
                TestContainer::KEY_TEST_DB_BKP_PATH => Configuration::DEFAULT_TEST_DB_BKP_PATH,
            ]
        );

        $this->testContainer = new TestContainer($this->containerStub);
    }

    protected function tearDown(): void
    {
        Filesystem::resetOverrides();
        Md5::resetOverrides();
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function constructUsingTestContainer(): void
    {
        $schemaManager = SchemaManager::constructUsingTestContainer($this->testContainer);

        /** @var FixtureList $fixtureList */
        $fixtureList = ReflectionHelper::getProtectedProperty($schemaManager, 'fixtureList');

        self::assertEquals($this->fixturesList, $fixtureList->getFixtures());
        self::assertEquals(
            $this->entityManagerMock,
            ReflectionHelper::getProtectedProperty($schemaManager, 'entityManager')
        );
        self::assertEquals(
            Configuration::DEFAULT_TEST_DB_BKP_PATH,
            ReflectionHelper::getProtectedProperty($schemaManager, 'testDbBackupPath')
        );
    }

    /**
     * @test
     */
    public function createTestDatabaseBackup_does_not_create_if_it_already_exists(): void
    {
        $shouldReuseExistingDbBkp = true;
        $this->mockConnectionIsSetupWithFilePath();
        $this->mockDbBackupExists();
        $this->mockEntitiesMetadata();

        $schemaManager = SchemaManager::constructUsingTestContainer($this->testContainer);
        $schemaManager->createTestDatabaseBackup($shouldReuseExistingDbBkp);
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function createTestDatabaseBackup_creates_if_it_does_not_exist(): void
    {
        $shouldReuseExistingDbBkp = true;
        $this->mockConnectionIsSetupWithFilePath();
        $this->mockDbBackupDoesNotExist();
        $this->expectEventManagerToDeliverListeners();

        $this->mockMovingDbBackupFileSucceeds();

        $schemaManager = SchemaManager::constructUsingTestContainer(
            $this->testContainer,
            $this->expectSchemaToolToCreateCleanDb($this->mockEntitiesMetadata()),
            $this->expectOrmExecutorToCreateFixturesInDb(),
            $this->expectReferenceRepositoryToCreateFixturesBkpFile()
        );
        $schemaManager->createTestDatabaseBackup($shouldReuseExistingDbBkp);
        $this->assertEventManagerContainsOriginalListeners();
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function createTestDatabaseBackup_creates_if_it_exists_but_should_not_reuse(): void
    {
        $shouldReuseExistingDbBkp = false;
        $this->mockConnectionIsSetupWithFilePath();
        $this->mockDbBackupExists();
        $this->expectEventManagerToDeliverListeners();

        $this->mockMovingDbBackupFileSucceeds();

        $schemaManager = SchemaManager::constructUsingTestContainer(
            $this->testContainer,
            $this->expectSchemaToolToCreateCleanDb($this->mockEntitiesMetadata()),
            $this->expectOrmExecutorToCreateFixturesInDb(),
            $this->expectReferenceRepositoryToCreateFixturesBkpFile()
        );
        $schemaManager->createTestDatabaseBackup($shouldReuseExistingDbBkp);
        $this->assertEventManagerContainsOriginalListeners();
    }

    /**
     * @test
     */
    public function restoreTestDatabase(): void
    {
        $this->mockConnectionIsSetupWithFilePath();
        $this->mockDbBackupExists();
        $this->mockEntitiesMetadata();

        $this->entityManagerMock->shouldReceive('flush')->once();
        $this->entityManagerMock->shouldReceive('clear')->once();

        Filesystem::override_copy(
            function () {
                return true;
            }
        );

        $schemaManager = SchemaManager::constructUsingTestContainer($this->testContainer);
        $schemaManager->restoreTestDatabase();
    }

    /**
     * @test
     * @group failing
     */
    public function removeTestDatabase(): void
    {
        $this->mockConnectionIsSetupWithFilePath();

        Filesystem::override_file_exists(
            function (string $filePath) {
                if ($filePath === self::TEST_DB_BKP_FILE) {
                    return true;
                }

                return false;
            }
        );

        Filesystem::override_unlink(
            function (string $filePath) {
                if ($filePath === self::TEST_DB_BKP_FILE) {
                    return true;
                }

                return false;
            }
        );

        $schemaManager = SchemaManager::constructUsingTestContainer($this->testContainer);
        $schemaManager->removeTestDatabase();
    }

    private function mockDbBackupExists(): void
    {
        Filesystem::override_file_exists(
            function (string $filePath) {
                if (
                    $filePath === self::TEST_DB_BKP_FILE
                    || $filePath === self::TEST_DB_SERIALIZED_FIXTURES_FILE
                ) {
                    return true;
                }

                return false;
            }
        );
    }

    private function mockDbBackupDoesNotExist(): void
    {
        Filesystem::override_file_exists(
            function () {
                return false;
            }
        );
    }

    private function mockEntitiesMetadata(): array
    {
        $entity1FilePath = 'file/path/to/entity1.php';
        $classMetadataMock1ReflectionClass = Mockery::mock(ReflectionClass::class);
        $classMetadataMock1ReflectionClass->shouldReceive('getFileName')->andReturn($entity1FilePath);
        $classMetadataMock1 = Mockery::mock(ClassMetadata::class);
        $classMetadataMock1->shouldReceive('getReflectionClass')->andReturn($classMetadataMock1ReflectionClass);

        $entity2FilePath = 'file/path/to/entity2.php';
        $classMetadataMock2ReflectionClass = Mockery::mock(ReflectionClass::class);
        $classMetadataMock2ReflectionClass->shouldReceive('getFileName')->andReturn($entity2FilePath);
        $classMetadataMock2 = Mockery::mock(ClassMetadata::class);
        $classMetadataMock2->shouldReceive('getReflectionClass')->andReturn($classMetadataMock2ReflectionClass);

        $entity1Filemtime = 123;
        $entity2Filemtime = 456;
        Filesystem::override_filemtime(
            function (string $filePath) use ($entity1FilePath, $entity1Filemtime, $entity2FilePath, $entity2Filemtime) {
                switch ($filePath) {
                    case $entity1FilePath:
                        return $entity1Filemtime;
                        break;
                    case $entity2FilePath:
                        return $entity2Filemtime;
                        break;
                    default:
                        return filemtime($filePath);
                }
            }
        );

        $classMetadataFactoryMock = Mockery::mock(ClassMetadataFactory::class);
        $classMetadataFactoryMock->shouldReceive('getAllMetadata')->andReturn([$classMetadataMock1, $classMetadataMock2]);
        $this->entityManagerMock->shouldReceive('getMetadataFactory')->andReturn($classMetadataFactoryMock);

        return [$classMetadataMock1, $classMetadataMock2];
    }

    private function mockConnectionIsSetupWithFilePath(): void
    {
        $this->connectionMock->shouldReceive('getParams')->andReturn(['path' => self::TEST_DB_FILE]);
    }

    private function mockMovingDbBackupFileSucceeds(): void
    {
        Filesystem::override_rename(
            function () {
                return true;
            }
        );
    }

    private function expectOrmExecutorToCreateFixturesInDb()
    {
        $ORMExecutor = Mockery::mock(ORMExecutor::class);
        $ORMExecutor->shouldReceive('setReferenceRepository')->once();
        $ORMExecutor->shouldReceive('execute')->once()->with($this->fixturesList, true);

        return $ORMExecutor;
    }

    /**
     * @return SchemaTool|MockInterface
     */
    private function expectSchemaToolToCreateCleanDb($entitiesMetadataMock): SchemaTool
    {
        $schemaTool = Mockery::mock(SchemaTool::class);
        $schemaTool->shouldReceive('dropDatabase')->once();
        $schemaTool->shouldReceive('createSchema')->once()->with($entitiesMetadataMock);

        return $schemaTool;
    }

    /**
     * @return ProxyReferenceRepository|MockInterface
     */
    private function expectReferenceRepositoryToCreateFixturesBkpFile(): ProxyReferenceRepository
    {
        $referenceRepository = Mockery::mock(ProxyReferenceRepository::class);
        $referenceRepository->shouldReceive('save')->once()->with(self::TEST_DB_BKP_FILE);

        return $referenceRepository;
    }

    private function expectEventManagerToDeliverListeners(): void
    {
        $this->eventManagerMock->shouldReceive('getListeners')->once()->andReturn(self::EVENT_MANAGER_LISTENERS);
    }

    /**
     * @throws \ReflectionException
     */
    private function assertEventManagerContainsOriginalListeners(): void
    {
        $propertyName = $this->eventManagerMock instanceof ContainerAwareEventManager ? 'listeners' : '_listeners';

        self::assertEquals(
            self::EVENT_MANAGER_LISTENERS,
            ReflectionHelper::getProtectedProperty($this->eventManagerMock, $propertyName)
        );
    }
}
