<?php

namespace Modules\OrganizationAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    /** @use HasFactory<\Database\Factories\VoterFactory> */
    use HasFactory, HasUuids;

    const CREATED_AT = null;

    protected $guarded = [
        'id',
        'updated_at',
    ];
}
