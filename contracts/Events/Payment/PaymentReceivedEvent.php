<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events\Payment;

use Constelation\Shared\Contracts\Events\AbstractEvent;

/**
 * Evento emitido quando um pagamento é confirmado.
 */
final class PaymentReceivedEvent extends AbstractEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $paymentId,
        private readonly int $chargeId,
        private readonly int $contractId,
        private readonly int $tenantId,
        private readonly float $amount,
        private readonly string $paymentMethod,
        private readonly ?string $transactionId = null,
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'payment.received';
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
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
        ];
    }
}
