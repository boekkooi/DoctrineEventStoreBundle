<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Serializer\Exclusion;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DomainEventExclusion implements ExclusionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return false;
    }

    /**
     * Whether the property should be skipped.
     *
     * @param PropertyMetadata $property     *
     * @param Context $context
     * @return bool
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        return in_array(
            $property->class,
            array(
                'Symfony\Component\EventDispatcher\Event',
                'Boekkooi\Bundle\DoctrineEventStoreBundle\DefaultDomainEvent'
            ),
            true
        );
    }
}
