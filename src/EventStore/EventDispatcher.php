<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class EventDispatcher extends ContainerAwareEventDispatcher {
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
