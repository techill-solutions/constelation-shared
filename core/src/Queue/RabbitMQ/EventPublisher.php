<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Queue\RabbitMQ;

use Constelation\Shared\Contracts\Events\EventInterface;
use Constelation\Shared\Contracts\Events\EventPublisherInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Illuminate\Support\Facades\Log;

/**
 * Implementação do Event Publisher usando RabbitMQ.
 *
 * Utiliza um exchange do tipo topic para permitir roteamento flexível.
 */
final class EventPublisher implements EventPublisherInterface
{
    private const EXCHANGE_NAME = 'constelation.events';
    private const EXCHANGE_TYPE = AMQPExchangeType::TOPIC;

    private bool $exchangeDeclared = false;

    public function __construct()
    {
        $this->ensureExchangeExists();
    }

    /**
     * Publica um evento no Event Bus.
     */
    public function publish(EventInterface $event, ?string $routingKey = null): void
    {
        try {
            $channel = RabbitMQConnection::getChannel();

            $routingKey = $routingKey ?? $event->getEventName();

            $message = new AMQPMessage(
                $event->toJson(),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'message_id' => $event->getEventId(),
                    'timestamp' => time(),
                    'app_id' => $event->getSourceService(),
                    'headers' => [
                        'event_name' => $event->getEventName(),
                        'company_id' => $event->getCompanyId(),
                        'source_service' => $event->getSourceService(),
                    ],
                ]
            );

            $channel->basic_publish(
                msg: $message,
                exchange: self::EXCHANGE_NAME,
                routing_key: $routingKey
            );

            Log::info('Event published', [
                'event_id' => $event->getEventId(),
                'event_name' => $event->getEventName(),
                'routing_key' => $routingKey,
                'company_id' => $event->getCompanyId(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to publish event', [
                'event_name' => $event->getEventName(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Publica múltiplos eventos em batch.
     */
    public function publishBatch(array $events): void
    {
        $channel = RabbitMQConnection::getChannel();

        foreach ($events as $event) {
            if (!$event instanceof EventInterface) {
                continue;
            }

            $message = new AMQPMessage(
                $event->toJson(),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'message_id' => $event->getEventId(),
                    'timestamp' => time(),
                    'app_id' => $event->getSourceService(),
                ]
            );

            $channel->batch_basic_publish(
                msg: $message,
                exchange: self::EXCHANGE_NAME,
                routing_key: $event->getEventName()
            );
        }

        $channel->publish_batch();

        Log::info('Batch events published', [
            'count' => count($events),
        ]);
    }

    /**
     * Garante que o exchange existe no RabbitMQ.
     */
    private function ensureExchangeExists(): void
    {
        if ($this->exchangeDeclared) {
            return;
        }

        $channel = RabbitMQConnection::getChannel();

        $channel->exchange_declare(
            exchange: self::EXCHANGE_NAME,
            type: self::EXCHANGE_TYPE,
            passive: false,
            durable: true,
            auto_delete: false
        );

        $this->exchangeDeclared = true;
    }
}
