<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use DateTimeImmutable;
use Keboola\JobQueueInternalClient\JobFactory\JobType;

readonly class JobTypeResult extends AbstractTaskResult
{
    public function __construct(
        string $taskId,
        string $phaseId,
        string $name,
        string $status,
        private string $jobId,
        private JobType $jobType,
        private string $componentId,
        private ?string $configId,
        private ?DateTimeImmutable $startTime,
        private ?int $duration,
        private ChildJobResult $result,
        private ?array $retry,
    ) {
        parent::__construct($taskId, $phaseId, $name, $status);
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getComponentId(): string
    {
        return $this->componentId;
    }

    public function getConfigId(): ?string
    {
        return $this->configId;
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getJobType(): JobType
    {
        return $this->jobType;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function getRetry(): ?array
    {
        return $this->retry;
    }

    /**
     * The terminal task entry is written in full (not a delta): all data is known at terminal time, so
     * the entry stays self-sufficient even if the earlier created/processing deltas were lost (best-effort
     * projection) or never written (a flow that started on a pre-feature daemon during a rolling deploy).
     */
    public function toArray(): array
    {
        $return = array_merge(
            parent::toArray(),
            [
                'jobId' => $this->getJobId(),
                'component' => $this->getComponentId(),
                'config' => $this->getConfigId(),
                'startTime' => $this->getStartTime()?->format('c'),
                'status' => $this->getStatus(),
                'duration' => $this->getDuration(),
                'results' => [$this->result->toArray()],
            ],
        );

        if ($this->getRetry()) {
            $return['retry'] = $this->getRetry();
        }

        if ($this->result->getDelay()) {
            $return['delay'] = $this->result->getDelay();
        }

        return $return;
    }

    public function getTaskType(): string
    {
        return 'job';
    }
}
