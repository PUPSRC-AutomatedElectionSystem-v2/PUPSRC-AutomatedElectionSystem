<?php

namespace Modules\Shared\Contracts\Messaging;

/**
 * Contract for consuming messages from a message broker.
 *
 * Implementations handle connection, subscription, and message acknowledgment.
 */
interface MessageConsumerInterface
{
    /**
     * Start consuming messages from the specified queue.
     *
     * This is a blocking call that runs until stopped.
     *
     * @param  string  $queue  The queue name to consume from
     * @param  callable(DomainMessage): void  $callback  Called for each message
     */
    public function consume(string $queue, callable $callback): void;

    /**
     * Start consuming messages from multiple queues.
     *
     * This is a blocking call that runs until stopped.
     *
     * @param  array<string>  $queues  The queue names to consume from
     * @param  callable(DomainMessage): void  $callback  Called for each message
     */
    public function consumeMultiple(array $queues, callable $callback): void;

    /**
     * Stop the consumer gracefully.
     */
    public function stop(): void;
}
