<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Tests;

use Keboola\JobQueue\FlowResult\FlowResultDocument;
use PHPUnit\Framework\TestCase;

class FlowResultDocumentTest extends TestCase
{
    public function testAddTaskMergesDeltasById(): void
    {
        $doc = new FlowResultDocument([]);
        // created delta, then processing delta, then terminal delta - each carries only what it knows
        $doc->addTask(['id' => 't1', 'name' => 'A', 'jobId' => 'j1', 'status' => 'created']);
        $doc->addTask(['id' => 't1', 'status' => 'processing', 'startTime' => '2024-01-01T10:00:00+00:00']);
        $doc->addTask(['id' => 't1', 'status' => 'success', 'duration' => 42]);

        /** @var list<array<string, mixed>> $tasks */
        $tasks = $doc->jsonSerialize()['tasks'];
        self::assertCount(1, $tasks);
        self::assertSame([
            'id' => 't1',
            'name' => 'A',
            'jobId' => 'j1',
            'status' => 'success',
            'startTime' => '2024-01-01T10:00:00+00:00',
            'duration' => 42,
        ], $tasks[0]);
    }

    public function testAddPhaseMergesDeltasById(): void
    {
        $doc = new FlowResultDocument([]);
        $doc->addPhase(['id' => 'p1', 'name' => 'P', 'status' => 'created']);
        $doc->addPhase(['id' => 'p1', 'status' => 'processing', 'startTime' => '2024-01-01T10:00:00+00:00']);
        $doc->addPhase(['id' => 'p1', 'status' => 'success', 'duration' => 7]);

        /** @var list<array<string, mixed>> $phases */
        $phases = $doc->jsonSerialize()['phases'];
        self::assertCount(1, $phases);
        self::assertSame([
            'id' => 'p1',
            'name' => 'P',
            'status' => 'success',
            'startTime' => '2024-01-01T10:00:00+00:00',
            'duration' => 7,
        ], $phases[0]);
    }

    public function testForeignKeysAndEntriesPreserved(): void
    {
        $doc = new FlowResultDocument([
            'tasks' => [['id' => 'foreign', 'status' => 'success']],
            'flow' => ['jobId' => 'f1'],
            'unknownTopLevel' => 'keepme',
        ]);
        $doc->addTask(['id' => 't1', 'status' => 'created']);

        $result = $doc->jsonSerialize();
        /** @var list<array<string, mixed>> $tasks */
        $tasks = $result['tasks'];
        self::assertCount(2, $tasks);
        self::assertSame('keepme', $result['unknownTopLevel']);
        /** @var array<string, mixed> $flow */
        $flow = $result['flow'];
        self::assertSame('f1', $flow['jobId']);
    }

    public function testUpdateChildResultInsertsThenReplacesByJobId(): void
    {
        $doc = new FlowResultDocument([
            'tasks' => [['id' => 't1', 'status' => 'processing', 'results' => []]],
        ]);
        $doc->updateChildResult('t1', 'child-1', ['jobId' => 'child-1', 'status' => 'created']);
        $doc->updateChildResult('t1', 'child-1', ['jobId' => 'child-1', 'status' => 'processing']);

        /** @var list<array<string, mixed>> $tasks */
        $tasks = $doc->jsonSerialize()['tasks'];
        /** @var list<array<string, mixed>> $results */
        $results = $tasks[0]['results'];
        self::assertCount(1, $results);
        self::assertSame('processing', $results[0]['status']);
    }

    public function testUpdateChildResultNoOpWhenTaskAbsent(): void
    {
        $doc = new FlowResultDocument(['tasks' => []]);
        $doc->updateChildResult('missing', 'child-1', ['jobId' => 'child-1', 'status' => 'created']);

        self::assertSame([], $doc->jsonSerialize()['tasks']);
    }

    public function testStatusGettersReturnStatusOrNullWhenAbsent(): void
    {
        $doc = new FlowResultDocument([
            'tasks' => [[
                'id' => 't1',
                'status' => 'processing',
                'results' => [['jobId' => 'child-1', 'status' => 'created']],
            ]],
            'phases' => [['id' => 'p1', 'status' => 'success']],
        ]);

        self::assertSame('processing', $doc->getTaskStatus('t1'));
        self::assertNull($doc->getTaskStatus('missing'));

        self::assertSame('success', $doc->getPhaseStatus('p1'));
        self::assertNull($doc->getPhaseStatus('missing'));

        self::assertSame('created', $doc->getChildStatus('t1', 'child-1'));
        self::assertNull($doc->getChildStatus('t1', 'missing-child'));
        self::assertNull($doc->getChildStatus('missing-task', 'child-1'));
    }
}
