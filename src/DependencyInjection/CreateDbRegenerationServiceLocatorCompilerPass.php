<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection;

use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Exception\AbstractServiceException;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class CreateDbRegenerationServiceLocatorCompilerPass implements CompilerPassInterface
{
    public const TAG_SERVICE_LOCATOR = 'container.service_locator';
    public const TEST_DB_REGENERATION_SERVICE_LOCATOR = 'test_db_regeneration.service_locator';

    public function process(ContainerBuilder $containerBuilder): void
    {
        /** @var HgracaDoctrineTestDbRegenerationExtension $config */
        $config = $containerBuilder->getExtension('hgraca_doctrine_test_db_regeneration');

        $serviceIdList = $config->getServicesIdList();

        $containerBuilder->register(self::TEST_DB_REGENERATION_SERVICE_LOCATOR, ServiceLocator::class)
            ->setPublic(true)
            ->addTag(self::TAG_SERVICE_LOCATOR)
            ->setArguments(
                [
                    array_combine(
                        $serviceIdList,
                        array_map(
                            function (string $id) use ($containerBuilder): Reference {
                                if (!$containerBuilder->has($id)) {
                                    throw new ServiceNotFoundException("Service with ID '$id' was not found.");
                                }

                                if ($containerBuilder->findDefinition($id)->isAbstract()) {
                                    throw new AbstractServiceException();
                                }

                                return new Reference($id);
                            },
                            $serviceIdList
                        )
                    ),
                ]
            );
    }
}
