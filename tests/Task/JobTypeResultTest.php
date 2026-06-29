<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Task\ChildJobResult;
use Keboola\JobQueue\FlowResult\Task\JobTypeResult;
use Keboola\JobQueueInternalClient\JobFactory\JobType;
use PHPUnit\Framework\TestCase;

class JobTypeResultTest extends TestCase
{
    private function createChildJobResult(): ChildJobResult
    {
        return new ChildJobResult(
            'job-799',
            'success',
            ['output' => 'test-output', 'metrics' => ['count' => 5]],
            new DateTimeImmutable('2023-01-01T12:00:01+00:00'),
            299,
            29,
        );
    }

    public function testGetters(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');
        $result = new JobTypeResult(
            'task-123',
            'phase-456',
            'Test Task',
            'success',
            'job-789',
            JobType::STANDARD,
            'keboola.ex-db',
            'config-1',
            $startTime,
            300,
            $this->createChildJobResult(),
            null,
        );

        self::assertSame('task-123', $result->getTaskId());
        self::assertSame('phase-456', $result->getPhaseId());
        self::assertSame('Test Task', $result->getName());
        self::assertSame('success', $result->getStatus());
        self::assertSame('job-789', $result->getJobId());
        self::assertSame(JobType::STANDARD, $result->getJobType());
        self::assertSame('keboola.ex-db', $result->getComponentId());
        self::assertSame('config-1', $result->getConfigId());
        self::assertSame($startTime, $result->getStartTime());
        self::assertSame(300, $result->getDuration());
        self::assertNull($result->getRetry());
        self::assertSame('job', $result->getTaskType());
    }

    public function testToArrayIsSelfSufficient(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');
        $result = new JobTypeResult(
            'task-123',
            'phase-456',
            'Test Task',
            'success',
            'job-789',
            JobType::STANDARD,
            'keboola.ex-db',
            'config-1',
            $startTime,
            300,
            $this->createChildJobResult(),
            null,
        );

        // The terminal task entry is written in full (not a delta): every known field is repeated, so the
        // entry renders correctly even if the earlier created/processing writes were lost or never made.
        $expected = [
            'id' => 'task-123',
            'name' => 'Test Task',
            'type' => 'job',
            'phase' => 'phase-456',
            'jobId' => 'job-789',
            'component' => 'keboola.ex-db',
            'config' => 'config-1',
            'startTime' => '2023-01-01T12:00:00+00:00',
            'status' => 'success',
            'duration' => 300,
            'results' => [
                [
                    'jobId' => 'job-799',
                    'status' => 'success',
                    'duration' => 299,
                    'startTime' => '2023-01-01T12:00:01+00:00',
                    'result' => ['output' => 'test-output', 'metrics' => ['count' => 5]],
                    'delay' => 29,
                ],
            ],
            'delay' => 29,
        ];

        self::assertEquals($expected, $result->toArray());
    }

    public function testToArrayIncludesRetryAndOmitsNullStaticFields(): void
    {
        $result = new JobTypeResult(
            'task-456',
            'phase-789',
            'Another Task',
            'failed',
            'job-456',
            JobType::RETRY_CONTAINER,
            'keboola.ex-db',
            null,
            null,
            null,
            $this->createChildJobResult(),
            ['count' => 3],
        );

        $arrayResult = $result->toArray();

        self::assertSame('task-456', $arrayResult['id']);
        self::assertSame('failed', $arrayResult['status']);
        self::assertNull($arrayResult['duration']);
        self::assertNull($arrayResult['config']);
        self::assertNull($arrayResult['startTime']);
        self::assertSame(['count' => 3], $arrayResult['retry']);
    }

    public function testToArrayOmitsRetryWhenNull(): void
    {
        $result = new JobTypeResult(
            'task-1',
            'phase-1',
            'Task',
            'success',
            'job-1',
            JobType::STANDARD,
            'keboola.ex-db',
            'config-1',
            null,
            100,
            $this->createChildJobResult(),
            null,
        );

        self::assertArrayNotHasKey('retry', $result->toArray());
    }

    public function testGetJobTypeForDifferentTypes(): void
    {
        foreach ([JobType::STANDARD, JobType::ROW_CONTAINER, JobType::RETRY_CONTAINER] as $jobType) {
            $result = new JobTypeResult(
                'task-1',
                'phase-1',
                'Task',
                'success',
                'job-1',
                $jobType,
                'keboola.ex-db',
                'config-1',
                null,
                100,
                $this->createChildJobResult(),
                null,
            );

            self::assertSame($jobType, $result->getJobType());
            self::assertSame('job', $result->getTaskType());
        }
    }
}
