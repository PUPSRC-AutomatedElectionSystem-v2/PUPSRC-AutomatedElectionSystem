<?php

namespace Modules\Shared\Contracts\Messaging;

/**
 * Contract for publishing domain messages to a message broker.
 *
 * Implementations may use RabbitMQ, Redis, SQS, or any other transport.
 * This abstraction allows swapping transports without changing business logic.
 */
interface MessagePublisherInterface
{
    /**
     * Publish a domain message to the specified exchange with a routing key.
     */
    public function publish(DomainMessage $message, string $exchange, string $routingKey): void;

    /**
     * Publish a raw array payload (for legacy compatibility).
     *
     * @deprecated Use publish() with DomainMessage instead.
     */
    public function publishRaw(array $payload, string $exchange, string $routingKey): void;
}
