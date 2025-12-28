<?php

namespace Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shared\Contracts\Messaging\MessageConsumerInterface;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;
use Modules\Shared\Services\MessageHandlerRegistry;
use Modules\Shared\Services\MessageProcessor;
use Modules\Shared\Services\RabbitMqMessageConsumer;
use Modules\Shared\Services\RabbitMqMessagePublisher;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind message publisher interface to RabbitMQ implementation
        $this->app->singleton(MessagePublisherInterface::class, function () {
            return RabbitMqMessagePublisher::fromConfig();
        });

        // Bind message consumer interface to RabbitMQ implementation
        $this->app->singleton(MessageConsumerInterface::class, function () {
            return RabbitMqMessageConsumer::fromConfig();
        });

        // Bind handler registry as singleton (shared across handlers)
        $this->app->singleton(MessageHandlerRegistryInterface::class, MessageHandlerRegistry::class);

        // Bind the message processor
        $this->app->singleton(MessageProcessor::class, function ($app) {
            return new MessageProcessor(
                $app->make(MessageConsumerInterface::class),
                $app->make(MessageHandlerRegistryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Shared\Console\Commands\ConsumeMessagesCommand::class,
                \Modules\Shared\Console\Commands\ListMessageHandlersCommand::class,
                \Modules\Shared\Console\Commands\SetupMessagingCommand::class,
            ]);
        }
    }
}
