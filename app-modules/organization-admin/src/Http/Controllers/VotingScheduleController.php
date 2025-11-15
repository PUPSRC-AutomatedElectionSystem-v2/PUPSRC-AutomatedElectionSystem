<?php

namespace Modules\OrganizationAdmin\Http\Controllers;

use Illuminate\Http\Request;

class VotingScheduleController
{
    public function create()
    {
        return view('organization-admin::voting.schedule.create');
    }
}
