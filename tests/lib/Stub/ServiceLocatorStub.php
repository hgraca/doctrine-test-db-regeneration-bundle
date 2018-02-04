<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\TestLib\Stub;

use Symfony\Component\DependencyInjection\ServiceLocator;

final class ServiceLocatorStub extends ServiceLocator
{
    /**
     * @var array
     */
    private $serviceList;

    public function __construct(array $serviceList)
    {
        $this->serviceList = $serviceList;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id): bool
    {
        return isset($this->serviceList[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->serviceList[$id];
    }
}
