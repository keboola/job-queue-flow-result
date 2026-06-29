<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Condition\EvaluatedConditionResult;
use Keboola\JobQueue\FlowResult\Condition\FailedConditionResult;
use Keboola\JobQueue\FlowResult\PhaseResult;
use PHPUnit\Framework\TestCase;

class PhaseResultTest extends TestCase
{
    /**
     * @dataProvider phaseResultDataProvider
     */
    public function testGetters(
        string $id,
        string $name,
        string $status,
        ?DateTimeImmutable $startTime,
        ?int $duration,
        ?string $jobId,
    ): void {
        $phaseResult = new PhaseResult($id, $name, $status, $startTime, $duration, $jobId);

        self::assertSame($id, $phaseResult->getId());
        self::assertSame($name, $phaseResult->getName());
        self::assertSame($status, $phaseResult->getStatus());
        self::assertSame($startTime, $phaseResult->getStartTime());
        self::assertSame($duration, $phaseResult->getDuration());
        self::assertSame($jobId, $phaseResult->getJobId());
    }

    public static function phaseResultDataProvider(): iterable
    {
        yield 'with all fields' => [
            'id' => 'phase-123',
            'name' => 'First phase',
            'status' => 'success',
            'startTime' => new DateTimeImmutable('2026-06-24T10:00:00+00:00'),
            'duration' => 1500,
            'jobId' => 'phase-job-1',
        ];

        yield 'with null optional fields' => [
            'id' => 'phase-456',
            'name' => 'Second phase',
            'status' => 'error',
            'startTime' => null,
            'duration' => null,
            'jobId' => null,
        ];
    }

    public function testGetConditionsResultsReturnsEmptyArrayByDefault(): void
    {
        $phaseResult = new PhaseResult('phase-1', 'Phase 1', 'success', null, 1000);

        self::assertSame([], $phaseResult->getConditionsResults());
    }

    public function testAddConditionResult(): void
    {
        $phaseResult = new PhaseResult('phase-1', 'Phase 1', 'success', null, 1000);

        $conditionResult1 = new EvaluatedConditionResult(
            'condition-1',
            'Test Condition 1',
            true,
            'Match found',
            'phase-2',
        );
        $conditionResult2 = new FailedConditionResult(
            'condition-2',
            'Test Condition 2',
            'Task not found',
            'user_error',
            null,
        );

        $phaseResult->addConditionResult($conditionResult1);
        $phaseResult->addConditionResult($conditionResult2);

        $results = $phaseResult->getConditionsResults();

        self::assertCount(2, $results);
        self::assertSame($conditionResult1, $results[0]);
        self::assertSame($conditionResult2, $results[1]);
    }

    public function testConditionResultWithEvaluationReason(): void
    {
        $phaseResult = new PhaseResult('phase-1', 'Phase 1', 'success', null, 1000);

        $conditionResult = new EvaluatedConditionResult(
            'condition-1',
            'Test Condition',
            true,
            'Condition matched',
            'phase-2',
        );

        $phaseResult->addConditionResult($conditionResult);

        $results = $phaseResult->getConditionsResults();

        self::assertCount(1, $results);
        self::assertSame('condition-1', $results[0]->getId());
        self::assertTrue($results[0]->hasMatch());
        self::assertSame('Condition matched', $results[0]->getReason());
    }
}
