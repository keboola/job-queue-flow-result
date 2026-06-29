<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Condition\AbstractConditionResult;

class PhaseResult
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $status,
        private readonly ?DateTimeImmutable $startTime,
        private readonly ?int $duration,
        private readonly ?string $jobId = null,
        /** @var AbstractConditionResult[] */
        private array $conditionsResults = [],
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function getConditionsResults(): array
    {
        return $this->conditionsResults;
    }

    public function addConditionResult(AbstractConditionResult $conditionResult): void
    {
        $this->conditionsResults[] = $conditionResult;
    }
}
