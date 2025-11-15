<?php

namespace App\Services;

use App\Enums\ScheduleRuleType;

/**
 * Converts the ScheduleRuleType enum into a server-side default shape for quick scaffolding.
 * Example usage: ScheduleRuleDto::shapeFor(ScheduleRuleType::YEAR_LEVEL)
 *
 * Returns an array representing a leaf node for that type. These leaves map directly to
 * attributes sourced from the centralized `users_data` table (identity_id, section, etc.).
 */
class ScheduleRuleDto
{
    public static function shapeFor(ScheduleRuleType $type): array
    {
        // Normalized server-side leaf shape uses 'values' for all leaf types
        return match ($type) {
            ScheduleRuleType::IDENTITY_ID => ['type' => $type->value, 'values' => []],
            ScheduleRuleType::YEAR_LEVEL => ['type' => $type->value, 'values' => []],
            ScheduleRuleType::SECTION => ['type' => $type->value, 'values' => []],
            ScheduleRuleType::FIRST_LETTER_LAST_NAME => ['type' => $type->value, 'values' => []],
            ScheduleRuleType::FIRST_LETTER_FIRST_NAME => ['type' => $type->value, 'values' => []],
        };
    }
}
