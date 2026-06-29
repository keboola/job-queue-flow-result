<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests;

use Keboola\JobQueue\FlowResult\FlowResultDocument;
use Keboola\JobQueue\FlowResult\FlowResultEntries;
use PHPUnit\Framework\TestCase;

class FlowResultEntriesTest extends TestCase
{
    public function testIsEmptyByDefault(): void
    {
        self::assertTrue((new FlowResultEntries())->isEmpty());
    }

    public function testIsNotEmptyAfterCollectingAnyDelta(): void
    {
        $entries = new FlowResultEntries();
        $entries->addPhase(['id' => 'phase-1']);

        self::assertFalse($entries->isEmpty());
    }

    public function testApplyToMergesCollectedDeltasIntoDocument(): void
    {
        $entries = new FlowResultEntries();
        $entries->addTask(['id' => 'task-1', 'status' => 'created']);
        $entries->addPhase(['id' => 'phase-1', 'status' => 'processing']);
        $entries->setFlow(['status' => 'processing']);

        $document = $entries->applyTo(new FlowResultDocument([]));

        self::assertSame([
            'tasks' => [['id' => 'task-1', 'status' => 'created']],
            'phases' => [['id' => 'phase-1', 'status' => 'processing']],
            'flow' => ['status' => 'processing'],
        ], $document->jsonSerialize());
    }

    public function testApplyToReturnsTheSameDocumentInstance(): void
    {
        $entries = new FlowResultEntries();
        $document = new FlowResultDocument([]);

        self::assertSame($document, $entries->applyTo($document));
    }
}
