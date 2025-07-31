<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationContacts extends Model
{
    /** @use HasFactory<\Database\Factories\Tenants\OrganizationContactsFactory> */
    use HasFactory;

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
