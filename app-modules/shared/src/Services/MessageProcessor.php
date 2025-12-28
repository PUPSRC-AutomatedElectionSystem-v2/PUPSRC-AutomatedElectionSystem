<?php

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageConsumerInterface;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;

/**
 * Orchestrates message consumption and handler dispatch.
 *
 * Acts as the entry point for processing messages from the broker.
 * Routes messages to appropriate handlers based on message type.
 */
class MessageProcessor
{
    public function __construct(
        private readonly MessageConsumerInterface $consumer,
        private readonly MessageHandlerRegistryInterface $registry,
    ) {}

    /**
     * Start processing messages from the specified queue.
     */
    public function process(string $queue): void
    {
        $this->consumer->consume($queue, function (DomainMessage $message) {
            $this->handleMessage($message);
        });
    }

    /**
     * Process messages from multiple queues.
     *
     * Creates a multi-queue consumer that listens to all specified queues.
     *
     * @param  array<string>  $queues
     */
    public function processAll(array $queues): void
    {
        $this->consumer->consumeMultiple($queues, function (DomainMessage $message) {
            $this->handleMessage($message);
        });
    }

    /**
     * Stop the processor gracefully.
     */
    public function stop(): void
    {
        $this->consumer->stop();
    }

    /**
     * Handle a single message by routing to the appropriate handler.
     */
    private function handleMessage(DomainMessage $message): void
    {
        if (! $this->registry->hasHandler($message->type)) {
            \Illuminate\Support\Facades\Log::warning('No handler for message type', [
                'type' => $message->type,
                'id' => $message->id,
            ]);

            return;
        }

        $handler = $this->registry->getHandler($message->type);

        // Version check
        if (! in_array($message->version, $handler->supportedVersions(), true)) {
            \Illuminate\Support\Facades\Log::warning('Unsupported message version', [
                'type' => $message->type,
                'version' => $message->version,
                'supported' => $handler->supportedVersions(),
            ]);

            return;
        }

        $handler->handle($message);
    }
}
