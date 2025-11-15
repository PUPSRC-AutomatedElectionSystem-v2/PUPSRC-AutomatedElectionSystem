<?php

namespace Modules\OrganizationAdmin\Http\Controllers;

use Illuminate\Http\Request;

class PositionController
{
    public function create()
    {
        return view('organization-admin::position.create');
    }
}
