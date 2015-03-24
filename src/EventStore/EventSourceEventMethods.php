<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;
use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Helper\EventName;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\BadMethodCallException;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
trait EventSourceEventMethods
{
    /**
     * @var int
     */
    private $eventVersion = 0;

    /**
     * @var DomainEvent[]
     */
    private $events = array();

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
