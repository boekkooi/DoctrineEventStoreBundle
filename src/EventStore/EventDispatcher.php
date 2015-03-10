<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class EventDispatcher extends ContainerAwareEventDispatcher 
{
    /**
     * @var \SplQueue
     */
    private $events;

    /**
     * @var bool
     */
    private $isDispatching = false;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->events = new \SplQueue();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function dispatch($eventName, Event $event = null)
    {
        $this->events->enqueue([$eventName, $event]);
        if ($this->isDispatching) {
            return;
        }

        $this->isDispatching = true;
        while (!$this->events->isEmpty()) {
            list($eventName, $event) = $this->events->dequeue();
            parent::dispatch($eventName, $event);
        }
        $this->isDispatching = false;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            // TODO catch exceptions and dispatch a DomainListenerExceptionEvent
            call_user_func($listener, $event, $eventName, $this);
        }
    }
}
