<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\Filesystem;
use Hgraca\DoctrineTestDbRegenerationBundle\Symfony\TestContainer;

final class SchemaManager implements SchemaManagerInterface
{
    /**
     * @var string
     */
    private $testDbBackupPath;

    /**
     * @var MigrationsExecutorInterface
     */
    private $migrationsExecutor;

    /**
     * @var string
     */
    private $databaseFilePath;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ClassMetadata[]
     */
    private $entityMetadata;

    /**
     * @var FixtureList
     */
    private $fixtureList;

    /**
     * @var ProxyReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var SchemaTool|null
     */
    private $schemaTool;

    /**
     * @var ORMExecutor|null
     */
    private $ORMExecutor;

    /**
     * @var ListenerToggler
     */
    private $doctrineListenersToggler;

    private function __construct(
        FixtureList $fixtureList,
        EntityManagerInterface $entityManager,
        string $testDbBackupPath,
        MigrationsExecutorInterface $migrationsExecutor,
        ?SchemaTool $schemaTool = null,
        ?ORMExecutor $ORMExecutor = null,
        ?ProxyReferenceRepository $referenceRepository = null
    ) {
        $this->fixtureList = $fixtureList;
        $this->entityManager = $entityManager;
        $this->testDbBackupPath = $testDbBackupPath;
        $this->migrationsExecutor = $migrationsExecutor;
        $this->schemaTool = $schemaTool;
        $this->ORMExecutor = $ORMExecutor;
        $this->referenceRepository = $referenceRepository;
        $this->doctrineListenersToggler = new ListenerToggler($this->entityManager->getEventManager());
    }

    /**
     * @throws \ErrorException
     */
    public static function constructUsingTestContainer(
        TestContainer $testContainer = null,
        SchemaTool $schemaTool = null,
        ORMExecutor $ORMExecutor = null,
        ProxyReferenceRepository $referenceRepository = null,
        MigrationsExecutorInterface $migrationsExecutor = null
    ): SchemaManagerInterface {
        $testContainer = $testContainer ?? new TestContainer();

        $testDbBackupPath = $testContainer->getDbBkpDir();

        return new self(
            FixtureList::constructFromFixturesLoader($testContainer->getFixturesLoader()),
            $testContainer->getEntityManager(),
            $testDbBackupPath,
            $migrationsExecutor ?? new MigrationsExecutor(
                $testContainer->getContainer(),
                $testContainer->getEntityManager()->getConnection()
            ),
            $schemaTool,
            $ORMExecutor,
            $referenceRepository
        );
    }

    /**
     * @throws ToolsException
     * @throws \Exception
     */
    public function createTestDatabaseBackup(
        bool $shouldReuseExistingDbBkp = false,
        array $migrationsToExecute = []
    ): void {
        $testDbPath = $this->getDatabaseFilePath();

        if ($shouldReuseExistingDbBkp && $this->testDatabaseBackupExists($this->testDbBackupPath)) {
            return;
        }

        $this->createBackup($this->testDbBackupPath, $testDbPath, ...$migrationsToExecute);
    }

    /**
     * @throws \Exception
     */
    public function restoreTestDatabase(): void
    {
        $testDbPath = $this->getDatabaseFilePath();

        $this->restoreBackup($this->testDbBackupPath, $testDbPath);
    }

    public function removeTestDatabase(): void
    {
        $usedSqliteDatabaseFile = $this->getDatabaseFilePath();

        if (Filesystem::file_exists($usedSqliteDatabaseFile)) {
            Filesystem::unlink($usedSqliteDatabaseFile);
        }
    }

    /**
     * @throws \Doctrine\Common\DataFixtures\OutOfBoundsException
     * @throws \Exception
     */
    public function getLoadedReferenceRepository(): ProxyReferenceRepository
    {
        $this->getReferenceRepository()->load($this->testDbBackupPath);

        return $this->getReferenceRepository();
    }

    private function getDatabaseFilePath(): string
    {
        return $this->databaseFilePath ?? $this->databaseFilePath = $this->getConnection()->getParams()['path'];
    }

    /**
     * @return ClassMetadata[]
     */
    private function getEntityMetadata(): array
    {
        return $this->entityMetadata
            ?? $this->entityMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @throws \Exception
     */
    private function testDatabaseBackupExists(string $testDbBackupPath): bool
    {
        if (Filesystem::file_exists($testDbBackupPath) && Filesystem::file_exists($testDbBackupPath . '.ser')) {
            return true;
        }

        return false;
    }

    /**
     * @throws ToolsException
     * @throws ToolsException
     * @throws \ReflectionException
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    private function createBackup(string $testDbBackupPath, string $testDbPath, string ...$migrationList): void
    {
        $this->removeTestDatabase();
        $this->createCleanSchema();
        $this->migrationsExecutor->execute(...$migrationList);
        $this->doctrineListenersToggler->disableListeners();
        $this->loadFixtures();
        $this->doctrineListenersToggler->enableListeners();
        $this->createDbBackupFile($testDbPath, $testDbBackupPath);
        $this->createFixturesBackupFile($testDbBackupPath);
    }

    /**
     * @throws ToolsException
     */
    private function createCleanSchema(): void
    {
        $this->getSchemaTool()->dropDatabase();
        $this->getSchemaTool()->createSchema($this->getEntityMetadata());
    }

    private function loadFixtures(): void
    {
        $this->getORMExecutor()->setReferenceRepository($this->getReferenceRepository());
        $this->getORMExecutor()->execute($this->fixtureList->getFixtures(), true);
    }

    private function createDbBackupFile(string $sourcePath, string $backupPath): void
    {
        Filesystem::rename($sourcePath, $backupPath);
    }

    private function createFixturesBackupFile(string $testDbBackupPath): void
    {
        $this->getReferenceRepository()->save($testDbBackupPath);
    }

    private function restoreBackup(string $testDbBackupPath, string $testDbPath): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->recoverDbBackupFile($testDbBackupPath, $testDbPath);
    }

    private function recoverDbBackupFile(string $backupPath, string $sourcePath): void
    {
        Filesystem::copy($backupPath, $sourcePath);
    }

    private function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    private function getSchemaTool(): SchemaTool
    {
        return $this->schemaTool ?? $this->schemaTool = new SchemaTool($this->entityManager);
    }

    private function getORMExecutor(): ORMExecutor
    {
        return $this->ORMExecutor ?? $this->ORMExecutor = new ORMExecutor($this->entityManager);
    }

    private function getReferenceRepository(): ProxyReferenceRepository
    {
        return $this->referenceRepository
            ?? $this->referenceRepository = new ProxyReferenceRepository($this->entityManager);
    }
}
