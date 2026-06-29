<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Condition;

use Keboola\JobQueue\FlowResult\Condition\EvaluatedConditionResult;
use PHPUnit\Framework\TestCase;

class EvaluatedConditionResultTest extends TestCase
{
    public function testGetters(): void
    {
        // Test with complete data
        $result = new EvaluatedConditionResult(
            'condition-123',
            'Test Condition',
            true,
            'Condition matched successfully',
            'phase-2',
        );

        self::assertSame('condition-123', $result->getId());
        self::assertSame('Test Condition', $result->getName());
        self::assertTrue($result->hasMatch());
        self::assertSame('Condition matched successfully', $result->getReason());
        self::assertSame('phase-2', $result->getGoto());

        // Test with null name and null goto
        $result = new EvaluatedConditionResult(
            'condition-456',
            null,
            false,
            'Condition evaluation failed',
            null,
        );

        self::assertSame('condition-456', $result->getId());
        self::assertSame('', $result->getName());
        self::assertFalse($result->hasMatch());
        self::assertSame('Condition evaluation failed', $result->getReason());
        self::assertNull($result->getGoto());
    }

    public function testToArray(): void
    {
        // Test with complete data
        $result = new EvaluatedConditionResult(
            'condition-123',
            'Test Condition',
            true,
            'Condition matched successfully',
            'phase-2',
        );

        $expected = [
            'id' => 'condition-123',
            'name' => 'Test Condition',
            'goto' => 'phase-2',
            'match' => true,
            'reason' => 'Condition matched successfully',
        ];

        self::assertSame($expected, $result->toArray());

        // Test with null name and null goto
        $result = new EvaluatedConditionResult(
            'condition-456',
            null,
            false,
            'Condition evaluation failed',
            null,
        );

        $expected = [
            'id' => 'condition-456',
            'name' => null,
            'goto' => null,
            'match' => false,
            'reason' => 'Condition evaluation failed',
        ];

        self::assertSame($expected, $result->toArray());
    }
}
