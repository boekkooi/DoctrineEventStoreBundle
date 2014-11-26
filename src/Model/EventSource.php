<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Model;

use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\BadMethodCallException;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Helper\EventName;
use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
abstract class EventSource implements \Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSource
{
    /**
     * @var \Rhumsaa\Uuid\Uuid
     */
    private $id;

    /**
     * @var int
     */
    private $eventVersion = 0;

    /**
     * @var DomainEvent[]
     */
    private $events = array();

    protected function setId(Uuid $uuid)
    {
        $this->id = $uuid;
    }

    /**
     * @return \Rhumsaa\Uuid\Uuid
     */
    final public function getId()
    {
        if (is_string($this->id)) {
            $this->id = Uuid::fromString($this->id);
        }

        return $this->id;
    }

    /**
     * @return int
     */
    final public function getEventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @return DomainEvent[]
     */
    final public function pullDomainEvents()
    {
        $events = $this->events;
        $this->events = array();

        return $events;
    }

    protected function apply(DomainEvent $event)
    {
        $this->executeEvent($event);
        $this->events[] = $event;
    }

    private function executeEvent(DomainEvent $event)
    {
        $eventName = new EventName($event);
        $method = sprintf('apply%s', (string) $eventName);

        if (!method_exists($this, $method)) {
            throw new BadMethodCallException(sprintf(
                "There is no event named '%s' that can be applied to '%s'. " .
                "If you just want to emit an event without applying changes use the raise() method.",
                $method,
                get_class($this)
            ));
        }

        $this->$method($event);
    }
}
