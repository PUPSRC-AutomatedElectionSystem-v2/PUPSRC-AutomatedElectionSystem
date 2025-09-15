<?php

namespace App\Models\CentralModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class OrganizationContacts extends Model
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
