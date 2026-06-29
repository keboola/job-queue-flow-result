<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use Keboola\JobQueue\FlowResult\Task\CreatedJobTaskResult;
use PHPUnit\Framework\TestCase;

class CreatedJobTaskResultTest extends TestCase
{
    public function testToArrayWithRetryAndDelay(): void
    {
        $result = new CreatedJobTaskResult(
            'task-1',
            'phase-1',
            'Task One',
            'job-1',
            'keboola.ex-db',
            'cfg-1',
            ['strategyParams' => ['maxRetries' => 3]],
            30,
        );

        self::assertSame(
            [
                'id' => 'task-1',
                'name' => 'Task One',
                'type' => 'job',
                'phase' => 'phase-1',
                'jobId' => 'job-1',
                'component' => 'keboola.ex-db',
                'config' => 'cfg-1',
                'status' => 'created',
                'retry' => ['strategyParams' => ['maxRetries' => 3]],
                'delay' => 30,
            ],
            $result->toArray(),
        );
    }

    public function testToArrayExportsOnlyWhatIsSet(): void
    {
        $result = new CreatedJobTaskResult(
            'task-1',
            'phase-1',
            'Task One',
            'job-1',
            'keboola.ex-db',
            null,
            null,
            null,
        );

        $entry = $result->toArray();

        self::assertSame('created', $entry['status']);
        self::assertNull($entry['config']);
        self::assertArrayNotHasKey('retry', $entry);
        self::assertArrayNotHasKey('delay', $entry);
    }
}
