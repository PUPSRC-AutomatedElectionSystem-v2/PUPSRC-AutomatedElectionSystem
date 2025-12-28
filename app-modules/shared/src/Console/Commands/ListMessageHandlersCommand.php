<?php

namespace Modules\Shared\Console\Commands;

use Illuminate\Console\Command;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;

/**
 * List all registered message handlers.
 *
 * Useful for debugging and verifying handler registration.
 */
class ListMessageHandlersCommand extends Command
{
    protected $signature = 'messaging:handlers';

    protected $description = 'List all registered message handlers';

    public function handle(MessageHandlerRegistryInterface $registry): int
    {
        $types = $registry->registeredTypes();

        if (empty($types)) {
            $this->warn('No message handlers registered.');
            $this->line('');
            $this->line('Register handlers in your module\'s service provider:');
            $this->line('  $registry->register(new YourMessageHandler());');

            return self::SUCCESS;
        }

        $this->info('Registered Message Handlers:');
        $this->newLine();

        $rows = [];
        foreach ($types as $type) {
            $handler = $registry->getHandler($type);
            $rows[] = [
                $handler->queue(),
                $type,
                get_class($handler),
                implode(', ', $handler->supportedVersions()),
            ];
        }

        $this->table(['Queue', 'Message Type', 'Handler Class', 'Versions'], $rows);

        $this->newLine();
        $this->line('Run <info>php artisan messaging:consume</info> to start consuming from all queues.');

        return self::SUCCESS;
    }
}
