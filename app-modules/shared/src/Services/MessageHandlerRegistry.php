<?php

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;

/**
 * Registry for message handlers.
 *
 * Maintains a map of message types to their handlers.
 * Handlers are registered during application boot.
 */
class MessageHandlerRegistry implements MessageHandlerRegistryInterface
{
    /** @var array<string, MessageHandlerInterface> */
    private array $handlers = [];

    public function register(MessageHandlerInterface $handler): void
    {
        $type = $handler->handles();

        if (isset($this->handlers[$type])) {
            throw new \RuntimeException(
                sprintf('Handler already registered for message type: %s', $type)
            );
        }

        $this->handlers[$type] = $handler;
    }

    public function getHandler(string $messageType): MessageHandlerInterface
    {
        if (! isset($this->handlers[$messageType])) {
            throw new \RuntimeException(
                sprintf('No handler registered for message type: %s', $messageType)
            );
        }

        return $this->handlers[$messageType];
    }

    public function hasHandler(string $messageType): bool
    {
        return isset($this->handlers[$messageType]);
    }

    public function registeredTypes(): array
    {
        return array_keys($this->handlers);
    }

    public function registeredQueues(): array
    {
        $queues = [];

        foreach ($this->handlers as $handler) {
            $queue = $handler->queue();
            if (! in_array($queue, $queues, true)) {
                $queues[] = $queue;
            }
        }

        return $queues;
    }

    public function handlersForQueue(string $queue): array
    {
        return array_filter(
            $this->handlers,
            fn (MessageHandlerInterface $handler) => $handler->queue() === $queue
        );
    }
}
