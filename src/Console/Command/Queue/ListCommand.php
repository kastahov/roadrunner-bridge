<?php

declare(strict_types=1);

namespace Spiral\RoadRunnerBridge\Console\Command\Queue;

use Spiral\Console\Command;
use Spiral\RoadRunner\Jobs\DTO\V1\Stat;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Symfony\Component\Console\Helper\Table;

final class ListCommand extends Command
{
    protected const NAME = 'queue:list';
    protected const DESCRIPTION = 'List available queue connections';

    public function perform(JobsInterface $jobs): void
    {
        $table = new Table($this->output);

        $table->setHeaders(
            ['Name', 'Driver', 'Default delay', 'Priority', 'Active jobs', 'Delayed jobs', 'Reserved jobs', 'Is active']
        );

        foreach ($jobs as $queue) {
            $options = $queue->getDefaultOptions();

            /** @var Stat $stat */
            $stat = $queue->getPipelineStat();

            $table->addRow([
                $stat->getPipeline(),
                $stat->getDriver(),
                $options->getDelay(),
                $options->getPriority(),
                $stat->getActive(),
                $stat->getDelayed(),
                $stat->getReserved(),
                $queue->isPaused() ? '<fg=red> ✖ </>' : '<fg=green> ✓ </>',
            ]);
        }

        $table->render();
    }
}
