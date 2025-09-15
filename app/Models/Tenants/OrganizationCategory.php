<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class OrganizationCategory extends Model
{
    /** @use HasFactory<\Database\Factories\Tenants\OrganizationCategoryFactory> */
    use CentralConnection, HasFactory;

    protected $fillable = ['name'];
}
