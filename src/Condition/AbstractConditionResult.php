<?php

declare(strict_types=1);

namespace Keboola\JobQueue\FlowResult\Condition;

abstract readonly class AbstractConditionResult
{
    public function __construct(
        protected string $id,
        protected ?string $name,
        protected ?string $goto,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function getGoto(): ?string
    {
        return $this->goto;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'goto' => $this->goto,
        ];
    }
}
