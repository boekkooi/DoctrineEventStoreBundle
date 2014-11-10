<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Doctrine\ORM\EventListener;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class EventSourceListenerResolver extends DefaultEntityListenerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $listenerClass;

    /**
     * Constructor.
     * Injecting the container to allow for lazy loading. (Yes it's ugly but I don't want to create a callable etc.)
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$container->has('boekkooi.doctrine_event_store.listener')) {
            throw new \InvalidArgumentException();
        }
        $this->container = $container;

        if ($container->hasParameter('boekkooi.doctrine_event_store.listener.class')) {
            $this->listenerClass = $container->getParameter('boekkooi.doctrine_event_store.listener.class');
        } else {
            $this->listenerClass = __NAMESPACE__  . '\\EventSourceListener';
        }
    }

    public function resolve($className)
    {
        if ($className === $this->listenerClass) {
            return $this->container->get('boekkooi.doctrine_event_store.listener');
        }

        return parent::resolve($className);
    }
}
