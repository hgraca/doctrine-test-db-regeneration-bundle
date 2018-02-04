<?php

declare(strict_types=1);

namespace Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber;

/**
 * This is a tag interface.
 * Its only used so that the listener only triggers the DB regeneration for the tests that actually need it.
 */
interface DatabaseAwareTestInterface
{
}
