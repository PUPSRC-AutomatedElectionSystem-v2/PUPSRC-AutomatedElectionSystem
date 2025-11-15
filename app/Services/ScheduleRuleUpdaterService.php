<?php

namespace App\Services;

/**
 * Simple updater service that extends ScheduleRuleStoreService semantics. Kept separate
 * as a distinct responsibility to follow SOLID: updating existing records only.
 */
class ScheduleRuleUpdaterService extends ScheduleRuleStoreService
{
    /**
     * Update model with payload. Here for semantic clarity; delegates to parent.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $payload
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function performUpdate($model, array $payload)
    {
        return parent::update($model, $payload);
    }
}
