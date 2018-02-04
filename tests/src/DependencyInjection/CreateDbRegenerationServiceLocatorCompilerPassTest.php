<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test\DependencyInjection;

use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Configuration;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\CreateDbRegenerationServiceLocatorCompilerPass;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\HgracaDoctrineTestDbRegenerationExtension;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class CreateDbRegenerationServiceLocatorCompilerPassTest extends AbstractUnitTest
{
    /**
     * @var MockInterface|ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var CreateDbRegenerationServiceLocatorCompilerPass
     */
    private $compilerPass;

    public function setUp(): void
    {
        $this->containerBuilder = Mockery::mock(ContainerBuilder::class);
        $this->containerBuilder->shouldReceive('setParameter');

        $this->compilerPass = new CreateDbRegenerationServiceLocatorCompilerPass();
    }

    /**
     * @test
     */
    public function process_sets_all_required_services_in_the_service_container(): void
    {
        $definitionMock = $this->mockDefinitionForHappyPath();
        $configExtension = $this->constructConfigExtension();
        $this->mockContainerBuilder($configExtension, $definitionMock);

        $this->containerBuilder->shouldReceive('has')->andReturn(true);
        $this->containerBuilder->shouldReceive('findDefinition')->andReturn(new Definition());

        $this->compilerPass->process($this->containerBuilder);
    }

    /**
     * @test
     * @expectedException \Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process_throws_exception_if_service_is_not_found(): void
    {
        $definitionMock = $this->mockDefinition();
        $configExtension = $this->constructConfigExtension();

        $this->mockContainerBuilder($configExtension, $definitionMock);

        $this->containerBuilder->shouldReceive('has')->andReturn(false);

        $this->compilerPass->process($this->containerBuilder);
    }

    /**
     * @test
     * @expectedException \Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Exception\AbstractServiceException
     */
    public function process_throws_exception_if_service_is_abstract(): void
    {
        $definitionMock = $this->mockDefinition();
        $configExtension = $this->constructConfigExtension();

        $this->mockContainerBuilder($configExtension, $definitionMock);

        $this->containerBuilder->shouldReceive('has')->andReturn(true);
        $definition = new Definition();
        $definition->setAbstract(true);
        $this->containerBuilder->shouldReceive('findDefinition')->andReturn($definition);

        $this->compilerPass->process($this->containerBuilder);
    }

    private function mockDefinitionForHappyPath(): Definition
    {
        $definitionMock = $this->mockDefinition();

        $definitionMock->shouldReceive('setArguments')
            ->once()
            ->with(
                [
                    [
                        'aaa' => new Reference('aaa'),
                        'bbb' => new Reference('bbb'),
                        'x' => new Reference('x'),
                        'y' => new Reference('y'),
                        'z' => new Reference('z'),
                    ],
                ]
            )
            ->andReturn($definitionMock);

        return $definitionMock;
    }

    private function constructConfigExtension(): HgracaDoctrineTestDbRegenerationExtension
    {
        $configExtension = new HgracaDoctrineTestDbRegenerationExtension();
        $configExtension->load(
            [
                [
                    Configuration::DOCTRINE_SERVICE_ID => 'aaa',
                    Configuration::FIXTURES_LOADER_SERVICE_ID => 'bbb',
                    Configuration::TEST_DB_BKP_PATH => 'ccc',
                    Configuration::EXTRA_SERVICE_LIST => ['x', 'y', 'z'],
                ],
            ],
            $this->containerBuilder
        );

        return $configExtension;
    }

    private function mockContainerBuilder(
        HgracaDoctrineTestDbRegenerationExtension $configExtension,
        Definition $definitionMock
    ): void {
        $this->containerBuilder->shouldReceive('getExtension')
            ->once()
            ->with('hgraca_doctrine_test_db_regeneration')
            ->andReturn($configExtension);

        $this->containerBuilder->shouldReceive('register')
            ->once()
            ->with(
                CreateDbRegenerationServiceLocatorCompilerPass::TEST_DB_REGENERATION_SERVICE_LOCATOR,
                ServiceLocator::class
            )
            ->andReturn($definitionMock);
    }

    /**
     * @return MockInterface|Definition
     */
    private function mockDefinition(): Definition
    {
        $definitionMock = Mockery::mock(Definition::class);

        $definitionMock->shouldReceive('setPublic')
            ->once()
            ->with(true)
            ->andReturn($definitionMock);

        $definitionMock->shouldReceive('addTag')
            ->once()
            ->with(CreateDbRegenerationServiceLocatorCompilerPass::TAG_SERVICE_LOCATOR)
            ->andReturn($definitionMock);

        return $definitionMock;
    }
}
