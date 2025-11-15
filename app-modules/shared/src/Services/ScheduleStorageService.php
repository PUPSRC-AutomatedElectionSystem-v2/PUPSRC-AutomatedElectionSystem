<?php

namespace App\Services;

use Modules\Shared\Models\RegistrationSchedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for creating/updating RegistrationSchedule records and
 * validating a small subset of the rule structure before persistence.
 * Single responsibility: persist schedules and ensure matching_rules shape is acceptable.
 */
class RegistrationScheduleStorageService
{
    /**
     * Create a schedule from payload.
     * Expected $payload keys: matching_rules (array|null), start_time, end_time, is_active, priority
     */
    public function create(array $payload): RegistrationSchedule
    {
        $payload = $this->normalizePayload($payload);

        $this->validateRulesShape($payload['matching_rules'] ?? null);

        /** @var RegistrationSchedule $model */
        $model = RegistrationSchedule::create($payload);

        return $model;
    }

    /**
     * Update an existing schedule with payload.
     */
    public function update(RegistrationSchedule $schedule, array $payload): RegistrationSchedule
    {
        $payload = $this->normalizePayload($payload);

        if (array_key_exists('matching_rules', $payload)) {
            $this->validateRulesShape($payload['matching_rules']);
        }

        $schedule->fill($payload);
        $schedule->save();

        return $schedule;
    }

    /**
     * Normalize incoming payload for model mass assignment.
     */
    protected function normalizePayload(array $payload): array
    {
        // ensure matching_rules is null or array
        if (array_key_exists('matching_rules', $payload) && is_string($payload['matching_rules'])) {
            $decoded = json_decode($payload['matching_rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['matching_rules'] = $decoded;
            }
        }

        return $payload;
    }

    /**
     * Very small validation of rules shape. This is intentionally permissive -
     * it ensures keys used for the UI and evaluator exist and are arrays where expected.
     * Throw an \InvalidArgumentException on invalid shape.
     */
    protected function validateRulesShape($rules): void
    {
        if ($rules === null) {
            return; // allowed: schedule might be default-driven or created later
        }

        if (! is_array($rules)) {
            throw new \InvalidArgumentException('matching_rules must be an array or null');
        }

        // Basic recursive shape check
        $this->assertNodeShape($rules);
    }

    protected function assertNodeShape(array $node): void
    {
        // group node
        if (array_key_exists('any', $node) || array_key_exists('all', $node)) {
            $key = array_key_exists('any', $node) ? 'any' : 'all';
            if (! is_array($node[$key])) {
                throw new \InvalidArgumentException("'$key' must be an array of child nodes");
            }
            foreach ($node[$key] as $child) {
                if (! is_array($child)) {
                    throw new \InvalidArgumentException("each child of '$key' must be a node array");
                }
                $this->assertNodeShape($child);
            }
            return;
        }

        if (array_key_exists('not', $node)) {
            if (! is_array($node['not'])) {
                throw new \InvalidArgumentException("'not' must be a node array");
            }
            $this->assertNodeShape($node['not']);
            return;
        }

        // leaf node must have a type
        if (! array_key_exists('type', $node)) {
            throw new \InvalidArgumentException('Leaf nodes must contain a "type" key');
        }

        $type = $node['type'];

        // allowed leaf types
        $allowedTypes = [
            'identity_id',
            'year_level',
            'section',
            'first_letter_last_name',
            'first_letter_first_name',
        ];

        if (! in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException("Unknown rule type: {$type}");
        }

        // ensure arrays where expected
        switch ($type) {
            case 'identity_id':
                if (! array_key_exists('ids', $node) || ! is_array($node['ids'])) {
                    throw new \InvalidArgumentException('identity_id nodes require an array "ids"');
                }
                break;
            case 'year_level':
                if (! array_key_exists('years', $node) || ! is_array($node['years'])) {
                    throw new \InvalidArgumentException('year_level nodes require an array "years"');
                }
                break;
            case 'section':
                if (! array_key_exists('sections', $node) || ! is_array($node['sections'])) {
                    throw new \InvalidArgumentException('section nodes require an array "sections"');
                }
                break;
            case 'first_letter_last_name':
            case 'first_letter_first_name':
                if (! array_key_exists('letters', $node) || ! is_array($node['letters'])) {
                    throw new \InvalidArgumentException('first_letter nodes require an array "letters"');
                }
                break;
        }
    }
}
