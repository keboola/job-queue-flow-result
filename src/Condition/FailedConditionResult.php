<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Condition;

readonly class FailedConditionResult extends AbstractConditionResult
{
    public function __construct(
        string $id,
        ?string $name,
        private string $message,
        private string $type,
        ?string $goto,
    ) {
        parent::__construct($id, $name, $goto);
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }

    public function getErrorType(): string
    {
        return $this->type;
    }

    public function getError(): array
    {
        return [
            'type' => $this->getErrorType(),
            'message' => $this->getErrorMessage(),
        ];
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'error' => $this->getError(),
            ],
        );
    }
}
