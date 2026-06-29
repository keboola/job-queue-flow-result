<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Condition;

readonly class EvaluatedConditionResult extends AbstractConditionResult
{
    public function __construct(
        string $id,
        ?string $name,
        private bool $match,
        private string $reason,
        ?string $goto,
    ) {
        parent::__construct($id, $name, $goto);
    }

    public function hasMatch(): bool
    {
        return $this->match;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'match' => $this->hasMatch(),
                'reason' => $this->getReason(),
            ],
        );
    }
}
