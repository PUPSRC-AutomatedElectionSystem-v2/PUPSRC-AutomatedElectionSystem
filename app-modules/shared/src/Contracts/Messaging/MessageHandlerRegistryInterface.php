<?php

namespace Modules\Shared\Contracts\Messaging;

/**
 * Registry for message handlers.
 *
 * Routes incoming messages to the appropriate handler based on message type.
 */
interface MessageHandlerRegistryInterface
{
    /**
     * Register a handler for a message type.
     */
    public function register(MessageHandlerInterface $handler): void;

    /**
     * Get the handler for a given message type.
     *
     * @throws \RuntimeException if no handler is registered for the type
     */
    public function getHandler(string $messageType): MessageHandlerInterface;

    /**
     * Check if a handler exists for the given message type.
     */
    public function hasHandler(string $messageType): bool;

    /**
     * Get all registered message types.
     *
     * @return array<string>
     */
    public function registeredTypes(): array;

    /**
     * Get all unique queue names from registered handlers.
     *
     * @return array<string>
     */
    public function registeredQueues(): array;

    /**
     * Get all handlers for a specific queue.
     *
     * @return array<MessageHandlerInterface>
     */
    public function handlersForQueue(string $queue): array;
}
