<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Exception\JobException;
use Spiral\Queue\HandlerInterface;
use Spiral\RoadRunnerBridge\Queue\ContainerRegistry;
use Spiral\Tests\TestCase;

final class ContainerRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new ContainerRegistry($this->container);
    }

    public function testGetsHandlerByJobType()
    {
        $this->container->bind('Mail\Job', $handler = \Mockery::mock(HandlerInterface::class));
        $this->registry->getHandler('mail.job');

        $this->assertSame($handler, $this->registry->getHandler('mail.job'));
    }

    public function testGetsHandlerWithWrongInterface()
    {
        $this->expectException(JobException::class);
        $this->expectErrorMessage('Unable to resolve job handler for `mail.job`');

        $this->container->bind('Mail\Job', $handler = \Mockery::mock('test'));
        $this->registry->getHandler('mail.job');
    }

    public function testGetsNotExistsHandler()
    {
        $this->expectException(JobException::class);
        $this->expectErrorMessage("Undefined class or binding 'Mail\Job'");

        $this->registry->getHandler('mail.job');
    }
}
