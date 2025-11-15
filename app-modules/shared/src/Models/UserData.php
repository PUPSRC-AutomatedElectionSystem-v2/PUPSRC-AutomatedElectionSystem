<?php

namespace Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'data' => 'array',
        'organizations' => 'array',
    ];
}
