<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine;

use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventStream;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\Storage;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\Transaction;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\ConcurrencyException;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\EventStreamNotFoundException;

use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class EventStore implements \Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventStore
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Commit the event stream to persistence.
     *
     * @param EventStream $stream
     * @return Transaction
     */
    public function commit(EventStream $stream)
    {
        $id = $stream->getUuid();

        $currentVersion = intval($stream->getVersion());
        $actualVersion = $this->storage->findCurrentVersion($id, $stream->getClassName());
        if ($actualVersion !== $currentVersion) {
            throw new ConcurrencyException(sprintf(
                'Invalid version for %s::%s, expected %s got %s',
                $stream->getClassName(),
                $id,
                $currentVersion,
                $actualVersion
            ));
        }

        $newEvents = $stream->newEvents();
        $nextVersion = $currentVersion + count($newEvents);

        if (count($newEvents) === 0) {
            return new Transaction($stream, $newEvents);
        }

        foreach ($newEvents as $newEvent) {
            $currentVersion++;
            $this->storage->store($id, $stream->getClassName(), $newEvent, $currentVersion);
        }
        $stream->markNewEventsProcessed($nextVersion);

        return new Transaction($stream, $newEvents);
    }
}
