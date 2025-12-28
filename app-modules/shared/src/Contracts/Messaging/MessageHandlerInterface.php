<?php

namespace Modules\Shared\Contracts\Messaging;

/**
 * Contract for handling incoming domain messages.
 *
 * Each message type should have its own handler implementation.
 * Handlers are registered in the MessageHandlerRegistry.
 */
interface MessageHandlerInterface
{
    /**
     * Get the queue name this handler consumes from.
     *
     * Used for auto-discovery when running `php artisan messaging:consume`.
     *
     * @return string The queue name (e.g., 'organization.user', 'central.sync')
     */
    public function queue(): string;

    /**
     * Get the message type this handler processes.
     *
     * @return string The message type (e.g., 'user.create', 'election.started')
     */
    public function handles(): string;

    /**
     * Get the supported message versions.
     *
     * @return array<int> List of supported versions
     */
    public function supportedVersions(): array;

    /**
     * Handle the incoming domain message.
     *
     * @throws \Exception if processing fails and message should be retried or dead-lettered
     */
    public function handle(DomainMessage $message): void;
}
