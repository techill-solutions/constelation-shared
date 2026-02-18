<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Queue\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Gerencia a conexão com RabbitMQ.
 */
final class RabbitMQConnection
{
    private static ?AMQPStreamConnection $connection = null;
    private static ?AMQPChannel $channel = null;

    private function __construct()
    {
        // Singleton
    }

    /**
     * Obtém a conexão com RabbitMQ (singleton).
     */
    public static function getConnection(): AMQPStreamConnection
    {
        if (self::$connection === null || !self::$connection->isConnected()) {
            self::$connection = new AMQPStreamConnection(
                host: env('RABBITMQ_HOST', 'rabbitmq'),
                port: (int) env('RABBITMQ_PORT', 5672),
                user: env('RABBITMQ_USER', 'constelation'),
                password: env('RABBITMQ_PASSWORD', 'secret'),
                vhost: env('RABBITMQ_VHOST', '/'),
                insist: false,
                login_method: 'AMQPLAIN',
                login_response: null,
                locale: 'en_US',
                connection_timeout: 3.0,
                read_write_timeout: 3.0,
                context: null,
                keepalive: true,
                heartbeat: 30
            );
        }

        return self::$connection;
    }

    /**
     * Obtém um canal de comunicação com RabbitMQ.
     */
    public static function getChannel(): AMQPChannel
    {
        if (self::$channel === null || !self::$channel->is_open()) {
            self::$channel = self::getConnection()->channel();
        }

        return self::$channel;
    }

    /**
     * Fecha a conexão com RabbitMQ.
     */
    public static function close(): void
    {
        if (self::$channel !== null && self::$channel->is_open()) {
            self::$channel->close();
            self::$channel = null;
        }

        if (self::$connection !== null && self::$connection->isConnected()) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
