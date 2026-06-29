<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Task\ChildJobResult;
use PHPUnit\Framework\TestCase;

class ChildJobResultTest extends TestCase
{
    public function testGetters(): void
    {
        // Test with complete data
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');
        $resultData = ['output' => 'test-output', 'metrics' => ['count' => 5]];

        $result = new ChildJobResult(
            'job-123',
            'success',
            $resultData,
            $startTime,
            300,
            30,
        );

        self::assertSame('job-123', $result->getJobId());
        self::assertSame('success', $result->getStatus());
        self::assertSame($startTime, $result->getStartTime());
        self::assertSame(300, $result->getDuration());
        self::assertSame(30, $result->getDelay());

        // Test with null optional values
        $result = new ChildJobResult(
            'job-456',
            'failed',
            [],
            null,
            null,
            null,
        );

        self::assertSame('job-456', $result->getJobId());
        self::assertSame('failed', $result->getStatus());
        self::assertNull($result->getStartTime());
        self::assertNull($result->getDuration());
        self::assertNull($result->getDelay());
    }

    public function testToArray(): void
    {
        // Test with complete data
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');
        $resultData = ['output' => 'test-output', 'metrics' => ['count' => 5]];

        $result = new ChildJobResult(
            'job-123',
            'success',
            $resultData,
            $startTime,
            300,
            30,
        );

        $expected = [
            'jobId' => 'job-123',
            'status' => 'success',
            'duration' => 300,
            'startTime' => '2023-01-01T12:00:00+00:00',
            'result' => $resultData,
            'delay' => 30,
        ];

        self::assertEquals($expected, $result->toArray());

        // Test with null values
        $result = new ChildJobResult(
            'job-456',
            'failed',
            [],
            null,
            null,
            null,
        );

        $expected = [
            'jobId' => 'job-456',
            'status' => 'failed',
            'duration' => null,
            'startTime' => null,
            'result' => [],
        ];

        self::assertEquals($expected, $result->toArray());
    }
}
