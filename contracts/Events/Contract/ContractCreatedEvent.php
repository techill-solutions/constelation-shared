<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events\Contract;

use Constelation\Shared\Contracts\Events\AbstractEvent;

/**
 * Evento emitido quando um contrato é criado.
 */
final class ContractCreatedEvent extends AbstractEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $contractId,
        private readonly int $propertyId,
        private readonly int $ownerId,
        private readonly int $tenantId,
        private readonly float $rentValue,
        private readonly string $startDate,
        private readonly string $endDate,
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'contract.created';
    }

    public function getSourceService(): string
    {
        return 'real-estate';
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function getPayload(): array
    {
        return [
            'contract_id' => $this->contractId,
            'property_id' => $this->propertyId,
            'owner_id' => $this->ownerId,
            'tenant_id' => $this->tenantId,
            'rent_value' => $this->rentValue,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
