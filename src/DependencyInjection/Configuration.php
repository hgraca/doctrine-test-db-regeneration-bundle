<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT = 'hgraca_doctrine_test_db_regeneration';
    public const FIXTURES_LOADER_SERVICE_ID = 'fixtures_loader_service_id';
    public const TEST_DB_BKP_PATH = 'test_db_bkp_path';
    public const DB_CONFIG_LIST = 'db_config_list';
    public const DOCTRINE_SERVICE_ID = 'doctrine_service_id';
    public const EXTRA_SERVICE_LIST = 'extra_service_list';

    public const DEFAULT_DOCTRINE_SERVICE_ID = 'doctrine';
    public const DEFAULT_FIXTURES_LOADER_SERVICE_ID = 'doctrine.fixtures.loader';
    public const DEFAULT_TEST_DB_BKP_PATH = '%kernel.cache_dir%/test.bkp.db';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root(self::ROOT);

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode(self::DOCTRINE_SERVICE_ID)->defaultValue(self::DEFAULT_DOCTRINE_SERVICE_ID)->end()
                ->scalarNode(self::FIXTURES_LOADER_SERVICE_ID)->defaultValue(self::DEFAULT_FIXTURES_LOADER_SERVICE_ID)->end()
                ->scalarNode(self::TEST_DB_BKP_PATH)->defaultValue(self::DEFAULT_TEST_DB_BKP_PATH)->end()
                ->arrayNode(self::EXTRA_SERVICE_LIST)
                    ->info('The list of extra services to add to the container, in case you want to reuse this already built container.')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
