<?php

namespace Modules\Shared\Console\Commands;

use Illuminate\Console\Command;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;
use Modules\Shared\Services\MessageProcessor;

/**
 * Artisan command to consume messages from RabbitMQ.
 *
 * Usage:
 *   php artisan messaging:consume                    # Auto-discover all queues from handlers
 *   php artisan messaging:consume organization.user  # Consume specific queue
 */
class ConsumeMessagesCommand extends Command
{
    protected $signature = 'messaging:consume
                            {queue? : The queue name to consume from (optional, auto-discovers if omitted)}';

    protected $description = 'Consume and process messages from RabbitMQ queues';

    public function handle(
        MessageProcessor $processor,
        MessageHandlerRegistryInterface $registry,
    ): int {
        $queue = $this->argument('queue');

        // Auto-discover queues if not specified
        if (! $queue) {
            return $this->consumeAllQueues($processor, $registry);
        }

        return $this->consumeQueue($processor, $queue);
    }

    /**
     * Consume messages from all registered queues.
     */
    private function consumeAllQueues(
        MessageProcessor $processor,
        MessageHandlerRegistryInterface $registry,
    ): int {
        $queues = $registry->registeredQueues();

        if (empty($queues)) {
            $this->warn('No message handlers registered.');
            $this->line('Register handlers in your service provider first.');

            return self::SUCCESS;
        }

        $this->info('Auto-discovered queues from registered handlers:');
        foreach ($queues as $queue) {
            $handlers = $registry->handlersForQueue($queue);
            $types = array_map(fn ($h) => $h->handles(), $handlers);
            $this->line("  • {$queue} → ".implode(', ', $types));
        }
        $this->newLine();

        // For now, consume from first queue (single-threaded)
        // In production, use supervisor to run multiple instances
        if (count($queues) > 1) {
            $this->warn('Multiple queues detected. Consuming from all sequentially.');
            $this->line('For parallel processing, run separate consumers or use supervisor.');
            $this->newLine();
        }

        $this->info('Starting unified consumer...');
        $this->info('Press Ctrl+C to stop.');
        $this->newLine();

        $this->registerSignalHandlers($processor);

        try {
            // Consume from all queues (processor handles routing)
            $processor->processAll($queues);
        } catch (\Exception $e) {
            $this->error("Consumer error: {$e->getMessage()}");
            \Illuminate\Support\Facades\Log::error('Consumer crashed', [
                'queues' => $queues,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Consume messages from a specific queue.
     */
    private function consumeQueue(MessageProcessor $processor, string $queue): int
    {
        $this->info("Starting message consumer for queue: {$queue}");
        $this->info('Press Ctrl+C to stop.');
        $this->newLine();

        $this->registerSignalHandlers($processor);

        try {
            $processor->process($queue);
        } catch (\Exception $e) {
            $this->error("Consumer error: {$e->getMessage()}");
            \Illuminate\Support\Facades\Log::error('Consumer crashed', [
                'queue' => $queue,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function registerSignalHandlers(MessageProcessor $processor): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn () => $this->shutdown($processor));
            pcntl_signal(SIGINT, fn () => $this->shutdown($processor));
        }
    }

    private function shutdown(MessageProcessor $processor): void
    {
        $this->newLine();
        $this->info('Shutting down gracefully...');
        $processor->stop();
    }
}
