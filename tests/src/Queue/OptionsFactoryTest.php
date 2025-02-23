<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Options;
use Spiral\RoadRunner\Jobs\Options as JobsOptions;
use Spiral\RoadRunner\Jobs\OptionsInterface as JobsOptionsInterface;
use PHPUnit\Framework\TestCase;
use Spiral\RoadRunnerBridge\Queue\OptionsFactory;

final class OptionsFactoryTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(?JobsOptionsInterface $expected, mixed $from): void
    {
        $this->assertEquals($expected, OptionsFactory::create($from));
    }

    public function createDataProvider(): \Traversable
    {
        yield [null, null];
        yield [new JobsOptions(), new Options()];
        yield [new JobsOptions(5), (new Options())->withDelay(5)];
        yield [
            (new JobsOptions())->withHeader('foo', 'bar'),
            (new Options())->withHeader('foo', 'bar'),
        ];
        yield [
            (new JobsOptions())->withPriority(4)->withDelay(6)->withAutoAck(true),
            (new JobsOptions())->withPriority(4)->withDelay(6)->withAutoAck(true),
        ];
    }
}
