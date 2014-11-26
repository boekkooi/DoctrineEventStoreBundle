<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\EventStreamNotFoundException;
use Rhumsaa\Uuid\Uuid;

/**
 * Stores events grouped together in streams identified by UUID.
 */
interface EventStore
{
    /**
     * Commit the event stream to persistence.
     *
     * @param EventStream $stream
     * @return Transaction
     */
    public function commit(EventStream $stream);
}
