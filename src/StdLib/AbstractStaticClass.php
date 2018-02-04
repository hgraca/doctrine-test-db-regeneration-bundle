<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\StdLib;

/**
 * This class is just an utility class that helps us to remove duplication from the tests
 * and that's why it can't be instantiated
 *
 * @codeCoverageIgnore
 */
abstract class AbstractStaticClass
{
    protected function __construct()
    {
        // disallow instantiation to all subclasses
    }
}
