<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationCategory extends Model
{
    /** @use HasFactory<\Database\Factories\Tenants\OrganizationCategoryFactory> */
    use HasFactory;

    protected $fillable = ['name'];
}
