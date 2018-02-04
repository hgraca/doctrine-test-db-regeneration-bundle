<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\DependencyInjection;

use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Configuration;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\HgracaDoctrineTestDbRegenerationExtension;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class HgracaDoctrineTestDbRegenerationExtensionTest extends AbstractUnitTest
{
    /**
     * @test
     * @dataProvider provideConfig
     *
     * @throws \ReflectionException
     */
    public function load(array $configs, array $expectedProcessedConfig): void
    {
        $extension = new HgracaDoctrineTestDbRegenerationExtension();
        $containerBuilder = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extension->load($configs, $containerBuilder);

        $this->assertEquals(
            ReflectionHelper::getProtectedProperty($extension, 'processedConfig'),
            $expectedProcessedConfig
        );

        if (!empty($configs)) {
            $this->assertEquals($expectedProcessedConfig[Configuration::DOCTRINE_SERVICE_ID], $extension->getDoctrineServiceId());
            $this->assertEquals($expectedProcessedConfig[Configuration::FIXTURES_LOADER_SERVICE_ID], $extension->getFixturesLoaderServiceId());
            $this->assertEquals($expectedProcessedConfig[Configuration::TEST_DB_BKP_PATH], $extension->getTestDbBkpPath());
            $this->assertEquals(
                array_merge(
                    [
                        $expectedProcessedConfig[Configuration::FIXTURES_LOADER_SERVICE_ID],
                        $expectedProcessedConfig[Configuration::DOCTRINE_SERVICE_ID],
                    ],
                    $expectedProcessedConfig[Configuration::EXTRA_SERVICE_LIST]
                ),
                $extension->getServicesIdList()
            );
        }
    }

    public function provideConfig(): array
    {
        return [
            [
                [],
                [
                    Configuration::DOCTRINE_SERVICE_ID => 'doctrine',
                    Configuration::FIXTURES_LOADER_SERVICE_ID => 'doctrine.fixtures.loader',
                    Configuration::TEST_DB_BKP_PATH => '%kernel.cache_dir%/test.bkp.db',
                    Configuration::EXTRA_SERVICE_LIST => [],
                ],
            ],
            [
                [
                    [
                        Configuration::DOCTRINE_SERVICE_ID => 'aaa',
                        Configuration::FIXTURES_LOADER_SERVICE_ID => 'bbb',
                        Configuration::TEST_DB_BKP_PATH => 'ccc',
                        Configuration::EXTRA_SERVICE_LIST => ['x', 'y', 'z'],
                    ],
                ],
                [
                    Configuration::DOCTRINE_SERVICE_ID => 'aaa',
                    Configuration::FIXTURES_LOADER_SERVICE_ID => 'bbb',
                    Configuration::TEST_DB_BKP_PATH => 'ccc',
                    Configuration::EXTRA_SERVICE_LIST => ['x', 'y', 'z'],
                ],
            ],
        ];
    }
}
