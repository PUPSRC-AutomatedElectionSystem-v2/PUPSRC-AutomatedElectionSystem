<?php

namespace App\Services;

use App\Enums\ScheduleRuleType;

/**
 * Produces client-friendly option structures from server enum definitions.
 * These options mirror the fields made available via the centralized `users_data` table so the
 * client knows exactly which attributes can be targeted by matching rules.
 * Example output:
 * [
 *   ['value' => 'identity_id', 'label' => 'Identity (ID)'],
 *   ['value' => 'year_level', 'label' => 'Year Level']
 * ]
 */
class ScheduleRuleOptionsDto
{
    public static function toClientOptions(): array
    {
        $map = [
            ScheduleRuleType::IDENTITY_ID->value => 'Identity (identity_id)',
            ScheduleRuleType::YEAR_LEVEL->value => 'Year Level',
            ScheduleRuleType::SECTION->value => 'Section',
            ScheduleRuleType::FIRST_LETTER_LAST_NAME->value => 'First Letter (Last Name)',
            ScheduleRuleType::FIRST_LETTER_FIRST_NAME->value => 'First Letter (First Name)',
        ];

        $out = [];
        foreach ($map as $value => $label) {
            $out[] = ['value' => $value, 'label' => $label];
        }

        return $out;
    }
}
