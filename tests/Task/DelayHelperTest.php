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
     * @dataProvider computeDelayDataProvider
     */
    public function testComputeDelay(
        ?DateTimeImmutable $createdTime,
        ?DateTimeImmutable $delayedStartTime,
        ?int $expectedResult,
    ): void {
        /** @var JobInterface&MockObject $job */
        $job = $this->createMock(JobInterface::class);

        $job->method('getDelayedStartTime')
            ->willReturn($delayedStartTime);

        $job->method('getCreatedTime')
            ->willReturn($createdTime);

        self::assertSame($expectedResult, DelayHelper::computeDelay($job));
    }

    public static function computeDelayDataProvider(): Generator
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
}
