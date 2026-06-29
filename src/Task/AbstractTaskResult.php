<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

abstract readonly class AbstractTaskResult
{
    public function __construct(
        protected string $taskId,
        protected string $phaseId,
        protected string $name,
        protected string $status,
    ) {
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getPhaseId(): string
    {
        return $this->phaseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    abstract public function getTaskType(): string;

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->getTaskId(),
            'name' => $this->getName(),
            'type' => $this->getTaskType(),
            'phase' => $this->getPhaseId(),
        ];
    }
}
