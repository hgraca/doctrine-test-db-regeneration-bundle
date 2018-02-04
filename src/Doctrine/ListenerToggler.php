<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\Doctrine;

use Doctrine\Common\EventManager;
use Hgraca\DoctrineTestDbRegenerationBundle\StdLib\ReflectionHelper;
use Symfony\Bridge\Doctrine\ContainerAwareEventManager;

final class ListenerToggler
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var array
     */
    private $currentListeners;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->propertyName = $eventManager instanceof ContainerAwareEventManager ? 'listeners' : '_listeners';
    }

    /**
     * @throws \ReflectionException
     */
    public function disableListeners(): void
    {
        $this->currentListeners = $this->eventManager->getListeners();
        ReflectionHelper::setProtectedProperty($this->eventManager, $this->propertyName, []);
    }

    /**
     * @throws \ReflectionException
     */
    public function enableListeners(): void
    {
        ReflectionHelper::setProtectedProperty($this->eventManager, $this->propertyName, $this->currentListeners);
        $this->currentListeners = [];
    }
}
