<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber;

use Exception;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\SchemaManager;
use Hgraca\DoctrineTestDbRegenerationBundle\Doctrine\SchemaManagerInterface;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

if (
    class_exists('PHPUnit_Runner_Version')
    && version_compare(PHPUnit_Runner_Version::id(), '5', '>=')
    && version_compare(PHPUnit_Runner_Version::id(), '6', '<')
) {
    // PHPUnit 5

    /**
     * @codeCoverageIgnore We can never cover both of these classes, although we do cover the trait
     */
    class DbRegenerationPHPUnitEventSubscriber extends \PHPUnit_Framework_BaseTestListener
    {
        use DbRegenerationPHPUnitEventSubscriberTrait;

        /**
         * @throws \Exception
         */
        public function startTest(\PHPUnit_Framework_Test $test): void
        {
            $this->onTestStart($test);
        }

        /**
         * @throws \Exception
         */
        public function endTest(\PHPUnit_Framework_Test $test, $time): void
        {
            $this->onEndTest($test);
        }
    }
} elseif (class_exists('\PHPUnit\Runner\Version') && version_compare(\PHPUnit\Runner\Version::id(), '7', '<')) {
    // PHPUnit 6+

    /**
     * @codeCoverageIgnore We can never cover both of these classes, although we do cover the trait
     */
    class DbRegenerationPHPUnitEventSubscriber implements TestListener
    {
        use TestListenerDefaultImplementation,
            DbRegenerationPHPUnitEventSubscriberTrait;

        /**
         * @throws \Exception
         */
        public function startTest(\PHPUnit\Framework\Test $test): void
        {
            $this->onTestStart($test);
        }

        /**
         * @throws \Exception
         */
        public function endTest(\PHPUnit\Framework\Test $test, $time): void
        {
            $this->onEndTest($test);
        }
    }
} elseif (class_exists('\PHPUnit\Runner\Version') && version_compare(\PHPUnit\Runner\Version::id(), '7', '>=')) {
    // PHPUnit 7+

    /**
     * @codeCoverageIgnore We can never cover both of these classes, although we do cover the trait
     */
    class DbRegenerationPHPUnitEventSubscriber implements TestListener
    {
        use TestListenerDefaultImplementation,
            DbRegenerationPHPUnitEventSubscriberTrait;

        /**
         * @throws \Exception
         */
        public function startTest(\PHPUnit\Framework\Test $test): void
        {
            $this->onTestStart($test);
        }

        /**
         * @throws \Exception
         */
        public function endTest(\PHPUnit\Framework\Test $test, float $time): void
        {
            $this->onEndTest($test);
        }
    }
} else {
    throw new Exception('DoctrineTestDbRegenerationBundle doesn\'t work with such an old version of PHPUnit.');
}

trait DbRegenerationPHPUnitEventSubscriberTrait
{
    /**
     * @var bool
     */
    private $hasCreatedTestDatabaseBackup = false;

    /**
     * @var bool
     */
    private $hasRestoredTestDatabase = false;

    /**
     * @var SchemaManagerInterface
     */
    private static $schemaManager;

    /**
     * @var bool
     */
    private $shouldRegenerateDbOnEveryTest;

    /**
     * @var bool
     */
    private $shouldRemoveDbAfterEveryTest;

    /**
     * @var int
     */
    private $shouldReuseExistingDbBkp;

    /**
     * @var array
     */
    private $executeMigrations;

    public function __construct(
        int $shouldRemoveDbAfterEveryTest = 1,
        int $shouldRegenerateDbOnEveryTest = 1,
        int $shouldReuseExistingDbBkp = 0,
        array $executeMigrations = [],
        SchemaManagerInterface $schemaManager = null
    ) {
        $this->shouldRemoveDbAfterEveryTest = (bool) $shouldRemoveDbAfterEveryTest;
        $this->shouldRegenerateDbOnEveryTest = (bool) $shouldRegenerateDbOnEveryTest;
        $this->shouldReuseExistingDbBkp = (bool) $shouldReuseExistingDbBkp;
        $this->executeMigrations = $executeMigrations;
        self::$schemaManager = $schemaManager;
    }

    /**
     * @throws \Exception
     */
    private function onTestStart($test): void
    {
        if (!$test instanceof DatabaseAwareTestInterface) {
            return;
        }

        if (!$this->hasCreatedTestDatabaseBackup()) {
            $this->createTestDatabaseBackup($this->shouldReuseExistingDbBkp);
        }

        if (!$this->hasRestoredTestDatabase() || $this->shouldRegenerateDbOnEveryTest) {
            $this->restoreTestDatabase();
        }
    }

    /**
     * At the end of each test that used the DB,
     * we remove the dirty DB just in case we forget
     * to add the interface to another test and it
     * ends up using a dirty DB, yielding unreliable results.
     * Its safer but slower though, so we can turn it off if we want to.
     *
     * @throws \ErrorException
     */
    public function onEndTest($test): void
    {
        if (!$this->shouldRemoveDbAfterEveryTest || !$test instanceof DatabaseAwareTestInterface) {
            return;
        }

        if ($this->shouldRegenerateDbOnEveryTest) {
            $this->getSchemaManager()->removeTestDatabase();
        }
    }

    /**
     * @throws \ErrorException
     */
    public static function getSchemaManager(): SchemaManagerInterface
    {
        return self::$schemaManager ?? self::$schemaManager = SchemaManager::constructUsingTestContainer();
    }

    private function hasCreatedTestDatabaseBackup(): bool
    {
        return $this->hasCreatedTestDatabaseBackup;
    }

    private function createdTestDatabaseBackup(): void
    {
        $this->hasCreatedTestDatabaseBackup = true;
    }

    private function hasRestoredTestDatabase(): bool
    {
        return $this->hasRestoredTestDatabase;
    }

    private function restoredTestDatabase(): void
    {
        $this->hasRestoredTestDatabase = true;
    }

    /**
     * @throws \ErrorException
     */
    private function createTestDatabaseBackup(bool $shouldReuseExistingDbBkp = false): void
    {
        $this->switchOffDoctrineTestBundleStaticDriver();
        $this->getSchemaManager()->createTestDatabaseBackup($shouldReuseExistingDbBkp, $this->executeMigrations);
        $this->createdTestDatabaseBackup();
        $this->switchOnDoctrineTestBundleStaticDriver();
    }

    /**
     * @throws \ErrorException
     */
    private function restoreTestDatabase(): void
    {
        $this->getSchemaManager()->restoreTestDatabase();
        $this->restoredTestDatabase();
    }

    private function switchOffDoctrineTestBundleStaticDriver(): void
    {
        if (\class_exists('DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver')) {
            \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::setKeepStaticConnections(false);
        }
    }

    private function switchOnDoctrineTestBundleStaticDriver(): void
    {
        if (\class_exists('DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver')) {
            \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::setKeepStaticConnections(true);
        }
    }
}
