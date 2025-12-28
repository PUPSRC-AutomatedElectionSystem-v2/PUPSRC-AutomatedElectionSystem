<?php

namespace Modules\OrganizationAdmin\Http\Controllers\Api\V01;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\OrganizationAdmin\Imports\VotersImport;
use Modules\Shared\Contracts\Messaging\MessagePublisherInterface;

class VoterController
{
    public function __construct(
        private readonly MessagePublisherInterface $publisher,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function import(Request $request)
    {
        debugbar()->info('VoterController@store called');
        $payload = $request->validate([
            'votersFileList' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        Excel::import(new VotersImport($this->publisher), $payload['votersFileList']);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
