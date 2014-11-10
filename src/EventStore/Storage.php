<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;
use Rhumsaa\Uuid\Uuid;

interface Storage
{
    /**
     * Store event stream data in persistence storage layer.
     *
     * @param Uuid $id
     * @param string $className
     * @param DomainEvent $event
     * @param int $nextVersion
     *
     * @return void
     */
    public function store(Uuid $id, $className, DomainEvent $event, $nextVersion);

    /**
     * Find the latest version for the given id.
     * If the id can't be found null will be returned.
     *
     * @param Uuid $id
     * @param string $className
     * @return integer|NULL
     */
    public function findCurrentVersion(Uuid $id, $className);
}
