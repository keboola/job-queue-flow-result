<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use DateTimeImmutable;

readonly class VariableTypeResult extends AbstractTaskResult
{
    public function __construct(
        string $taskId,
        string $phaseId,
        string $name,
        string $status,
        private array $result,
        private DateTimeImmutable $startTime,
        private int $duration,
    ) {
        parent::__construct($taskId, $phaseId, $name, $status);
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getStartTime(): DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'status' => $this->getStatus(),
                'results' => $this->getResult(),
                'startTime' => $this->getStartTime()->format('c'),
                'duration' => $this->getDuration(),
            ],
        );
    }

    public function getTaskType(): string
    {
        return 'variable';
    }
}
