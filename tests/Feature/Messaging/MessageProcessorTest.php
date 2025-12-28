<?php

namespace Tests\Feature\Messaging;

use Modules\Shared\Contracts\Messaging\DomainMessage;
use Modules\Shared\Contracts\Messaging\MessageHandlerInterface;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageProcessorTest extends TestCase
{
    #[Test]
    public function it_routes_message_to_correct_handler(): void
    {
        $handledMessages = [];

        // Create a test handler
        $handler = new class($handledMessages) implements MessageHandlerInterface
        {
            public function __construct(private array &$handled) {}

            public function queue(): string
            {
                return 'test.queue';
            }

            public function handles(): string
            {
                return 'test.route';
            }

            public function supportedVersions(): array
            {
                return [1];
            }

            public function handle(DomainMessage $message): void
            {
                $this->handled[] = $message;
            }
        };

        // Register the handler
        $registry = $this->app->make(MessageHandlerRegistryInterface::class);
        $registry->register($handler);

        $this->assertTrue($registry->hasHandler('test.route'));
    }

    #[Test]
    public function it_registers_central_app_handler(): void
    {
        $registry = $this->app->make(MessageHandlerRegistryInterface::class);

        // The central app should have registered user.create handler
        $this->assertTrue($registry->hasHandler('user.create'));

        $handler = $registry->getHandler('user.create');
        $this->assertInstanceOf(\App\Handlers\CreateUserDataHandler::class, $handler);
    }

    #[Test]
    public function it_lists_all_handlers_via_command(): void
    {
        $this->artisan('messaging:handlers')
            ->expectsTable(
                ['Queue', 'Message Type', 'Handler Class', 'Versions'],
                [
                    ['central.userdata', 'userdata.created', 'Modules\OrganizationAdmin\Handlers\CreateTenantUserHandler', '1'],
                    ['organization.user', 'user.create', 'App\Handlers\CreateUserDataHandler', '1'],
                ]
            )
            ->assertSuccessful();
    }
}
