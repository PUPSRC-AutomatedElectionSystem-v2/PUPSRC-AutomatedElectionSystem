<?php

namespace Modules\OrganizationAdmin\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class OrganizationAdminServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Relation::morphMap([
            'committee' => 'Modules\OrganizationAdmin\Models\Committee',
            'voter' => 'Modules\OrganizationAdmin\Models\Voter',
        ]);
    }
}
