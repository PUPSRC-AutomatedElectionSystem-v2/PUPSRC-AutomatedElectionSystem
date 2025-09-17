<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    /** @use HasFactory<\Database\Factories\Central\UserDataFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
