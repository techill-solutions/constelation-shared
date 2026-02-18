<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events\Payment;

use Constelation\Shared\Contracts\Events\AbstractEvent;

/**
 * Evento emitido quando uma cobrança fica vencida.
 */
final class ChargeOverdueEvent extends AbstractEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $chargeId,
        private readonly int $contractId,
        private readonly int $tenantId,
        private readonly float $amount,
        private readonly string $dueDate,
        private readonly int $daysOverdue,
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'charge.overdue';
    }

    public function getSourceService(): string
    {
        return 'payments';
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function getPayload(): array
    {
        return [
            'charge_id' => $this->chargeId,
            'contract_id' => $this->contractId,
            'tenant_id' => $this->tenantId,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'days_overdue' => $this->daysOverdue,
        ];
    }
}
