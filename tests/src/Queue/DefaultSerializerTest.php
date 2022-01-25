<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\RoadRunnerBridge\Queue\DefaultSerializer;
use Spiral\Tests\TestCase;

final class DefaultSerializerTest extends TestCase
{
    private DefaultSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new DefaultSerializer();
    }

    public function testSerialize()
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $serializedPayload = $this->serializer->serialize([
            'int' => 1,
            'string' => 'foo',
            'array' => ['foo'],
            'object' => $object,
            'closure' => function () use ($object) {
                return $object;
            },
        ]);

        $this->assertIsArray(
            $payload = $this->serializer->deserialize($serializedPayload)
        );

        $this->assertSame(1, $payload['int']);
        $this->assertSame('foo', $payload['string']);
        $this->assertInstanceOf($object::class, $payload['object']);
        $this->assertTrue($payload['closure'] instanceof \Closure);
    }
}
