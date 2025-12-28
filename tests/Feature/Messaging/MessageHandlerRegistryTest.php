<?php

namespace Tests\Feature\Messaging;

use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;
use Modules\Shared\Services\MessageHandlerRegistry;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageHandlerRegistryTest extends TestCase
{
    private MessageHandlerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new MessageHandlerRegistry;
    }

    #[Test]
    public function it_registers_a_handler(): void
    {
        $handler = $this->createMockHandler('test.queue', 'test.message', [1]);

        $this->registry->register($handler);

        $this->assertTrue($this->registry->hasHandler('test.message'));
        $this->assertContains('test.message', $this->registry->registeredTypes());
    }

    #[Test]
    public function it_retrieves_a_registered_handler(): void
    {
        $handler = $this->createMockHandler('test.queue', 'user.create', [1, 2]);

        $this->registry->register($handler);

        $retrieved = $this->registry->getHandler('user.create');

        $this->assertSame($handler, $retrieved);
    }

    #[Test]
    public function it_throws_exception_for_duplicate_handler(): void
    {
        $handler1 = $this->createMockHandler('test.queue', 'duplicate.type', [1]);
        $handler2 = $this->createMockHandler('test.queue', 'duplicate.type', [1]);

        $this->registry->register($handler1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler already registered for message type: duplicate.type');

        $this->registry->register($handler2);
    }

    #[Test]
    public function it_throws_exception_for_unknown_handler(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler registered for message type: unknown.type');

        $this->registry->getHandler('unknown.type');
    }

    #[Test]
    public function it_returns_false_for_unregistered_type(): void
    {
        $this->assertFalse($this->registry->hasHandler('nonexistent.type'));
    }

    #[Test]
    public function it_lists_all_registered_types(): void
    {
        $this->registry->register($this->createMockHandler('queue.a', 'type.one', [1]));
        $this->registry->register($this->createMockHandler('queue.b', 'type.two', [1]));
        $this->registry->register($this->createMockHandler('queue.a', 'type.three', [1]));

        $types = $this->registry->registeredTypes();

        $this->assertCount(3, $types);
        $this->assertContains('type.one', $types);
        $this->assertContains('type.two', $types);
        $this->assertContains('type.three', $types);
    }

    #[Test]
    public function it_lists_unique_queues(): void
    {
        $this->registry->register($this->createMockHandler('queue.a', 'type.one', [1]));
        $this->registry->register($this->createMockHandler('queue.b', 'type.two', [1]));
        $this->registry->register($this->createMockHandler('queue.a', 'type.three', [1]));

        $queues = $this->registry->registeredQueues();

        $this->assertCount(2, $queues);
        $this->assertContains('queue.a', $queues);
        $this->assertContains('queue.b', $queues);
    }

    #[Test]
    public function it_returns_handlers_for_queue(): void
    {
        $this->registry->register($this->createMockHandler('queue.a', 'type.one', [1]));
        $this->registry->register($this->createMockHandler('queue.b', 'type.two', [1]));
        $this->registry->register($this->createMockHandler('queue.a', 'type.three', [1]));

        $handlersA = $this->registry->handlersForQueue('queue.a');
        $handlersB = $this->registry->handlersForQueue('queue.b');

        $this->assertCount(2, $handlersA);
        $this->assertCount(1, $handlersB);
    }

    #[Test]
    public function it_is_bound_in_container(): void
    {
        $registry = $this->app->make(MessageHandlerRegistryInterface::class);

        $this->assertInstanceOf(MessageHandlerRegistry::class, $registry);
    }

    private function createMockHandler(string $queue, string $type, array $versions): MessageHandlerInterface
    {
        $handler = $this->createMock(MessageHandlerInterface::class);
        $handler->method('queue')->willReturn($queue);
        $handler->method('handles')->willReturn($type);
        $handler->method('supportedVersions')->willReturn($versions);

        return $handler;
    }
}
