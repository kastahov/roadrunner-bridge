<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Job;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\RoadRunnerBridge\Queue\Exception\InvalidArgumentException;
use Spiral\RoadRunnerBridge\Queue\Job\CallableJob;
use Spiral\Tests\TestCase;

final class CallableJobTest extends TestCase
{
    public function testPayloadCallbackKeyIsRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Payload `callback` key is required.');

        $job = new CallableJob($this->container);
        $job->handle('foo', 'foo-id', []);
    }

    public function testPayloadCallbackValueShouldBeClosure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Payload `callback` key value type should be a closure.');

        $job = new CallableJob($this->container);
        $job->handle('foo', 'foo-id', ['callback' => 'test']);
    }

    public function testHandle()
    {
        $callback = function (string $name, string $id, ContainerInterface $container) {
            $this->assertSame('foo', $name);
            $this->assertSame('foo-id', $id);
            $this->assertInstanceOf(Container::class, $container);
        };

        $job = new CallableJob($this->container);

        $job->handle('foo', 'foo-id', ['callback' => $callback]);
    }
}
