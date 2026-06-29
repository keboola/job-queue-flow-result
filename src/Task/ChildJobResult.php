<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Task;

use DateTimeImmutable;

readonly class ChildJobResult
{
    public function __construct(
        private string $jobId,
        private string $status,
        private array $result,
        private ?DateTimeImmutable $startTime,
        private ?int $duration,
        private ?int $delay,
    ) {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    public function toArray(): array
    {
        $return = [
            'jobId' => $this->getJobId(),
            'status' => $this->getStatus(),
            'duration' => $this->getDuration(),
            'startTime' => $this->getStartTime()?->format('c'),
            'result' => $this->result,
        ];

        if ($this->getDelay()) {
            $return['delay'] = $this->getDelay();
        }
        return $return;
    }
}
