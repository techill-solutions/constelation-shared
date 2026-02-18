<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Queue\RabbitMQ;

use Constelation\Shared\Contracts\Events\EventConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Illuminate\Support\Facades\Log;

/**
 * Consumidor de eventos do RabbitMQ.
 *
 * Cada serviço deve criar sua própria fila e vincular aos eventos que deseja escutar.
 */
final class EventConsumer
{
    private const EXCHANGE_NAME = 'constelation.events';

    /**
     * @var array<string, EventConsumerInterface>
     */
    private array $handlers = [];

    private string $queueName;

    public function __construct(string $serviceName)
    {
        $this->queueName = "constelation.{$serviceName}.events";
        $this->setupQueue();
    }

    /**
     * Registra um handler para processar eventos.
     */
    public function registerHandler(EventConsumerInterface $handler): void
    {
        foreach ($handler->getSubscribedEvents() as $eventName) {
            $this->handlers[$eventName] = $handler;
            $this->bindQueue($eventName);

            Log::info('Event handler registered', [
                'event' => $eventName,
                'handler' => get_class($handler),
            ]);
        }
    }

    /**
     * Inicia o consumo de eventos.
     */
    public function consume(): void
    {
        $channel = RabbitMQConnection::getChannel();

        $channel->basic_qos(
            prefetch_size: 0,
            prefetch_count: 10,
            a_global: false
        );

        $channel->basic_consume(
            queue: $this->queueName,
            consumer_tag: '',
            no_local: false,
            no_ack: false,
            exclusive: false,
            nowait: false,
            callback: [$this, 'handleMessage']
        );

        Log::info('Event consumer started', [
            'queue' => $this->queueName,
            'handlers' => array_keys($this->handlers),
        ]);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * Processa uma mensagem recebida.
     */
    public function handleMessage(AMQPMessage $message): void
    {
        $eventData = [];

        try {
            $eventData = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $eventName = $eventData['event_name'] ?? 'unknown';

            Log::info('Event received', [
                'event_id' => $eventData['event_id'] ?? 'unknown',
                'event_name' => $eventName,
            ]);

            if (isset($this->handlers[$eventName])) {
                $this->handlers[$eventName]->handle($eventData);

                Log::info('Event processed successfully', [
                    'event_id' => $eventData['event_id'] ?? 'unknown',
                    'event_name' => $eventName,
                ]);
            } else {
                Log::warning('No handler for event', [
                    'event_name' => $eventName,
                ]);
            }

            // Acknowledge the message
            $message->ack();
        } catch (\Exception $e) {
            Log::error('Failed to process event', [
                'event_id' => $eventData['event_id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Requeue the message for retry
            $message->nack(requeue: true);
        }
    }

    /**
     * Configura a fila do serviço.
     */
    private function setupQueue(): void
    {
        $channel = RabbitMQConnection::getChannel();

        // Declare the exchange
        $channel->exchange_declare(
            exchange: self::EXCHANGE_NAME,
            type: AMQPExchangeType::TOPIC,
            passive: false,
            durable: true,
            auto_delete: false
        );

        // Declare the queue with dead letter exchange
        $channel->queue_declare(
            queue: $this->queueName,
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false,
            nowait: false,
            arguments: [
                'x-dead-letter-exchange' => ['S', self::EXCHANGE_NAME . '.dlx'],
                'x-dead-letter-routing-key' => ['S', $this->queueName . '.dead'],
            ]
        );

        // Setup dead letter queue
        $channel->exchange_declare(
            exchange: self::EXCHANGE_NAME . '.dlx',
            type: AMQPExchangeType::DIRECT,
            passive: false,
            durable: true,
            auto_delete: false
        );

        $channel->queue_declare(
            queue: $this->queueName . '.dead',
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false
        );

        $channel->queue_bind(
            queue: $this->queueName . '.dead',
            exchange: self::EXCHANGE_NAME . '.dlx',
            routing_key: $this->queueName . '.dead'
        );
    }

    /**
     * Vincula a fila a um padrão de roteamento de evento.
     */
    private function bindQueue(string $eventPattern): void
    {
        $channel = RabbitMQConnection::getChannel();

        $channel->queue_bind(
            queue: $this->queueName,
            exchange: self::EXCHANGE_NAME,
            routing_key: $eventPattern
        );
    }
}
