<?php

namespace App\Services;

use App\Enums\ScheduleRuleType;
use Illuminate\Support\Arr;

/**
 * Abstract base class for persisting rule-bearing models (registration, voting, etc.).
 * Child classes must set $modelClass to the FQCN of the model they persist.
 *
 * Responsibility: normalize payload, validate rule shape via ScheduleRuleValidator,
 * and create/update the model instance.
 */
abstract class ScheduleRuleStoreService
{
    /**
     * Fully qualified model class this storage operates on.
     * Example: Modules\Shared\Models\RegistrationSchedule::class
     *
     * @var string
     */
    protected string $modelClass;

    protected ScheduleRuleValidator $validator;

    public function __construct()
    {
        $this->validator = new ScheduleRuleValidator();
    }

    /**
     * Create a new model with the payload. Returns the created model.
     *
     * @param array $payload
     */
    public function create(array $payload)
    {
        $payload = $this->normalizePayload($payload);

        $this->validator->validate($payload['matching_rules'] ?? null);

        $modelClass = $this->modelClass;
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $modelClass::create($payload);

        return $model;
    }

    /**
     * Update an existing model with payload. Returns the updated model.
     */
    public function update($model, array $payload)
    {
        $payload = $this->normalizePayload($payload);

        if (array_key_exists('matching_rules', $payload)) {
            $this->validator->validate($payload['matching_rules']);
        }

        $model->fill($payload);
        $model->save();

        return $model;
    }

    protected function normalizePayload(array $payload): array
    {
        if (array_key_exists('matching_rules', $payload) && is_string($payload['matching_rules'])) {
            $decoded = json_decode($payload['matching_rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['matching_rules'] = $decoded;
            }
        }

        return $payload;
    }
}
