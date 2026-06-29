<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use Keboola\JobQueueInternalClient\JobFactory\JobInterface;

class DelayHelper
{
    public static function computeDelay(JobInterface $job): ?int
    {
        if (!$job->getDelayedStartTime() || !$job->getCreatedTime()) {
            return null;
        }

        return $job->getDelayedStartTime()->getTimestamp() - $job->getCreatedTime()->getTimestamp();
    }
}
