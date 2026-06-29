<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests;

use Keboola\JobQueue\FlowResult\CreatedPhaseResult;
use PHPUnit\Framework\TestCase;

class CreatedPhaseResultTest extends TestCase
{
    public function testToArray(): void
    {
        self::assertSame(
            [
                'id' => 'phase-1',
                'name' => 'First phase',
                'status' => 'created',
            ],
            (new CreatedPhaseResult('phase-1', 'First phase'))->toArray(),
        );
    }
}
