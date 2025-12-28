<?php

namespace App\Providers;

use App\Handlers\CreateUserDataHandler;
use Illuminate\Support\ServiceProvider;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;

class MessageServiceProvider extends ServiceProvider
{
    /**
     * Message handlers to register.
     *
     * Add handler classes here to auto-register them.
     *
     * @var array<class-string>
     */
    protected array $handlers = [
        CreateUserDataHandler::class,
        // Add more handlers here:
        // SyncOrganizationHandler::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerMessageHandlers();
    }

    /**
     * Register all message handlers with the registry.
     */
    private function registerMessageHandlers(): void
    {
        if (! $this->app->bound(MessageHandlerRegistryInterface::class)) {
            return;
        }

        $registry = $this->app->make(MessageHandlerRegistryInterface::class);

        foreach ($this->handlers as $handlerClass) {
            $registry->register($this->app->make($handlerClass));
        }
    }
}
