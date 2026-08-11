<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use Keboola\JobQueue\FlowResult\Task\UncreatedJobTaskResult;
use PHPUnit\Framework\TestCase;

class UncreatedJobTaskResultTest extends TestCase
{
    public function testToArray(): void
    {
        $result = new UncreatedJobTaskResult(
            'task-1',
            'phase-1',
            'Task One',
            'user_error',
            'keboola.ex-db',
            'cfg-1',
            "You don't have access to the resource.",
        );

        self::assertSame(
            [
                'id' => 'task-1',
                'name' => 'Task One',
                'type' => 'job',
                'phase' => 'phase-1',
                'component' => 'keboola.ex-db',
                'config' => 'cfg-1',
                'status' => 'user_error',
                'message' => "You don't have access to the resource.",
            ],
            $result->toArray(),
        );
    }

    public function testToArrayHasNoJobFields(): void
    {
        $result = new UncreatedJobTaskResult(
            'task-1',
            'phase-1',
            'Task One',
            'application_error',
            'keboola.ex-db',
            null,
            'Internal error.',
        );

        $entry = $result->toArray();

        // The task never got a job, so nothing job-bound or time-bound may be exported.
        self::assertArrayNotHasKey('jobId', $entry);
        self::assertArrayNotHasKey('results', $entry);
        self::assertArrayNotHasKey('startTime', $entry);
        self::assertArrayNotHasKey('duration', $entry);
        self::assertNull($entry['config']);
    }

    public function testGetters(): void
    {
        $result = new UncreatedJobTaskResult(
            'task-1',
            'phase-1',
            'Task One',
            'user_error',
            'keboola.ex-db',
            'cfg-1',
            'Nope.',
        );

        self::assertSame('task-1', $result->getTaskId());
        self::assertSame('phase-1', $result->getPhaseId());
        self::assertSame('Task One', $result->getName());
        self::assertSame('user_error', $result->getStatus());
        self::assertSame('job', $result->getTaskType());
    }
}
