<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Task\VariableTypeResult;
use PHPUnit\Framework\TestCase;

class VariableTypeResultTest extends TestCase
{
    public function testGetters(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete data
        $result = new VariableTypeResult(
            'task-123',
            'phase-456',
            'Variable Task',
            'processed',
            ['variableValue' => 'test-value', 'type' => 'string'],
            $startTime,
            5,
        );

        self::assertSame('task-123', $result->getTaskId());
        self::assertSame('phase-456', $result->getPhaseId());
        self::assertSame('Variable Task', $result->getName());
        self::assertSame('processed', $result->getStatus());
        self::assertSame('variable', $result->getTaskType());
        self::assertSame($startTime, $result->getStartTime());
        self::assertSame(5, $result->getDuration());

        // Test with empty result data
        $result = new VariableTypeResult(
            'task-456',
            'phase-789',
            'Another Variable',
            'failed',
            [],
            $startTime,
            0,
        );

        self::assertSame('task-456', $result->getTaskId());
        self::assertSame('phase-789', $result->getPhaseId());
        self::assertSame('Another Variable', $result->getName());
        self::assertSame('failed', $result->getStatus());
        self::assertSame('variable', $result->getTaskType());
    }

    public function testGetResult(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete result data
        $resultData = [
            'variableValue' => 'test-value',
            'type' => 'string',
            'computed' => true,
        ];

        $result = new VariableTypeResult(
            'task-123',
            'phase-456',
            'Variable Task',
            'processed',
            $resultData,
            $startTime,
            5,
        );

        self::assertEquals($resultData, $result->getResult());

        // Test with empty result data
        $result = new VariableTypeResult(
            'task-456',
            'phase-789',
            'Another Variable',
            'failed',
            [],
            $startTime,
            0,
        );

        self::assertEquals([], $result->getResult());

        // Test with complex result data
        $complexResultData = [
            'variables' => [
                'var1' => 'value1',
                'var2' => 42,
                'var3' => ['nested' => 'array'],
            ],
            'metadata' => [
                'source' => 'configuration',
                'timestamp' => '2023-01-01T12:00:00+00:00',
            ],
        ];

        $result = new VariableTypeResult(
            'task-789',
            'phase-abc',
            'Complex Variable',
            'success',
            $complexResultData,
            $startTime,
            0,
        );

        self::assertEquals($complexResultData, $result->getResult());
    }

    public function testToArray(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete data
        $resultData = [
            'variableValue' => 'test-value',
            'type' => 'string',
            'computed' => true,
        ];

        $result = new VariableTypeResult(
            'task-123',
            'phase-456',
            'Variable Task',
            'processed',
            $resultData,
            $startTime,
            5,
        );

        $expected = [
            'id' => 'task-123',
            'name' => 'Variable Task',
            'type' => 'variable',
            'phase' => 'phase-456',
            'status' => 'processed',
            'results' => $resultData,
            'startTime' => '2023-01-01T12:00:00+00:00',
            'duration' => 5,
        ];

        self::assertEquals($expected, $result->toArray());

        // Test with empty result data and zero duration
        $result = new VariableTypeResult(
            'task-456',
            'phase-789',
            'Another Variable',
            'failed',
            [],
            $startTime,
            0,
        );

        $expected = [
            'id' => 'task-456',
            'name' => 'Another Variable',
            'type' => 'variable',
            'phase' => 'phase-789',
            'status' => 'failed',
            'results' => [],
            'startTime' => '2023-01-01T12:00:00+00:00',
            'duration' => 0,
        ];

        self::assertEquals($expected, $result->toArray());

        // Test with complex result data
        $complexResultData = [
            'variables' => [
                'var1' => 'value1',
                'var2' => 42,
            ],
            'metadata' => [
                'source' => 'configuration',
            ],
        ];

        $result = new VariableTypeResult(
            'task-789',
            'phase-abc',
            'Complex Variable',
            'success',
            $complexResultData,
            $startTime,
            0,
        );

        $expected = [
            'id' => 'task-789',
            'name' => 'Complex Variable',
            'type' => 'variable',
            'phase' => 'phase-abc',
            'status' => 'success',
            'results' => $complexResultData,
            'startTime' => '2023-01-01T12:00:00+00:00',
            'duration' => 0,
        ];

        self::assertEquals($expected, $result->toArray());
    }

    public function testVariableTypeValue(): void
    {
        $result = new VariableTypeResult(
            'task-variable',
            'phase-1',
            'Test Variable',
            'computed',
            ['value' => 'test'],
            new DateTimeImmutable('2023-01-01T12:00:00+00:00'),
            0,
        );

        $array = $result->toArray();
        self::assertSame('variable', $array['type']);
    }
}
