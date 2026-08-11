<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

/**
 * The terminal entry for a job task whose job was never created (e.g. Storage API refused the request).
 * The counterpart of CreatedJobTaskResult: the task failed before it got a job, so the entry carries no
 * jobId, no results[] and no startTime/duration - only the static fields, the error status and the
 * message explaining why the job could not be created. Nothing follows it, the task never ran.
 */
readonly class UncreatedJobTaskResult extends AbstractTaskResult
{
    public function __construct(
        string $taskId,
        string $phaseId,
        string $name,
        string $status,
        private string $componentId,
        private ?string $configId,
        private string $message,
    ) {
        parent::__construct($taskId, $phaseId, $name, $status);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'component' => $this->componentId,
            'config' => $this->configId,
            'status' => $this->getStatus(),
            'message' => $this->message,
        ]);
    }

    public function getTaskType(): string
    {
        return 'job';
    }
}
