<?php

namespace Modules\OrganizationAdmin\Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Modules\OrganizationAdmin\Models\Committee;
use Modules\OrganizationAdmin\Models\User;

class FirstCommitteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = tenancy()->tenant;
        $tenant->loadMissing(['orgContact']);

        $masterCommittee = Committee::factory()->create();

        $orgMasterEmail = $tenant->orgContact?->email;

        User::factory()->forAccount($masterCommittee)->create([
            'email' => $orgMasterEmail,
        ]);
    }
}
