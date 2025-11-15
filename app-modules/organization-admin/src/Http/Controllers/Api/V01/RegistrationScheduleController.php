<?php

namespace Modules\OrganizationAdmin\Http\Controllers\Api\V01;

use App\Http\Controllers\Controller;
use App\Services\RegistrationScheduleRuleStoreService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegistrationScheduleController extends Controller
{
    /**
     * Create a new registration schedule.
     * Expects fields: start_time (ISO), end_time (ISO), is_active (bool), priority (int), is_default (bool), matching_rules (array or JSON string)
     */
    public function store(Request $request, RegistrationScheduleRuleStoreService $action): JsonResponse
    {
        $payload = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Create schedule through the store service — it normalizes JSON and validates rule shape
        $model = $action->create($payload);

        return response()->json($model, 201);
    }
}
