<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Model;

use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Event
{
    /**
     * @var Uuid
     */
    private $eventSourceId;

    /**
     * @var integer
     */
    private $version;

    /**
     * @var string
     */
    private $eventSourceType;

    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string
     */
    private $data;

    /**
     * @var float
     */
    protected $createdAt;

    public function __construct($eventSourceId, $eventSourceType, $version, $event, $eventType, $data)
    {
        $this->eventSourceId = $eventSourceId;
        $this->eventSourceType = $eventSourceType;
        $this->version = $version;
        $this->event = $event;
        $this->eventType = $eventType;
        $this->data = $data;
        $this->createdAt = microtime(true);
    }

    /**
     * @return Uuid
     */
    public function getEventSourceId()
    {
        return $this->eventSourceId;
    }

    /**
     * @return string
     */
    public function getEventSourceType()
    {
        return $this->eventSourceType;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns createdAt value.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return (new \DateTime())->setTimeStamp($this->createdAt);
    }
}
