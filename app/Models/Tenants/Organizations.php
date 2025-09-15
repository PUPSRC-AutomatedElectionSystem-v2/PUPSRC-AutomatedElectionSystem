<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Organizations extends Model
{
    /** @use HasFactory<\Database\Factories\Tenants\OrganizationsFactory> */
    use CentralConnection, HasFactory, HasUlids;

    protected $fillable = [
        'id',
        'tenant_id',
        'short_name',
        'name',
        'contact_id',
        'category_id',
        'should_copy_from_other_org',
        'allow_cross_membership',
        'theme',
        'order',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'theme' => 'array',
        'should_copy_from_other_org' => 'boolean',
        'allow_cross_membership' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id', 'id');
    }
}
