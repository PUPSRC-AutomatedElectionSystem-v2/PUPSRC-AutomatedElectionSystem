<?php

namespace Modules\OrganizationAdmin\Http\Controllers\Api\V01;

use Illuminate\Http\Request;
use Modules\OrganizationAdmin\Models\Candidate;

class CandidateController
{
    public function store(Request $request)
    {
        // TODO: Validate the incoming request data

        // Create a new candidate record
        $candidate = Candidate::create($request->all());

        // Return a JSON response with the created candidate
        return response()->json([
            'message' => 'Candidate created successfully',
            'candidate' => $candidate,
        ], 201);
    }
}
