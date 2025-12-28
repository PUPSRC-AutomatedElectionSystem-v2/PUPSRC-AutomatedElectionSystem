<?php

namespace Modules\Shared\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Set up RabbitMQ exchanges, queues, and bindings for messaging.
 *
 * Run this once to initialize the messaging infrastructure.
 */
class SetupMessagingCommand extends Command
{
    protected $signature = 'messaging:setup
                            {--exchange=organization : The exchange name}
                            {--queue=organization.user : The queue name}
                            {--routing-key=user.* : The routing key pattern}';

    protected $description = 'Set up RabbitMQ exchanges, queues, and bindings';

    public function handle(): int
    {
        $exchange = $this->option('exchange');
        $queue = $this->option('queue');
        $routingKey = $this->option('routing-key');

        $this->info('Setting up messaging infrastructure...');
        $this->newLine();

        try {
            $connection = new AMQPStreamConnection(
                config('queue.connections.rabbitmq.hosts.0.host', '127.0.0.1'),
                (int) config('queue.connections.rabbitmq.hosts.0.port', 5672),
                config('queue.connections.rabbitmq.hosts.0.user', 'guest'),
                config('queue.connections.rabbitmq.hosts.0.password', 'guest'),
                config('queue.connections.rabbitmq.hosts.0.vhost', '/'),
            );

            $channel = $connection->channel();

            // Declare the exchange
            $channel->exchange_declare(
                exchange: $exchange,
                type: 'topic',
                passive: false,
                durable: true,
                auto_delete: false
            );
            $this->info("✓ Exchange '{$exchange}' declared (topic, durable)");

            // Declare the queue
            $channel->queue_declare(
                queue: $queue,
                passive: false,
                durable: true,
                exclusive: false,
                auto_delete: false,
                nowait: false,
                arguments: [
                    // Optional: set up dead letter exchange
                    // 'x-dead-letter-exchange' => ['S', "{$exchange}.dlx"],
                ]
            );
            $this->info("✓ Queue '{$queue}' declared (durable)");

            // Bind the queue to the exchange
            $channel->queue_bind(
                queue: $queue,
                exchange: $exchange,
                routing_key: $routingKey
            );
            $this->info("✓ Queue bound to exchange with routing key '{$routingKey}'");

            $channel->close();
            $connection->close();

            $this->newLine();
            $this->info('Messaging infrastructure ready!');
            $this->newLine();
            $this->line('To start consuming messages:');
            $this->line("  php artisan messaging:consume {$queue}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to set up messaging: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
