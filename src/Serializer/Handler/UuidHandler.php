<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Code originally created by David Kalosi for https://github.com/schmittjoh/serializer/pull/208
 */
class UuidHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $formats = array('json', 'xml', 'yml');

        foreach ($formats as $format) {
            $methods[] = array(
                'type' => 'Rhumsaa\Uuid\Uuid',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'method' => 'serializeUuid'
            );

            $methods[] = array(
                'type' => 'Rhumsaa\Uuid\Uuid',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'method' => 'deserializeUuid'
            );
        }

        return $methods;
    }

    public function serializeUuid(VisitorInterface $visitor, Uuid $uuid, array $type, Context $context)
    {
        return $visitor->visitString($uuid->toString(), $type, $context);
    }

    public function deserializeUuid(VisitorInterface $visitor, $data)
    {
        return Uuid::fromString($data);
    }
}
