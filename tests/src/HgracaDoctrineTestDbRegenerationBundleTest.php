<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Test;

use Hamcrest\Core\IsInstanceOf;
use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\CreateDbRegenerationServiceLocatorCompilerPass;
use Hgraca\DoctrineTestDbRegenerationBundle\HgracaDoctrineTestDbRegenerationBundle;
use Hgraca\DoctrineTestDbRegenerationBundle\TestLib\AbstractUnitTest;
use Mockery;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class HgracaDoctrineTestDbRegenerationBundleTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function build(): void
    {
        $containerBuilderMock = Mockery::mock(ContainerBuilder::class);
        $containerBuilderMock->shouldReceive('addCompilerPass')
            ->once()
            ->with(IsInstanceOf::anInstanceOf(CreateDbRegenerationServiceLocatorCompilerPass::class));

        $bundle = new HgracaDoctrineTestDbRegenerationBundle();
        $bundle->build($containerBuilderMock);
    }
}
