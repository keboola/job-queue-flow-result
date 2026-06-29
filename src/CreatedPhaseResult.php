<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult;

use Keboola\JobQueueInternalClient\JobFactory\PlainJobInterface;

/**
 * The "created" entry for a phase: written when its phase container job is created, so the phase is
 * visible while queued. startTime/jobId/duration are added later (processing/terminal) and merged in
 * by FlowResultDocument.
 */
readonly class CreatedPhaseResult
{
    public function __construct(
        private string $id,
        private string $name,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => PlainJobInterface::STATUS_CREATED,
        ];
    }
}
