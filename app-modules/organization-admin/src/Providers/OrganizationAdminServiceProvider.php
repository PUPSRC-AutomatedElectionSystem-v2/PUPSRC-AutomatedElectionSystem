<?php

namespace Modules\OrganizationAdmin\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\OrganizationAdmin\Services\Password\Default\DefaultAdminPassword;
use Modules\OrganizationAdmin\Services\Password\Default\DefaultUserPassword;
use Modules\OrganizationAdmin\Services\Password\Default\MakeDefaultPassword;

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
    }
}
