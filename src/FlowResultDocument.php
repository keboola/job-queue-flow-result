<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult;

use JsonSerializable;

/**
 * Mutable view over a Conditional Flow job result. Callers add task, phase and child entries in the
 * order the lifecycle happens (created → processing → terminal); each call carries only the delta it
 * knows and is merged into the existing entry (matched by id), so no status precedence is enforced
 * here — later deltas overlay earlier ones, which matches the monotonic order of the write sites.
 */
class FlowResultDocument implements JsonSerializable
{
    /** @var array<mixed> */
    private array $document;

    /** @param array<mixed> $current */
    public function __construct(array $current)
    {
        $this->document = $current;
    }

    /** @param array<string, mixed> $task */
    public function addTask(array $task): void
    {
        $this->upsert('tasks', self::asString($task['id'] ?? ''), $task);
    }

    /** @param array<string, mixed> $phase */
    public function addPhase(array $phase): void
    {
        $this->upsert('phases', self::asString($phase['id'] ?? ''), $phase);
    }

    /**
     * Insert or replace a child job result inside a task's `results[]` by `jobId`.
     * No-op if the task is absent.
     *
     * @param array<string, mixed> $childDelta
     */
    public function updateChildResult(string $taskId, string $childJobId, array $childDelta): void
    {
        /** @var array<int, array<string, mixed>> $tasks */
        $tasks = (array) ($this->document['tasks'] ?? []);
        $taskIndex = $this->indexOf($tasks, $taskId);
        if ($taskIndex === null) {
            return;
        }

        /** @var array<int, array<string, mixed>> $children */
        $children = (array) ($tasks[$taskIndex]['results'] ?? []);
        $childIndex = $this->indexOfChild($children, $childJobId);

        if ($childIndex === null) {
            $children[] = $childDelta;
        } else {
            $children[$childIndex] = array_merge($children[$childIndex], $childDelta);
        }

        $tasks[$taskIndex]['results'] = array_values($children);
        $this->document['tasks'] = array_values($tasks);
    }

    /** @param array<string, mixed> $flow */
    public function setFlow(array $flow): void
    {
        $this->document['flow'] = $flow;
    }

    /** @return array<mixed> */
    public function jsonSerialize(): array
    {
        return $this->document;
    }

    /**
     * Current status of a task entry (matched by id), or null when the task is absent. Lets a caller
     * decide whether to advance an entry without re-implementing the lookup over the document structure.
     */
    public function getTaskStatus(string $id): ?string
    {
        return $this->statusAt((array) ($this->document['tasks'] ?? []), $id);
    }

    /** Current status of a phase entry (matched by id), or null when the phase is absent. */
    public function getPhaseStatus(string $id): ?string
    {
        return $this->statusAt((array) ($this->document['phases'] ?? []), $id);
    }

    /** Current status of a child result inside a task's results[] (matched by jobId), or null when absent. */
    public function getChildStatus(string $taskId, string $childJobId): ?string
    {
        /** @var array<int, array<string, mixed>> $tasks */
        $tasks = (array) ($this->document['tasks'] ?? []);
        $taskIndex = $this->indexOf($tasks, $taskId);
        if ($taskIndex === null) {
            return null;
        }

        /** @var array<int, array<string, mixed>> $children */
        $children = (array) ($tasks[$taskIndex]['results'] ?? []);
        $childIndex = $this->indexOfChild($children, $childJobId);

        return $childIndex === null ? null : self::statusValue($children[$childIndex]);
    }

    /**
     * Merge a delta into the existing entry with the same id, or insert it when absent.
     *
     * @param array<string, mixed> $entry
     */
    private function upsert(string $key, string $id, array $entry): void
    {
        /** @var array<int, array<string, mixed>> $entries */
        $entries = (array) ($this->document[$key] ?? []);
        $index = $this->indexOf($entries, $id);

        if ($index !== null) {
            $entries[$index] = array_merge($entries[$index], $entry);
        } else {
            $entries[] = $entry;
        }

        $this->document[$key] = array_values($entries);
    }

    /** @param array<mixed> $entries */
    private function indexOf(array $entries, string $id): ?int
    {
        foreach ($entries as $index => $entry) {
            if (is_array($entry) && self::asString($entry['id'] ?? '') === $id) {
                return (int) $index;
            }
        }
        return null;
    }

    /** @param array<mixed> $children */
    private function indexOfChild(array $children, string $childJobId): ?int
    {
        foreach ($children as $index => $child) {
            if (is_array($child) && self::asString($child['jobId'] ?? '') === $childJobId) {
                return (int) $index;
            }
        }
        return null;
    }

    /** @param array<mixed> $entries */
    private function statusAt(array $entries, string $id): ?string
    {
        $index = $this->indexOf($entries, $id);
        if ($index === null) {
            return null;
        }

        $entry = $entries[$index];
        return is_array($entry) ? self::statusValue($entry) : null;
    }

    /** @param array<string, mixed> $entry */
    private static function statusValue(array $entry): ?string
    {
        $status = $entry['status'] ?? null;
        return is_scalar($status) ? (string) $status : null;
    }

    private static function asString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
