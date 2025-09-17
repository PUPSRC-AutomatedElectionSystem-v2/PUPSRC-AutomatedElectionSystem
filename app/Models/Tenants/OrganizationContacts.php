<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class OrganizationContact extends Model
{
    /** @use HasFactory<\Database\Factories\Tenants\OrganizationContactsFactory> */
    use CentralConnection, HasFactory;

    protected $fillable = [
        'organization_id',
        'email',
        'website',
        'facebook',
        'twitter',
        'instagram',
        'threads',
        'discord',
    ];
}
