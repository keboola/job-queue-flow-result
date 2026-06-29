<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use Keboola\JobQueueInternalClient\JobFactory\PlainJobInterface;

/**
 * The "created" entry for a job task: the static fields known the moment the task job is created.
 * startTime/duration/results are added later (processing/terminal) and merged in by FlowResultDocument.
 */
readonly class CreatedJobTaskResult extends AbstractTaskResult
{
    public function __construct(
        string $taskId,
        string $phaseId,
        string $name,
        private string $jobId,
        private string $componentId,
        private ?string $configId,
        private ?array $retry,
        private ?int $delay,
    ) {
        parent::__construct($taskId, $phaseId, $name, PlainJobInterface::STATUS_CREATED);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $entry = array_merge(parent::toArray(), [
            'jobId' => $this->jobId,
            'component' => $this->componentId,
            'config' => $this->configId,
            'status' => $this->getStatus(),
        ]);

        // Optional static fields are exported only when set, matching the terminal entry's shape.
        if ($this->retry) {
            $entry['retry'] = $this->retry;
        }
        if ($this->delay) {
            $entry['delay'] = $this->delay;
        }

        return $entry;
    }

    public function getTaskType(): string
    {
        return 'job';
    }
}
