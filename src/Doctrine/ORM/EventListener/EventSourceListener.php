<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine\ORM\EventListener;

use Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\EventSource;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Exception\RuntimeException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Model\Event;

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

    /**
     * @var \ReflectionProperty
     */
    private $eventSourceReflection;

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
        if ($metadata->isMappedSuperclass) {
            return;
        }

        $refl = $metadata->getReflectionClass();
        if ($refl !== null && $refl->isSubclassOf($this->eventSourceClass)) {
            $metadata->addEntityListener(Events::preRemove, __CLASS__, 'preRemoveHandler');
            $metadata->addEntityListener(Events::preFlush, __CLASS__, 'preFlushHandler');
        }
    }

    public function preRemoveHandler(EventSource $object, LifecycleEventArgs $event)
    {
        $this->getUnitOfWork()->save($object);

        $events = $this->getEventSourceEvents($object, $event);
        if (count($events) > 0) {
            return;
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $event->getObjectManager();
        $insertions = $em->getUnitOfWork()->getScheduledEntityInsertions();
        foreach ($insertions as $insertion) {
            if (!$insertion instanceof Event) {
                continue;
            }
            // So there is a event in the unit of work well done.
            // Thank you doctrine orphan order :|
            if ($insertion->getEventSourceId() === $object->getId()) {
                return;
            }
        }

        throw new RuntimeException('Unable to remove domain model, no removal event was found.');
    }

    public function preFlushHandler(EventSource $object)
    {
        $this->getUnitOfWork()->save($object);
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

    protected function getEventSourceEvents($object, LifecycleEventArgs $event)
    {
        if ($this->eventSourceReflection === null) {
            $reflection = $event->getObjectManager()
                ->getClassMetadata($this->eventSourceClass)
                ->getReflectionClass()
                ->getProperty('events');
            $reflection->setAccessible(true);

            $this->eventSourceReflection = $reflection;
        }

        return $this->eventSourceReflection->getValue($object);
    }
}
