<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine\ORM\EventListener;

use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSource;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\RuntimeException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class EventSourceListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $eventSourceClass;

    private $changes = array();

    public function __construct(ContainerInterface $container)
    {
        if (!$container->has('boekkooi.doctrine_event_store.unit_of_work')) {
            throw new \InvalidArgumentException();
        }

        $this->container = $container;
        $this->eventSourceClass = 'Boekkooi\\Bundle\\DoctrineEventStoreBundle\\Model\\EventSource';
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
            Events::postFlush
        );
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var ClassMetadataInfo $metadata */
        $metadata = $event->getClassMetadata();
        $refl = $metadata->getReflectionClass();
        if ($refl === null || !$refl->isSubclassOf($this->eventSourceClass)) {
            return;
        }

        $this->registerEventListener($metadata, Events::preFlush, 'preFlushHandler');
        $this->registerEventListener($metadata, Events::preRemove, 'preRemoveHandler');
        $this->registerEventListener($metadata, Events::postRemove, 'postRemoveHandler');
    }

    public function preFlushHandler(EventSource $object)
    {
        unset($this->changes[spl_object_hash($object)]);

        if ($this->getUnitOfWork()->save($object) > 0) {
            $this->changes[spl_object_hash($object)] = true;
            return;
        }
    }

    public function preRemoveHandler(EventSource $object)
    {
        if ($this->getUnitOfWork()->save($object) > 0) {
            $this->changes[spl_object_hash($object)] = true;
            return;
        }

        if (isset($this->changes[spl_object_hash($object)])) {
            return;
        }

        throw new RuntimeException('Unable to remove domain model, no removal event was found.');
    }

    public function postRemoveHandler(EventSource $object)
    {
        unset($this->changes[spl_object_hash($object)]);
    }

    public function postFlush()
    {
        $this->getUnitOfWork()->dispatch();
    }

    /**
     * @return \Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine\UnitOfWork
     */
    protected function getUnitOfWork()
    {
        return $this->container->get('boekkooi.doctrine_event_store.unit_of_work');
    }

    protected function registerEventListener(ClassMetadataInfo $metadata, $eventName, $method)
    {
        $class = $metadata->fullyQualifiedClassName(__CLASS__);

        // Check if the event listener is not already registered
        $listener = array(
            'class'  => $class,
            'method' => $method
        );
        if (
            isset($metadata->entityListeners[$eventName]) &&
            in_array($listener, $metadata->entityListeners[$eventName])
        ) {
            return;
        }

        // Register listener
        $metadata->addEntityListener($eventName, $class, $method);
    }
}
