<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\TestLib\Stub;

use Hgraca\DoctrineTestDbRegenerationBundle\DependencyInjection\CreateDbRegenerationServiceLocatorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class ContainerStub implements ContainerInterface
{
    /**
     * @var array
     */
    private $serviceList;

    /**
     * @var array
     */
    private $parameterList;

    public function __construct(array $serviceList, array $parameterList)
    {
        $this->serviceList = [
            CreateDbRegenerationServiceLocatorCompilerPass::TEST_DB_REGENERATION_SERVICE_LOCATOR => new ServiceLocatorStub($serviceList),
        ];
        $this->parameterList = $parameterList;
    }

    /**
     * Sets a service.
     *
     * @param string $id The service identifier
     * @param object $service The service instance
     */
    public function set($id, $service): void
    {
        $this->serviceList[$id] = $service;
    }

    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     * @param int $invalidBehavior The behavior when the service does not exist
     *
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     *
     * @return object The associated service
     *
     * @see Reference
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (!isset($this->serviceList[$id])) {
            throw new ServiceNotFoundException($id);
        }

        return $this->serviceList[$id];
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return bool true if the service is defined, false otherwise
     */
    public function has($id): bool
    {
        return isset($this->serviceList[$id]);
    }

    /**
     * Check for whether or not a service has been initialized.
     *
     * @param string $id
     *
     * @return bool true if the service has been initialized, false otherwise
     */
    public function initialized($id): bool
    {
        return true;
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @throws InvalidArgumentException if the parameter is not defined
     *
     * @return mixed The parameter value
     */
    public function getParameter($name)
    {
        if (!isset($this->parameterList[$name])) {
            throw new InvalidArgumentException("Can't find a parameter named '$name'");
        }

        return $this->parameterList[$name];
    }

    /**
     * Checks if a parameter exists.
     *
     * @param string $name The parameter name
     *
     * @return bool The presence of parameter in container
     */
    public function hasParameter($name): bool
    {
        return isset($this->parameterList[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name The parameter name
     * @param mixed $value The parameter value
     */
    public function setParameter($name, $value): void
    {
        $this->parameterList[$name] = $value;
    }
}
