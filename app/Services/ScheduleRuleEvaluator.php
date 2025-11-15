<?php

namespace App\Services;

use App\Enums\ScheduleRuleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Evaluates rule expression trees against a user-like object.
 * Single responsibility: boolean evaluation of rule trees.
 *
 * Shape expected:
 * - Group nodes: { "any": [node,...] } or { "all": [node,...] }
 * - Negation: { "not": node }
 * - Leaf nodes: { "type":"identity_id", "ids":[...]} etc.
 */
class ScheduleRuleEvaluator
{
    /**
     * Return true if the schedule's matching_rules evaluate to true for the given user.
     * If matching_rules is null or empty, returns false.
     *
     * The provided schedule may be any Eloquent model that exposes the matching_rules JSON column plus
     * the typical lifecycle flags (is_active, start_time, end_time) used by both registration and voting
     * schedules. This keeps the evaluator reusable across modules while respecting SOLID.
     *
     * The $user payload should come from the centralized users_data table (identity_id, name fields,
     * section, year_level). Make sure the consumer hydrates those properties before evaluation.
     *
     * @param Model $schedule
     * @param object $user User-like object exposing identity_id, first_name, last_name, year_level, section
     */
    public function matches(Model $schedule, $user): bool
    {
        if (! $schedule->is_active) {
            return false;
        }

        $now = now();
        if ($schedule->start_time && $schedule->start_time->gt($now)) {
            return false;
        }
        if ($schedule->end_time && $schedule->end_time->lt($now)) {
            return false;
        }

        $rules = $schedule->matching_rules ?? null;
        if (empty($rules) || ! is_array($rules)) {
            return false;
        }

        return (bool) $this->evaluateNode($rules, $user);
    }

    protected function evaluateNode(array $node, $user): bool
    {
        // group nodes
        if (array_key_exists('any', $node)) {
            foreach ($node['any'] as $child) {
                if ($this->evaluateNode($child, $user)) {
                    return true;
                }
            }
            return false;
        }

        if (array_key_exists('all', $node)) {
            foreach ($node['all'] as $child) {
                if (! $this->evaluateNode($child, $user)) {
                    return false;
                }
            }
            return true;
        }

        if (array_key_exists('not', $node)) {
            return ! $this->evaluateNode($node['not'], $user);
        }

        // leaf node
        $type = Arr::get($node, 'type');
        if (! $type) {
            return false;
        }

        // Normalized values key used for all leaf types
        $values = (array) Arr::get($node, 'values', []);

        switch ($type) {
            case ScheduleRuleType::IDENTITY_ID->value:
                return in_array((string) ($user->identity_id ?? ''), array_map('strval', $values), true);

            case ScheduleRuleType::YEAR_LEVEL->value:
                return in_array((int) ($user->year_level ?? null), array_map('intval', $values), true);

            case ScheduleRuleType::SECTION->value:
                return in_array((string) ($user->section ?? ''), array_map('strval', $values), true);

            case ScheduleRuleType::FIRST_LETTER_LAST_NAME->value:
                $first = strtoupper(substr((string) ($user->last_name ?? ''), 0, 1));
                return in_array($first, array_map('strtoupper', $values), true);

            case ScheduleRuleType::FIRST_LETTER_FIRST_NAME->value:
                $first = strtoupper(substr((string) ($user->first_name ?? ''), 0, 1));
                return in_array($first, array_map('strtoupper', $values), true);

            default:
                return false;
        }
    }
}
