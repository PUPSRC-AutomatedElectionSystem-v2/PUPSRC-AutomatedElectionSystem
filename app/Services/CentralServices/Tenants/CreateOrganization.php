<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use App\Models\Central\Organization;

class CreateOrganization
{
    /**
     * @param  array{tenant_id: string, short_name: string, name: string, category_id: int, should_copy_from_other_org?: bool, allow_cross_membership?: bool, theme?: array|null, order?: int|null}  $data
     */
    public function handle(array $data): Organization
    {
        return Organization::create([
            'tenant_id' => $data['tenant_id'],
            'short_name' => $data['short_name'],
            'name' => $data['name'],
            'contact_id' => 0,
            'category_id' => $data['category_id'],
            'should_copy_from_other_org' => (bool) ($data['should_copy_from_other_org'] ?? false),
            'allow_cross_membership' => (bool) ($data['allow_cross_membership'] ?? false),
            'theme' => $data['theme'] ?? [],
            'order' => $data['order'] ?? 0,
        ]);
    }
}
