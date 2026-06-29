<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult;

/**
 * Mutable, I/O-free collector of flow-result deltas: task and phase entries plus an optional flow
 * entry, each a plain array. The collected deltas are applied (in insertion order) to a
 * FlowResultDocument via applyTo(), typically inside a single atomic patch callback.
 */
class FlowResultEntries
{
    /** @var list<array<string, mixed>> */
    private array $taskEntries = [];

    /** @var list<array<string, mixed>> */
    private array $phaseEntries = [];

    /** @var array<string, mixed>|null */
    private ?array $flowEntry = null;

    /** @param array<string, mixed> $task */
    public function addTask(array $task): void
    {
        $this->taskEntries[] = $task;
    }

    /** @param array<string, mixed> $phase */
    public function addPhase(array $phase): void
    {
        $this->phaseEntries[] = $phase;
    }

    /** @param array<string, mixed> $flow */
    public function setFlow(array $flow): void
    {
        $this->flowEntry = $flow;
    }

    public function isEmpty(): bool
    {
        return $this->taskEntries === [] && $this->phaseEntries === [] && $this->flowEntry === null;
    }

    public function applyTo(FlowResultDocument $document): FlowResultDocument
    {
        foreach ($this->taskEntries as $task) {
            $document->addTask($task);
        }
        foreach ($this->phaseEntries as $phase) {
            $document->addPhase($phase);
        }
        if ($this->flowEntry !== null) {
            $document->setFlow($this->flowEntry);
        }
        return $document;
    }
}
