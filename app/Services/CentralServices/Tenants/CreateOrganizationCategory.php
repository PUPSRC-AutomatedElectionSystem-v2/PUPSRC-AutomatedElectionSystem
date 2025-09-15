<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use App\Models\CentralModels\OrganizationCategory;

class CreateOrganizationCategory
{
    /**
     * Create or fetch an organization category by name (central DB).
     */
    public function handle(string $name): OrganizationCategory
    {
        return OrganizationCategory::firstOrCreate(['name' => $name]);
    }
}
