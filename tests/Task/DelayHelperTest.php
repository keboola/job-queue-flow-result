<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests\Task;

use DateTimeImmutable;
use Generator;
use Keboola\JobQueue\FlowResult\Task\DelayHelper;
use Keboola\JobQueueInternalClient\JobFactory\JobInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DelayHelperTest extends TestCase
{
    /**
     * @dataProvider computeDelayFromTimestampsDataProvider
     */
    public function testComputeDelayFallsBackToTimestamps(
        ?DateTimeImmutable $createdTime,
        ?DateTimeImmutable $delayedStartTime,
        ?int $expectedResult,
    ): void {
        /** @var JobInterface&MockObject $job */
        $job = $this->createMock(JobInterface::class);

        // Jobs created before the delay was persisted report null here.
        $job->expects(self::once())
            ->method('getRequestedDelay')
            ->willReturn(null);

        // Call counts depend on which branch the fallback takes, so these are value stubs.
        $job->expects(self::any())
            ->method('getDelayedStartTime')
            ->willReturn($delayedStartTime);

        $job->expects(self::any())
            ->method('getCreatedTime')
            ->willReturn($createdTime);

        self::assertSame($expectedResult, DelayHelper::computeDelay($job));
    }

    public static function computeDelayFromTimestampsDataProvider(): Generator
    {
        yield 'valid times with 5 minutes delay' => [
            'createdTime' => new DateTimeImmutable('2023-01-01T12:00:00+00:00'),
            'delayedStartTime' => new DateTimeImmutable('2023-01-01T12:05:00+00:00'),
            'expectedResult' => 300,
        ];

        yield 'null delayed start time' => [
            'createdTime' => new DateTimeImmutable('2023-01-01T12:00:00+00:00'),
            'delayedStartTime' => null,
            'expectedResult' => null,
        ];

        yield 'null created time' => [
            'createdTime' => null,
            'delayedStartTime' => new DateTimeImmutable('2023-01-01T12:05:00+00:00'),
            'expectedResult' => null,
        ];

        yield 'both times null' => [
            'createdTime' => null,
            'delayedStartTime' => null,
            'expectedResult' => null,
        ];
    }

    /**
     * @dataProvider requestedDelayDataProvider
     */
    public function testComputeDelayPrefersRequestedDelay(int $requestedDelay): void
    {
        /** @var JobInterface&MockObject $job */
        $job = $this->createMock(JobInterface::class);

        $job->expects(self::once())
            ->method('getRequestedDelay')
            ->willReturn($requestedDelay);

        // The persisted value wins outright; the timestamps are never consulted, so the
        // reported delay no longer depends on two clocks agreeing.
        $job->expects(self::never())
            ->method('getDelayedStartTime');

        $job->expects(self::never())
            ->method('getCreatedTime');

        self::assertSame($requestedDelay, DelayHelper::computeDelay($job));
    }

    public static function requestedDelayDataProvider(): Generator
    {
        yield 'five minutes' => [300];

        // 0 is falsy but a legitimate persisted value and must not fall through to the timestamps.
        yield 'no delay requested' => [0];
    }
}
