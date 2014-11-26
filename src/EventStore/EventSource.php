<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface EventSource
{
    public function getId();

    /**
     * @return int
     */
    public function getEventVersion();

    /**
     * @return DomainEvent[]
     */
    public function pullDomainEvents();
}
