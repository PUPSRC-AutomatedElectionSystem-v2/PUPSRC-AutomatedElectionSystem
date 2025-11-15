<?php

namespace Modules\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;

class RegistrationSchedule extends Model
{
    use HasUlids;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'matching_rules' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Check whether this schedule applies to the given user.
     * Delegates to the ScheduleRuleEvaluator service to keep model thin and reusable
     * across registration and voting schedules.
     *
     * @param object $user User-like object with identity_id, first_name, last_name, year_level, section
     */
    public function matchesUser($user): bool
    {
        return App::make(\App\Services\ScheduleRuleEvaluator::class)->matches($this, $user);
    }
}
