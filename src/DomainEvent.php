<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle;

use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface DomainEvent
{
    /**
     * @throws \RuntimeException When setting an aggregate id where one already exists.
     * @param Uuid $aggregateId
     * @return void
     */
    public function setEventSourceId(Uuid $aggregateId);

    /**
     * @return Uuid
     */
    public function getEventSourceId();
}
