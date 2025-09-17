<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use App\Models\Central\OrganizationContacts;

class CreateOrganizationContact
{
    /**
     * @param  array{organization_id: string, email: string, website?: ?string, facebook?: ?string, twitter?: ?string, instagram?: ?string, threads?: ?string, discord?: ?string}  $data
     */
    public function handle(array $data): OrganizationContacts
    {
        return OrganizationContacts::create([
            'organization_id' => $data['organization_id'],
            'email' => $data['email'],
            'website' => $data['website'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'twitter' => $data['twitter'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'threads' => $data['threads'] ?? null,
            'discord' => $data['discord'] ?? null,
        ]);
    }
}
