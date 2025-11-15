<?php

namespace Modules\OrganizationAdmin\Http\Controllers;

use Illuminate\Http\Request;

class CandidateController
{
    public function create()
    {
        return view('organization-admin::candidate.create');
    }
}
