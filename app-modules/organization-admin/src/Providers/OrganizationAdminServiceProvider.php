<?php

namespace Modules\OrganizationAdmin\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\OrganizationAdmin\Handlers\CreateTenantUserHandler;
use Modules\OrganizationAdmin\Services\Password\Default\DefaultAdminPassword;
use Modules\OrganizationAdmin\Services\Password\Default\DefaultUserPassword;
use Modules\OrganizationAdmin\Services\Password\Default\MakeDefaultPassword;
use Modules\Shared\Contracts\Messaging\MessageHandlerRegistryInterface;

class OrganizationAdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('make-default-password', MakeDefaultPassword::class);
        $this->app->singleton(DefaultUserPassword::class);
        $this->app->singleton(DefaultAdminPassword::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'committee' => 'Modules\OrganizationAdmin\Models\Committee',
            'voter' => 'Modules\OrganizationAdmin\Models\Voter',
        ]);

        $this->registerMessageHandlers();
    }

    /**
     * Register message handlers for organization-admin module.
     *
     * This module consumes 'userdata.created' messages from the central app
     * to create tenant User and Voter records.
     */
    private function registerMessageHandlers(): void
    {
        if (! $this->app->bound(MessageHandlerRegistryInterface::class)) {
            return;
        }

        $registry = $this->app->make(MessageHandlerRegistryInterface::class);

        $registry->register($this->app->make(CreateTenantUserHandler::class));
    }
}
