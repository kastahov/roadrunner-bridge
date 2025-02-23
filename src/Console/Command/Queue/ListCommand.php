<?php

declare(strict_types=1);

namespace Spiral\RoadRunnerBridge\Console\Command\Queue;

use Spiral\Console\Command;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\Queue;
use Symfony\Component\Console\Helper\Table;

final class ListCommand extends Command
{
    protected const SIGNATURE = 'roadrunner:list';
    protected const DESCRIPTION = 'List available roadrunner pipelines';

    public function perform(JobsInterface $jobs): int
    {
        $queues = \iterator_to_array($jobs->getIterator());

        if ($queues === []) {
            return self::SUCCESS;
        }

        $table = new Table($this->output);

        $table->setHeaders(
            ['Name', 'Driver', 'Priority', 'Active jobs', 'Delayed jobs', 'Reserved jobs', 'Is active']
        );

        foreach ($queues as $queue) {
            /** @var Queue $queue */

            $stat = $queue->getPipelineStat();

            $table->addRow([
                $stat->getPipeline(),
                $stat->getDriver(),
                $stat->getPriority(),
                $stat->getActive(),
                $stat->getDelayed(),
                $stat->getReserved(),
                $stat->getReady() ? '<fg=green> ✓ </>' : '<fg=red> ✖ </>',
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
