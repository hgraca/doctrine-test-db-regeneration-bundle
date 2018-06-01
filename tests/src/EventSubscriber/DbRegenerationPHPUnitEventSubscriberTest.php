<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\EventSubscriber;

use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\SchemaManager;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\SchemaManagerInterface;
use Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber\DatabaseAwareTestInterface;
use Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber\DbRegenerationPHPUnitEventSubscriber;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Mockery;
use Mockery\MockInterface;

final class DbRegenerationPHPUnitEventSubscriberTest extends AbstractUnitTest
{
    /**
     * @var MockInterface|SchemaManager
     */
    private $schemaManager;

    protected function setUp(): void
    {
        $this->schemaManager = Mockery::mock(SchemaManagerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideFlagAndSchemaManagerExpectationsForStartTest
     */
    public function startTest_only_restores_db_if_test_implements_interface_and_should_regenerate_on_every_test(
        bool $isInstanceOfDatabaseAwareTestInterface,
        int $shouldRegenerateDbOnEveryTest,
        int $expectsToCreateTestDatabaseBackup,
        int $expectsToRestoreTestDatabase
    ): void {
        $eventSubscriber = new DbRegenerationPHPUnitEventSubscriber(1, $shouldRegenerateDbOnEveryTest, 0, [], $this->schemaManager);

        $this->schemaManager->shouldReceive('createTestDatabaseBackup')->times($expectsToCreateTestDatabaseBackup);
        $this->schemaManager->shouldReceive('restoreTestDatabase')->times($expectsToRestoreTestDatabase);
        $eventSubscriber->startTest($this->createTest($isInstanceOfDatabaseAwareTestInterface));
        $eventSubscriber->startTest($this->createTest($isInstanceOfDatabaseAwareTestInterface));
    }

    public function provideFlagAndSchemaManagerExpectationsForStartTest(): array
    {
        return [
            [false, 1, 0, 0],
            [false, 0, 0, 0],
            [true, 1, 1, 2],
            [true, 0, 1, 1],
        ];
    }

    /**
     * @test
     * @dataProvider provideFlagAndSchemaManagerExpectationsForEndTest
     */
    public function endTest_only_removes_db_if_test_implements_interface_and_should_remove_on_every_test(
        bool $isInstanceOfDatabaseAwareTestInterface,
        int $shouldRegenerateDbOnEveryTest,
        int $expectsToRemoveTestDatabase
    ): void {
        $eventSubscriber = new DbRegenerationPHPUnitEventSubscriber(1, $shouldRegenerateDbOnEveryTest, 0, [], $this->schemaManager);

        $this->schemaManager->shouldReceive('removeTestDatabase')->times($expectsToRemoveTestDatabase);
        $eventSubscriber->endTest($this->createTest($isInstanceOfDatabaseAwareTestInterface), 1);
    }

    public function provideFlagAndSchemaManagerExpectationsForEndTest(): array
    {
        return [
            [false, 1, 0],
            [false, 0, 0],
            [true, 1, 1],
            [true, 0, 0],
        ];
    }

    private function createTest(bool $isInstanceOfDatabaseAwareTestInterface)
    {
        if (!class_exists('\PHPUnit_Framework_BaseTestListener')) {
            if ($isInstanceOfDatabaseAwareTestInterface) {
                return Mockery::mock(\PHPUnit\Framework\Test::class, DatabaseAwareTestInterface::class);
            }

            return Mockery::mock(\PHPUnit\Framework\Test::class);
        }

        if ($isInstanceOfDatabaseAwareTestInterface) {
            return Mockery::mock(\PHPUnit_Framework_Test::class, DatabaseAwareTestInterface::class);
        }

        return Mockery::mock(\PHPUnit_Framework_Test::class);
    }
}
