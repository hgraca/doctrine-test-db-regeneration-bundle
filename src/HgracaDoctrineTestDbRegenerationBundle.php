<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle;

use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\CreateDbRegenerationServiceLocatorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HgracaDoctrineTestDbRegenerationBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder): void
    {
        parent::build($containerBuilder);
        $containerBuilder->addCompilerPass(new CreateDbRegenerationServiceLocatorCompilerPass());
    }
}
