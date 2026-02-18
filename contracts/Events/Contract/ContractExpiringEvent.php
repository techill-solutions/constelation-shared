<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events\Contract;

use Constelation\Shared\Contracts\Events\AbstractEvent;

/**
 * Evento emitido quando um contrato está próximo de expirar.
 */
final class ContractExpiringEvent extends AbstractEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $contractId,
        private readonly int $tenantId,
        private readonly int $ownerId,
        private readonly string $endDate,
        private readonly int $daysUntilExpiration,
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'contract.expiring';
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
            'tenant_id' => $this->tenantId,
            'owner_id' => $this->ownerId,
            'end_date' => $this->endDate,
            'days_until_expiration' => $this->daysUntilExpiration,
        ];
    }
}
