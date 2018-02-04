<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\TestLib;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class AbstractUnitTest extends TestCase
{
    use MockeryPHPUnitIntegration;
}
