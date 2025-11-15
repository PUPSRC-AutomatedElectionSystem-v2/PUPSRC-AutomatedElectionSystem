<?php

namespace Modules\OrganizationAdmin\Http\Controllers;

use Illuminate\Http\Request;

class RegistrationScheduleController
{
    public function create()
    {
        return view('organization-admin::registration.schedule.create');
    }
}
