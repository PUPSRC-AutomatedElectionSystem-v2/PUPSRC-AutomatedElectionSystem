<?php

namespace App\Enums;

/**
 * Available rule types for matching users. Use these values in rule leaf nodes `type`.
 */
enum ScheduleRuleType: string
{
    case IDENTITY_ID = 'identity_id';
    case YEAR_LEVEL = 'year_level';
    case SECTION = 'section';
    case FIRST_LETTER_LAST_NAME = 'first_letter_last_name';
    case FIRST_LETTER_FIRST_NAME = 'first_letter_first_name';
}
