<?php

declare(strict_types=1);

namespace App\Http\Resources\CentralResources\Tenants\ViewModels;

class CreateTenantViewModel
{
    public function toArray(): array
    {
        return [
            'defaults' => [
                'tenant' => [
                    'id' => null,
                    'domain' => '',
                    'provided_domain' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost',
                ],
                'organization' => [
                    'short_name' => '',
                    'name' => '',
                    'category_name' => '',
                    'should_copy_from_other_org' => false,
                    'allow_cross_membership' => false,
                    'theme' => [],
                    'order' => 0,
                ],
                'contacts' => [
                    'email' => '',
                    'website' => null,
                    'facebook' => null,
                    'twitter' => null,
                    'instagram' => null,
                    'threads' => null,
                    'discord' => null,
                ],
            ],
        ];
    }
}
