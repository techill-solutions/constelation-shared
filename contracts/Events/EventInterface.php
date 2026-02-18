<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events;

/**
 * Interface para todos os eventos do sistema Constelation.
 *
 * Todos os eventos devem implementar esta interface para garantir
 * consistência na comunicação entre microserviços.
 */
interface EventInterface
{
    /**
     * Retorna o nome único do evento.
     *
     * @example "payment.received", "contract.created", "notification.sent"
     */
    public function getEventName(): string;

    /**
     * Retorna o payload do evento como array.
     * Este payload será serializado para JSON quando publicado no Event Bus.
     */
    public function getPayload(): array;

    /**
     * Retorna o timestamp de quando o evento foi criado.
     */
    public function getTimestamp(): string;

    /**
     * Retorna um ID único para rastreamento do evento.
     */
    public function getEventId(): string;

    /**
     * Retorna o ID da empresa (tenant) relacionada ao evento.
     */
    public function getCompanyId(): int;

    /**
     * Retorna o serviço de origem do evento.
     *
     * @example "payments", "real-estate", "notifications"
     */
    public function getSourceService(): string;
}
