<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use Keboola\JobQueueInternalClient\JobFactory\JobInterface;

class DelayHelper
{
    public static function computeDelay(JobInterface $job): ?int
    {
        // Prefer the delay recorded with the job. Jobs created before `requestedDelay` was
        // persisted fall back to deriving it from the delayed start and created times, which is
        // approximate: delayedStartTime is computed client-side at object construction while
        // createdTime is generated separately, usually by the database.
        $requestedDelay = $job->getRequestedDelay();
        if ($requestedDelay !== null) {
            return $requestedDelay;
        }

        if (!$job->getDelayedStartTime() || !$job->getCreatedTime()) {
            return null;
        }

        return $job->getDelayedStartTime()->getTimestamp() - $job->getCreatedTime()->getTimestamp();
    }
}
