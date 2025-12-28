<?php

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQ implementation of the message publisher.
 *
 * Publishes domain messages to RabbitMQ exchanges using topic routing.
 * Connection is lazy-loaded and reused for efficiency.
 */
class RabbitMqMessagePublisher implements MessagePublisherInterface
{
    private ?AMQPStreamConnection $connection = null;

    private ?AMQPChannel $channel = null;

    /** @var array<string, bool> Track declared exchanges to avoid redundant declarations */
    private array $declaredExchanges = [];

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $vhost,
    ) {}

    /**
     * Create instance from Laravel config.
     */
    public static function fromConfig(): self
    {
        return new self(
            host: config('queue.connections.rabbitmq.hosts.0.host', '127.0.0.1'),
            port: (int) config('queue.connections.rabbitmq.hosts.0.port', 5672),
            user: config('queue.connections.rabbitmq.hosts.0.user', 'guest'),
            password: config('queue.connections.rabbitmq.hosts.0.password', 'guest'),
            vhost: config('queue.connections.rabbitmq.hosts.0.vhost', '/'),
        );
    }

    public function publish(DomainMessage $message, string $exchange, string $routingKey): void
    {
        $this->ensureExchangeDeclared($exchange);

        $amqpMessage = new AMQPMessage(
            $message->toJson(),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json',
                'message_id' => $message->id,
                'timestamp' => strtotime($message->timestamp),
                'type' => $message->type,
            ]
        );

        $this->getChannel()->basic_publish($amqpMessage, $exchange, $routingKey);
    }

    public function publishRaw(array $payload, string $exchange, string $routingKey): void
    {
        $message = DomainMessage::fromArray($payload);
        $this->publish($message, $exchange, $routingKey);
    }

    /**
     * Get or create the AMQP channel.
     */
    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null || ! $this->channel->is_open()) {
            $this->channel = $this->getConnection()->channel();
        }

        return $this->channel;
    }

    /**
     * Get or create the AMQP connection.
     */
    private function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || ! $this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }

        return $this->connection;
    }

    /**
     * Ensure the exchange is declared (idempotent, only declares once per instance).
     */
    private function ensureExchangeDeclared(string $exchange): void
    {
        if (isset($this->declaredExchanges[$exchange])) {
            return;
        }

        $this->getChannel()->exchange_declare(
            exchange: $exchange,
            type: 'topic',
            passive: false,
            durable: true,
            auto_delete: false
        );

        $this->declaredExchanges[$exchange] = true;
    }

    /**
     * Close connections gracefully.
     */
    public function disconnect(): void
    {
        if ($this->channel?->is_open()) {
            $this->channel->close();
        }

        if ($this->connection?->isConnected()) {
            $this->connection->close();
        }

        $this->channel = null;
        $this->connection = null;
        $this->declaredExchanges = [];
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
