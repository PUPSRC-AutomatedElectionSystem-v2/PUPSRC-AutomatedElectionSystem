<?php

namespace App\Services;

use Modules\Shared\Models\RegistrationSchedule;

/**
 * Concrete storage service for registration schedules.
 * Sets the modelClass to the shared RegistrationSchedule model.
 */
class RegistrationScheduleRuleStoreService extends ScheduleRuleStoreService
{
    protected string $modelClass = RegistrationSchedule::class;
}
