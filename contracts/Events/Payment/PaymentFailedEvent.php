<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events\Payment;

use Constelation\Shared\Contracts\Events\AbstractEvent;

/**
 * Evento emitido quando um pagamento falha.
 */
final class PaymentFailedEvent extends AbstractEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $chargeId,
        private readonly int $contractId,
        private readonly int $tenantId,
        private readonly float $amount,
        private readonly string $reason,
        private readonly ?int $paymentId = null,
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'payment.failed';
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
            'payment_id' => $this->paymentId,
            'charge_id' => $this->chargeId,
            'contract_id' => $this->contractId,
            'tenant_id' => $this->tenantId,
            'amount' => $this->amount,
            'reason' => $this->reason,
        ];
    }
}
