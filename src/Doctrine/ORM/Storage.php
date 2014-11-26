<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine\ORM;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Model\Event;
use Boekkooi\Bundle\DoctrineEventStoreBundle\DomainEvent;
use Boekkooi\Bundle\DoctrineEventStoreBundle\Helper\EventName;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Serializer\Exclusion\DomainEventExclusion;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Storage implements \Boekkooi\Bundle\DoctrineEventStoreBundle\EventStore\Storage
{
    /**
     * @var EntityManager
     */
    private $manager;
    /**
     * @var EntityRepository
     */
    private $repository;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(EntityManager $manager, EntityRepository $repository, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /**
     * Store event stream data in persistence storage layer.
     *
     * Requires a check on the current version in the actual database
     * for optimistic locking purposes.
     *
     * @param Uuid $id
     * @param string $className
     * @param DomainEvent $event
     * @param int $nextVersion
     *
     * @return void
     */
    public function store(Uuid $id, $className, DomainEvent $event, $nextVersion)
    {
        $storeEvent = new Event($id, $className, $nextVersion, new EventName($event), get_class($event), $this->serialize($event));
        $this->manager->persist($storeEvent);

        // Dirty trick to fix problems with the event listener
        $this->manager->getUnitOfWork()->computeChangeSet(
            $this->manager->getClassMetadata(get_class($storeEvent)),
            $storeEvent
        );
    }

    /**
     * Find the latest version for the given id.
     * If the id can't be found 0 will be returned.
     *
     * @param Uuid $id
     * @param string $className
     * @return integer
     */
    public function findCurrentVersion(Uuid $id, $className)
    {
        $query = $this->repository->createNamedQuery('current_version');
        $query->setParameter('eventSourceId', $id, 'uuid');
        $query->setParameter('eventSourceType', $className, 'string');
        $res = $query->getScalarResult();

        if (empty($res)) {
            return 0;
        }
        return intval($res[0]['version']);
    }

    /**
     * Serialize the domain event for storage.
     *
     * @param DomainEvent $event
     * @return string
     */
    protected function serialize(DomainEvent $event)
    {
        $context = SerializationContext::create()
            ->addExclusionStrategy(new DomainEventExclusion());
        return $this->serializer->serialize($event, 'json', $context);
    }
}
