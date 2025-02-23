<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\Queue\CreateInfoInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunnerBridge\Queue\JobsAdapterSerializer;
use Spiral\RoadRunnerBridge\Queue\RPCPipelineRegistry;
use Spiral\Tests\TestCase;

final class RPCPipelineRegistryTest extends TestCase
{
    /** @var CreateInfoInterface|m\LegacyMockInterface|m\MockInterface */
    private $memoryConnector;
    /** @var CreateInfoInterface|m\LegacyMockInterface|m\MockInterface */
    private $localConnector;
    private RPCPipelineRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = (new RPCPipelineRegistry(
            $this->jobs = m::mock(JobsInterface::class),
            $this->getContainer()->get(JobsAdapterSerializer::class),
            [
                'memory' => [
                    'connector' => $this->memoryConnector = m::mock(CreateInfoInterface::class),
                    'cunsume' => true,
                ],
                'local' => [
                    'connector' => $this->localConnector = m::mock(CreateInfoInterface::class),
                    'consume' => false,
                ],
                'without-connector' => [
                    'cunsume' => true,
                ],
                'with-wrong-connector' => [
                    'connector' => 'test',
                    'cunsume' => true,
                ],
            ],
            [
                'user-data' => 'memory',
                'bad-alias' => 'test',
            ],
            60
        ));
    }

    public function testGetsExistsPipelineByNameShouldReturnQueue(): void
    {
        $this->memoryConnector->shouldReceive('getName')->andReturn('local');

        $this->jobs->shouldReceive('getIterator')->once()->andReturn(new \ArrayIterator(['local' => '']));
        $this->jobs->shouldReceive('connect')
            ->once()
            ->with('local')
            ->andReturn($queue = m::mock(QueueInterface::class));

        $this->assertInstanceOf(
            QueueInterface::class,
            $this->registry->getPipeline('memory', 'some')
        );
    }

    public function testGetsNonExistsPipelineByNameShouldCreateItAndReturnQueue(): void
    {
        $this->memoryConnector->shouldReceive('getName')->once()->andReturn('local');

        $this->jobs->shouldReceive('getIterator')->once()->andReturn(new \ArrayIterator(['memory']));
        $this->jobs->shouldReceive('create')
            ->once()
            ->with($this->memoryConnector)
            ->andReturn($queue = m::mock(QueueInterface::class));

        $queue->shouldReceive('resume')->once();

        $this->assertInstanceOf(
            QueueInterface::class,
            $this->registry->getPipeline('memory', 'some')
        );
    }

    public function testGetsNonExistsPipelineByNameWithoutConsumingShouldCreateItAndReturnQueue(): void
    {
        $this->localConnector->shouldReceive('getName')->once()->andReturn('local');

        $this->jobs->shouldReceive('getIterator')->once()->andReturn(new \ArrayIterator(['memory']));
        $this->jobs->shouldReceive('create')
            ->once()
            ->with($this->localConnector)
            ->andReturn($queue = m::mock(QueueInterface::class));

        $this->assertInstanceOf(
            QueueInterface::class,
            $this->registry->getPipeline('local', 'some')
        );
    }

    public function testGetsExistsPipelineByAliasShouldReturnQueue(): void
    {
        $this->memoryConnector->shouldReceive('getName')->once()->andReturn('local');

        $this->jobs->shouldReceive('getIterator')->once()->andReturn(new \ArrayIterator(['memory']));
        $this->jobs->shouldReceive('create')
            ->once()
            ->with($this->memoryConnector)
            ->andReturn($queue = m::mock(QueueInterface::class));

        $queue->shouldReceive('resume')->once();

        $this->assertInstanceOf(
            QueueInterface::class,
            $this->registry->getPipeline('user-data', 'some')
        );
    }

    public function testGetsNonExistsPipelineShouldReturnQueue(): void
    {
        $this->jobs->shouldReceive('connect')
            ->once()
            ->with('test')
            ->andReturn($queue = m::mock(QueueInterface::class));

        $this->assertSame($queue, $this->registry->getPipeline('test', 'some'));
    }

    public function testGetsNonExistsAliasPipelineShouldReturnQueue(): void
    {
        $this->jobs->shouldReceive('connect')
            ->once()
            ->with('test')
            ->andReturn($queue = m::mock(QueueInterface::class));

        $this->assertSame($queue, $this->registry->getPipeline('bad-alias', 'some'));
    }

    public function testGetsPipelineWithoutConnectorShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('You must specify connector for given pipeline `without-connector`.');

        $this->registry->getPipeline('without-connector', 'some');
    }

    public function testGetsPipelineWithWrongConnectorShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Connector should implement Spiral\RoadRunner\Jobs\Queue\CreateInfoInterface interface.');

        $this->registry->getPipeline('with-wrong-connector', 'some');
    }
}
