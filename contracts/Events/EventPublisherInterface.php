<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events;

/**
 * Interface para publicação de eventos no Event Bus.
 */
interface EventPublisherInterface
{
    /**
     * Publica um evento no Event Bus (RabbitMQ).
     *
     * @param EventInterface $event O evento a ser publicado
     * @param string|null $routingKey Chave de roteamento opcional (padrão: nome do evento)
     */
    public function publish(EventInterface $event, ?string $routingKey = null): void;

    /**
     * Publica múltiplos eventos em batch.
     *
     * @param array<EventInterface> $events Lista de eventos
     */
    public function publishBatch(array $events): void;
}
