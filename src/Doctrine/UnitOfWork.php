<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine;

use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSource;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventStore;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventStream;
use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\Transaction;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Helper\EventName;

use Doctrine\Common\Persistence\Proxy;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
final class UnitOfWork
{
    /**
     * @var EventStream[]
     */
    private $streams = array();

    /**
     * @var Transaction[]
     */
    private $transactions = array();

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventStore $eventStore, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventStore = $eventStore;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EventSource $object
     * @return void
     */
    public function save(EventSource $object)
    {
        $id = (string) $object->getId();

        if (!isset($this->streams[$id])) {
            $objectClass = ($object instanceof Proxy ? get_parent_class($object) : get_class($object));
            $this->streams[$id] = new EventStream(
                $objectClass,
                $object->getId(),
                array(),
                $object->getEventVersion()
            );
        }

        $eventStream = $this->streams[$id];
        $eventStream->addEvents($object->pullDomainEvents());

        $this->transactions[$id] = $this->eventStore->commit($eventStream);
    }

    public function dispatch()
    {
        $transactions = $this->transactions;
        $this->transactions = array();

        foreach ($transactions as $objectId => $transaction) {
            /** @var DomainEvent|\Symfony\Component\EventDispatcher\Event $event */
            foreach ($transaction->getCommittedEvents() as $event) {
                if ($event->getEventSourceId() === null) {
                    $event->setEventSourceId(Uuid::fromString($objectId));
                }

                $this->eventDispatcher->dispatch(
                    $this->getDispatcherEventName($event),
                    $event
                );
            }
        }
    }

    public static function getDispatcherEventName(DomainEvent $event)
    {
        $name = new EventName($event);

        // Converts 'EventName' to 'event_name'
        $name = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $name));

        // Prefix the event name
        return 'domain.' . $name;
    }
}
