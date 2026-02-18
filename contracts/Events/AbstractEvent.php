<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events;

use Ramsey\Uuid\Uuid;

/**
 * Classe base abstrata para todos os eventos do sistema.
 *
 * Fornece implementação padrão para os métodos comuns da EventInterface.
 */
abstract class AbstractEvent implements EventInterface
{
    protected readonly string $eventId;
    protected readonly string $timestamp;

    public function __construct()
    {
        $this->eventId = Uuid::uuid4()->toString();
        $this->timestamp = date('c');
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * Serializa o evento para publicação no Event Bus.
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->getEventId(),
            'event_name' => $this->getEventName(),
            'source_service' => $this->getSourceService(),
            'company_id' => $this->getCompanyId(),
            'timestamp' => $this->getTimestamp(),
            'payload' => $this->getPayload(),
        ];
    }

    /**
     * Serializa o evento para JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
