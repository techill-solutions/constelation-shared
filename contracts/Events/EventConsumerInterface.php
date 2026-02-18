<?php

declare(strict_types=1);

namespace Constelation\Shared\Contracts\Events;

/**
 * Interface para consumidores de eventos do Event Bus.
 */
interface EventConsumerInterface
{
    /**
     * Retorna os nomes dos eventos que este consumidor escuta.
     *
     * @return array<string>
     */
    public function getSubscribedEvents(): array;

    /**
     * Processa um evento recebido.
     *
     * @param array $eventData Dados do evento decodificados do JSON
     * @throws \Exception Se o processamento falhar
     */
    public function handle(array $eventData): void;
}
