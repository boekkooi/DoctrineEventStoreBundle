<?php
namespace Tests\Boekkooi\Bundle\DoctrineEventStoreBundle\Serializer\Handler;

use Boekkooi\Bundle\DoctrineEventStoreBundle\Serializer\Handler\UuidHandler;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Handler\HandlerRegistry;

/**
 * Code originally created by David Kalosi for https://github.com/schmittjoh/serializer/pull/208
 */
class UuidHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @var \Rhumsaa\Uuid\Uuid
     */
    private $uuid;

    public function setUp()
    {
        $this->serializer = SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(new UuidHandler());
                })
            ->build();

        $this->uuid = Uuid::uuid1();
    }

    public function testSerializeJson()
    {
        $subject = new ObjectWithUuid($this->uuid);
        $json = $this->serializer->serialize($subject, 'json');
        $decoded = json_decode($json);

        $this->assertEquals($this->uuid->toString(), $decoded->uuid);

        $serializedUuid = Uuid::fromString($decoded->uuid);

        $this->assertEquals(0, $this->uuid->compareTo($serializedUuid));
        $this->assertTrue($this->uuid->equals($serializedUuid));
    }

    /**
     * @depends testSerializeJson
     */
    public function testDeserializeJson()
    {
        $subject = $this->serializer->deserialize('{"uuid":"ed34c88e-78b0-11e3-9ade-406c8f20ad00"}', __NAMESPACE__ . '\\ObjectWithUuid', 'json');

        $this->assertEquals(0, $subject->getUuid()->compareTo(Uuid::fromString("ed34c88e-78b0-11e3-9ade-406c8f20ad00")));
        $this->assertTrue($subject->getUuid()->equals(Uuid::fromString("ed34c88e-78b0-11e3-9ade-406c8f20ad00")));
    }

}

class ObjectWithUuid
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     */
    protected $uuid;

    public function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function setUuid(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid()
    {
        return $this->uuid;
    }
}
