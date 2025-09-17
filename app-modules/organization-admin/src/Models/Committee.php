<?php

namespace Modules\OrganizationAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    /** @use HasFactory<\Database\Factories\CommiteeFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    const CREATED_AT = null;
}
