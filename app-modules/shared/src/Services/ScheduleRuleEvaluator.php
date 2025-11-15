<?php

namespace App\Services;

use Modules\Shared\Models\RegistrationSchedule;
use Illuminate\Support\Arr;

/**
 * Service responsible for evaluating registration schedule rule trees.
 * Single responsibility: evaluate a schedule's `matching_rules` JSON structure
 * against a given user instance.
 */
class RegistrationRuleEvaluator
{
    /**
     * Evaluate whether the given schedule applies to the provided user.
     * The schedule may contain a `matching_rules` JSON expression tree.
     * If `matching_rules` is empty, false is returned — legacy matching
     * should be handled elsewhere if needed.
     *
     * @param RegistrationSchedule $schedule
     * @param mixed $user An object providing properties used in rules (id, identity_id, last_name, first_name, year_level, section)
     * @return bool
     */
    public function matches(RegistrationSchedule $schedule, $user): bool
    {
        // quick checks
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

        if (empty($rules)) {
            return false;
        }

        return (bool) $this->evaluateNode($rules, $user);
    }

    /**
     * Recursive evaluation of a rule node. Node may be an 'any' (OR), 'all' (AND), 'not' (negation)
     * or a leaf condition with a 'type' key.
     *
     * Supported leaf types (per request):
     * - identity_id  => {"type":"identity_id","ids":[...]}  (match user->identity_id)
     * - year_level   => {"type":"year_level","years":[...]}   (match user->year_level)
     * - section      => {"type":"section","sections":[...]}    (match user->section)
     * - first_letter_last_name => {"type":"first_letter_last_name","letters":["A","B"]}
     * - first_letter_first_name => {"type":"first_letter_first_name","letters":["A"]}
     */
    protected function evaluateNode($node, $user): bool
    {
        if (! is_array($node)) {
            return false;
        }

        // boolean groups
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

        switch ($type) {
            case 'identity_id':
                $ids = (array) Arr::get($node, 'ids', []);
                return in_array((string) ($user->identity_id ?? ''), array_map('strval', $ids), true);

            case 'year_level':
                $years = (array) Arr::get($node, 'years', []);
                return in_array((int) ($user->year_level ?? null), array_map('intval', $years), true);

            case 'section':
                $sections = (array) Arr::get($node, 'sections', []);
                return in_array((string) ($user->section ?? ''), array_map('strval', $sections), true);

            case 'first_letter_last_name':
                $letters = (array) Arr::get($node, 'letters', []);
                $first = strtoupper(substr((string) ($user->last_name ?? ''), 0, 1));
                return in_array($first, array_map('strtoupper', $letters), true);

            case 'first_letter_first_name':
                $letters = (array) Arr::get($node, 'letters', []);
                $first = strtoupper(substr((string) ($user->first_name ?? ''), 0, 1));
                return in_array($first, array_map('strtoupper', $letters), true);

            default:
                // unknown leaf type — return false to be safe
                return false;
        }
    }
}
