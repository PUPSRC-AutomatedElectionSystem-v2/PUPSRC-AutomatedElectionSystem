<?php

namespace Modules\OrganizationAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoterStatus extends Model
{
    /** @use HasFactory<\Database\Factories\VoterStatusFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
