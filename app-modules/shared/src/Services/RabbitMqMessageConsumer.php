<?php

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageConsumerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQ implementation of the message consumer.
 *
 * Subscribes to a queue and processes messages using a callback.
 * Handles connection management, acknowledgments, and graceful shutdown.
 */
class RabbitMqMessageConsumer implements MessageConsumerInterface
{
    private ?AMQPStreamConnection $connection = null;

    private ?AMQPChannel $channel = null;

    private bool $shouldStop = false;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $vhost,
        private readonly int $prefetchCount = 1,
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
            prefetchCount: (int) config('queue.connections.rabbitmq.options.prefetch_count', 1),
        );
    }

    public function consume(string $queue, callable $callback): void
    {
        $this->consumeMultiple([$queue], $callback);
    }

    public function consumeMultiple(array $queues, callable $callback): void
    {
        $channel = $this->getChannel();

        // Set QoS - process one message at a time
        $channel->basic_qos(
            prefetch_size: 0,
            prefetch_count: $this->prefetchCount,
            a_global: false
        );

        // Declare and subscribe to all queues
        foreach ($queues as $queue) {
            $channel->queue_declare(
                queue: $queue,
                passive: false,
                durable: true,
                exclusive: false,
                auto_delete: false
            );

            $channel->basic_consume(
                queue: $queue,
                consumer_tag: "consumer-{$queue}",
                no_local: false,
                no_ack: false,
                exclusive: false,
                nowait: false,
                callback: function (AMQPMessage $amqpMessage) use ($callback, $channel) {
                    $this->processMessage($amqpMessage, $callback, $channel);
                }
            );
        }

        // Consume loop
        while ($channel->is_consuming() && ! $this->shouldStop) {
            $channel->wait(null, false, 1); // 1 second timeout for graceful shutdown
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * Process a single message.
     */
    private function processMessage(AMQPMessage $amqpMessage, callable $callback, AMQPChannel $channel): void
    {
        try {
            $payload = json_decode($amqpMessage->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $domainMessage = DomainMessage::fromArray($payload);

            $callback($domainMessage);

            // Acknowledge successful processing
            $channel->basic_ack($amqpMessage->getDeliveryTag());
        } catch (\JsonException $e) {
            // Invalid JSON - reject without requeue (send to DLX if configured)
            \Illuminate\Support\Facades\Log::error('Invalid JSON in message', [
                'error' => $e->getMessage(),
                'body' => $amqpMessage->getBody(),
            ]);
            $channel->basic_nack($amqpMessage->getDeliveryTag(), false, false);
        } catch (\InvalidArgumentException $e) {
            // Invalid message structure - reject without requeue
            \Illuminate\Support\Facades\Log::error('Invalid message structure', [
                'error' => $e->getMessage(),
                'body' => $amqpMessage->getBody(),
            ]);
            $channel->basic_nack($amqpMessage->getDeliveryTag(), false, false);
        } catch (\Exception $e) {
            // Processing error - requeue for retry
            \Illuminate\Support\Facades\Log::error('Message processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $channel->basic_nack($amqpMessage->getDeliveryTag(), false, true);
        }
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
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
