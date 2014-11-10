<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

/**
 * Code originated at https://github.com/beberlei/litecqrs-php/
 */
class Transaction
{
    private $eventStream;
    private $committedEvents = array();

    public function __construct(EventStream $eventStream, array $committedEvents)
    {
        $this->eventStream = $eventStream;
        $this->committedEvents = $committedEvents;
    }

    /**
     * @return EventStream
     */
    public function getEventStream()
    {
        return $this->eventStream;
    }

    /**
     * @return array<\Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent>
     */
    public function getCommittedEvents()
    {
        return $this->committedEvents;
    }
}
