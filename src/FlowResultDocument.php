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
        $this->upsert('tasks', $task);
    }

    /** @param array<string, mixed> $phase */
    public function addPhase(array $phase): void
    {
        $this->upsert('phases', $phase);
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
        $taskIndex = self::indexOf($tasks, 'id', $taskId);
        if ($taskIndex === null) {
            return;
        }

        /** @var array<int, array<string, mixed>> $children */
        $children = (array) ($tasks[$taskIndex]['results'] ?? []);
        $childIndex = self::indexOf($children, 'jobId', $childJobId);

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

    /** Current status of a task entry (matched by id), or null when the task is absent. */
    public function getTaskStatus(string $id): ?string
    {
        return self::statusOf((array) ($this->document['tasks'] ?? []), 'id', $id);
    }

    /** Current status of a phase entry (matched by id), or null when the phase is absent. */
    public function getPhaseStatus(string $id): ?string
    {
        return self::statusOf((array) ($this->document['phases'] ?? []), 'id', $id);
    }

    /** Current status of a child result inside a task's results[] (matched by jobId), or null when absent. */
    public function getChildStatus(string $taskId, string $childJobId): ?string
    {
        /** @var array<int, array<string, mixed>> $tasks */
        $tasks = (array) ($this->document['tasks'] ?? []);
        $taskIndex = self::indexOf($tasks, 'id', $taskId);
        if ($taskIndex === null) {
            return null;
        }

        /** @var array<int, array<string, mixed>> $children */
        $children = (array) ($tasks[$taskIndex]['results'] ?? []);
        return self::statusOf($children, 'jobId', $childJobId);
    }

    /**
     * Merge a delta into the existing entry with the same id, or append it when absent.
     *
     * @param array<string, mixed> $entry
     */
    private function upsert(string $section, array $entry): void
    {
        /** @var array<int, array<string, mixed>> $entries */
        $entries = (array) ($this->document[$section] ?? []);
        /** @var string $id always present and a string in every delta produced by the result DTOs */
        $id = $entry['id'];
        $index = self::indexOf($entries, 'id', $id);

        if ($index !== null) {
            $entries[$index] = array_merge($entries[$index], $entry);
        } else {
            $entries[] = $entry;
        }

        $this->document[$section] = array_values($entries);
    }

    /**
     * Index of the first entry whose $key equals $value, or null when none matches.
     *
     * @param array<mixed> $entries
     */
    private static function indexOf(array $entries, string $key, string $value): ?int
    {
        foreach ($entries as $index => $entry) {
            if (is_array($entry) && ($entry[$key] ?? null) === $value) {
                return (int) $index;
            }
        }
        return null;
    }

    /**
     * Status (as a string) of the first entry whose $key equals $value, or null when absent.
     *
     * @param array<mixed> $entries
     */
    private static function statusOf(array $entries, string $key, string $value): ?string
    {
        $index = self::indexOf($entries, $key, $value);
        $entry = $index === null ? null : $entries[$index];
        $status = is_array($entry) ? ($entry['status'] ?? null) : null;
        return is_scalar($status) ? (string) $status : null;
    }
}
