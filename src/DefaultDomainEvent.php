<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\LogicException;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Helper\EventName;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
abstract class DefaultDomainEvent extends Event implements DomainEvent
{
    /**
     * @var mixed
     */
    private $eventSourceId;

    public function __construct(Uuid $eventSourceId, array $data = array())
    {
        $this->setEventSourceId($eventSourceId);

        foreach ($data as $key => $value) {
            $this->assertPropertyExists($key);

            $this->$key = $value;
        }
    }

    public function setEventSourceId(Uuid $eventSourceId)
    {
        if ($this->eventSourceId !== null) {
            throw new \RuntimeException(sprintf(
                'A aggregate id can only be set once for %s.',
                new EventName($this)
            ));
        }
        $this->eventSourceId = $eventSourceId;
    }

    public function getEventSourceId()
    {
        return $this->eventSourceId;
    }

    public function __get($name)
    {
        $this->assertPropertyExists($name);

        return $this->$name;
    }

    private function assertPropertyExists($name)
    {
        if (!property_exists($this, $name)) {
            throw new \RuntimeException(sprintf(
                'Property %s is not a valid property on event %s',
                $name,
                new EventName($this)
            ));
        }
    }

    public function isPropagationStopped()
    {
        return false;
    }

    public function stopPropagation()
    {
        throw new LogicException(sprintf('Domain event %s can\'t be stopped propagating.', new EventName($this)));
    }
}
