<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Condition;

use Keboola\JobQueue\FlowResult\Condition\FailedConditionResult;
use PHPUnit\Framework\TestCase;

class FailedConditionResultTest extends TestCase
{
    public function testGetters(): void
    {
        // Test with complete data
        $result = new FailedConditionResult(
            'condition-123',
            'Test Condition',
            'Condition evaluation failed',
            'user_error',
            'phase-2',
        );

        self::assertSame('condition-123', $result->getId());
        self::assertSame('Test Condition', $result->getName());
        self::assertSame('Condition evaluation failed', $result->getErrorMessage());
        self::assertSame('user_error', $result->getErrorType());
        self::assertSame('phase-2', $result->getGoto());

        // Test with null name and null goto
        $result = new FailedConditionResult(
            'condition-456',
            null,
            'Another error message',
            'application_error',
            null,
        );

        self::assertSame('condition-456', $result->getId());
        self::assertSame('', $result->getName());
        self::assertSame('Another error message', $result->getErrorMessage());
        self::assertSame('application_error', $result->getErrorType());
        self::assertNull($result->getGoto());
    }

    public function testGetError(): void
    {
        // Test with user error
        $result = new FailedConditionResult(
            'condition-123',
            'Test Condition',
            'User error occurred',
            'user_error',
            'phase-2',
        );

        $expected = [
            'type' => 'user_error',
            'message' => 'User error occurred',
        ];

        self::assertEquals($expected, $result->getError());

        // Test with application error
        $result = new FailedConditionResult(
            'condition-456',
            'Another Condition',
            'Application error occurred',
            'application_error',
            null,
        );

        $expected = [
            'type' => 'application_error',
            'message' => 'Application error occurred',
        ];

        self::assertEquals($expected, $result->getError());
    }

    public function testToArray(): void
    {
        // Test with complete data
        $result = new FailedConditionResult(
            'condition-123',
            'Test Condition',
            'Condition evaluation failed',
            'user_error',
            'phase-2',
        );

        $expected = [
            'id' => 'condition-123',
            'name' => 'Test Condition',
            'goto' => 'phase-2',
            'error' => [
                'type' => 'user_error',
                'message' => 'Condition evaluation failed',
            ],
        ];

        self::assertEquals($expected, $result->toArray());

        // Test with null name and null goto
        $result = new FailedConditionResult(
            'condition-456',
            null,
            'Another error message',
            'application_error',
            null,
        );

        $expected = [
            'id' => 'condition-456',
            'name' => null,
            'goto' => null,
            'error' => [
                'type' => 'application_error',
                'message' => 'Another error message',
            ],
        ];

        self::assertEquals($expected, $result->toArray());
    }
}
