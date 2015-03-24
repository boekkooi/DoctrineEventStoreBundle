<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Model;

use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSourceEventMethods;
use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
abstract class EventSource implements \Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSource
{
    use EventSourceEventMethods

    /**
     * @var \Rhumsaa\Uuid\Uuid
     */
    private $id;

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
}
