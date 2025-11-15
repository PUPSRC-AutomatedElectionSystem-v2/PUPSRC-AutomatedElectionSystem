<?php

namespace App\Services;

use App\Enums\ScheduleRuleType;

/**
 * Validates the shape of a rule expression tree before persistence or evaluation.
 * The user attributes referenced by these rules (identity_id, name fragments, section, year_level)
 * are expected to be hydrated from the centralized `users_data` table before validation/evaluation.
 *
 * Expected node shapes:
 * - Group nodes: { "any": [node,...] } or { "all": [node,...] }
 * - Negation: { "not": node }
 * - Leaf nodes: { "type": "identity_id", "values": ["...", ...] }
 *
 * Supported leaf type shapes (normalized):
 * - identity_id: { type: 'identity_id', values: [string] }
 * - year_level:  { type: 'year_level', values: [int] }
 * - section:     { type: 'section', values: [string] }
 * - first_letter_last_name: { type: 'first_letter_last_name', values: [string] }
 * - first_letter_first_name: { type: 'first_letter_first_name', values: [string] }
 */
class ScheduleRuleValidator
{
    /**
     * Validate top-level rules node; throws \InvalidArgumentException on invalid shape.
     *
     * @param array|null $rules
     * @return void
     */
    public function validate(?array $rules): void
    {
        if ($rules === null) {
            return;
        }

        if (! is_array($rules)) {
            throw new \InvalidArgumentException('Rules must be an array or null.');
        }

        $this->assertNode($rules);
    }

    protected function assertNode(array $node): void
    {
        if (array_key_exists('any', $node) || array_key_exists('all', $node)) {
            $key = array_key_exists('any', $node) ? 'any' : 'all';
            if (! is_array($node[$key])) {
                throw new \InvalidArgumentException("Node '$key' must be an array");
            }
            foreach ($node[$key] as $child) {
                if (! is_array($child)) {
                    throw new \InvalidArgumentException("Each child of '$key' must be a node array");
                }
                $this->assertNode($child);
            }
            return;
        }

        if (array_key_exists('not', $node)) {
            if (! is_array($node['not'])) {
                throw new \InvalidArgumentException("'not' must be a node array");
            }
            $this->assertNode($node['not']);
            return;
        }

        // Leaf node
        if (! array_key_exists('type', $node)) {
            throw new \InvalidArgumentException('Leaf nodes must contain a "type" key');
        }

        $type = $node['type'];
        if (! in_array($type, array_map(fn($t) => $t->value, ScheduleRuleType::cases()), true)) {
            throw new \InvalidArgumentException("Unknown rule type: {$type}");
        }

        $this->assertLeafShape($type, $node);
    }

    protected function assertLeafShape(string $type, array $node): void
    {
        // All leaf types now use a normalized 'values' array.
        if (! array_key_exists('values', $node) || ! is_array($node['values'])) {
            throw new \InvalidArgumentException("{$type} nodes require an array 'values'");
        }
        // Additional type-specific checks could be added here if needed (e.g., numeric check for year_level)
        return;
    }
}
