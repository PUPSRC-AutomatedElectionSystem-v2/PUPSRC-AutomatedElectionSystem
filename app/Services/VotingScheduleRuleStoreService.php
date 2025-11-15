<?php

namespace App\Services;

use Modules\OrganizationAdmin\Models\VotingSchedule;

/**
 * Concrete storage service stub for voting schedules.
 * Replace the $modelClass with your VotingSchedule model FQCN (e.g. Modules\Voting\Models\VotingSchedule::class)
 * when available in your codebase.
 */
class VotingScheduleRuleStoreService extends ScheduleRuleStoreService
{
    /**
     * @var string
     */
    protected string $modelClass = VotingSchedule::class;
}
