<?php

declare(strict_types=1);

namespace Constelation\Shared\Events;

use Constelation\Shared\Contracts\Events\AbstractEvent;

abstract class BaseEvent extends AbstractEvent
{
    public function __construct(
        protected readonly int $companyId,
        protected readonly array $payload = [],
        protected readonly string $sourceService = 'unknown'
    ) {
        parent::__construct();
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function getSourceService(): string
    {
        return $this->sourceService;
    }
}
