<?php

namespace Modules\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasUuids;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function candidates()
    {
        // return $this->hasMany(Candidate::class, 'position_id', 'id');
    }

    public function currentWinner()
    {
        // return $this->belongsTo(Candidate::class, 'current_winner_id', 'id');
    }
}
