<?php

namespace Modules\OrganizationAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Committee extends Model
{
    /** @use HasFactory<\Database\Factories\CommiteeFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    const CREATED_AT = null;

    public function users(): MorphOne
    {
        return $this->morphOne(User::class, 'account');
    }
}
