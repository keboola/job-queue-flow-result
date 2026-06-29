<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use DateTimeImmutable;
use Keboola\JobQueue\FlowResult\Task\NotificationTypeResult;
use PHPUnit\Framework\TestCase;

class NotificationTypeResultTest extends TestCase
{
    public function testGetters(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete data
        $result = new NotificationTypeResult(
            'task-123',
            'phase-456',
            'Email Notification',
            'sent',
            ['message' => 'Email sent successfully', 'recipients' => ['user@example.com']],
            $startTime,
            3,
        );

        self::assertSame('task-123', $result->getTaskId());
        self::assertSame('phase-456', $result->getPhaseId());
        self::assertSame('Email Notification', $result->getName());
        self::assertSame('sent', $result->getStatus());
        self::assertSame('notification', $result->getTaskType());
        self::assertSame($startTime, $result->getStartTime());
        self::assertSame(3, $result->getDuration());

        // Test with empty result data
        $result = new NotificationTypeResult(
            'task-456',
            'phase-789',
            'SMS Notification',
            'failed',
            [],
            $startTime,
            0,
        );

        self::assertSame('task-456', $result->getTaskId());
        self::assertSame('phase-789', $result->getPhaseId());
        self::assertSame('SMS Notification', $result->getName());
        self::assertSame('failed', $result->getStatus());
        self::assertSame('notification', $result->getTaskType());
    }

    public function testGetResult(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete result data
        $resultData = [
            'message' => 'Email sent successfully',
            'recipients' => ['user@example.com'],
            'deliveryTime' => '2023-01-01T12:00:00+00:00',
        ];

        $result = new NotificationTypeResult(
            'task-123',
            'phase-456',
            'Email Notification',
            'sent',
            $resultData,
            $startTime,
            3,
        );

        self::assertEquals($resultData, $result->getResult());

        // Test with empty result data
        $result = new NotificationTypeResult(
            'task-456',
            'phase-789',
            'SMS Notification',
            'failed',
            [],
            $startTime,
            0,
        );

        self::assertEquals([], $result->getResult());
    }

    public function testToArray(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01T12:00:00+00:00');

        // Test with complete data
        $resultData = [
            'message' => 'Email sent successfully',
            'recipients' => ['user@example.com'],
            'deliveryTime' => '2023-01-01T12:00:00+00:00',
        ];

        $result = new NotificationTypeResult(
            'task-123',
            'phase-456',
            'Email Notification',
            'sent',
            $resultData,
            $startTime,
            3,
        );

        $expected = [
            'id' => 'task-123',
            'name' => 'Email Notification',
            'type' => 'notification',
            'phase' => 'phase-456',
            'status' => 'sent',
            'results' => $resultData,
            'startTime' => '2023-01-01T12:00:00+00:00',
            'duration' => 3,
        ];

        self::assertEquals($expected, $result->toArray());

        // Test with empty result data and zero duration
        $result = new NotificationTypeResult(
            'task-456',
            'phase-789',
            'SMS Notification',
            'failed',
            [],
            $startTime,
            0,
        );

        $expected = [
            'id' => 'task-456',
            'name' => 'SMS Notification',
            'type' => 'notification',
            'phase' => 'phase-789',
            'status' => 'failed',
            'results' => [],
            'startTime' => '2023-01-01T12:00:00+00:00',
            'duration' => 0,
        ];

        self::assertEquals($expected, $result->toArray());
    }

    public function testNotificationTypeValue(): void
    {
        $result = new NotificationTypeResult(
            'task-notification',
            'phase-1',
            'Test Notification',
            'delivered',
            ['notificationId' => 'notif-123'],
            new DateTimeImmutable('2023-01-01T12:00:00+00:00'),
            0,
        );

        $array = $result->toArray();
        self::assertSame('notification', $array['type']);
    }
}
