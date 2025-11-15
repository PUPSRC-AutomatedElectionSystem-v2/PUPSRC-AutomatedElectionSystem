<?php

namespace Modules\OrganizationAdmin\Http\Controllers\Api\V01;

use Illuminate\Http\Request;
use Modules\Shared\Models\Position;

class PositionController
{

    public function index()
    {
        return Position::all();
    }

    public function store(Request $request)
    {
        // Logic to store a new position

        Position::create($request->all());
        return response()->json(['message' => 'Position created successfully'], 201);
    }
}
