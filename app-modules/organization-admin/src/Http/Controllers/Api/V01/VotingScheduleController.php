<?php

namespace Modules\OrganizationAdmin\Http\Controllers\Api\V01;

use App\Http\Controllers\Controller;
use App\Services\VotingScheduleRuleStoreService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VotingScheduleController extends Controller
{
    /**
     * Store a new voting schedule.
     *
     * Accepts: start_time (ISO date), end_time (ISO date), matching_rules (nullable array|json), is_active (bool)
     */
    public function store(Request $request, VotingScheduleRuleStoreService $action): JsonResponse
    {

        $payload = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'matching_rules' => ['nullable'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Create schedule through the store service — it normalizes JSON and validates rule shape
        $model = $action->create($payload);

        return response()->json($model, 201);
    }
}
