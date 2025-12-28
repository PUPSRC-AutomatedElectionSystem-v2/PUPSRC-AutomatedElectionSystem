<?php

namespace Modules\OrganizationAdmin\Imports\Formats;

use Illuminate\Support\Collection;

class StudentVoterImport
{
    public static function parseRow(Collection $row): Collection
    {
        return collect([
            'identity_id' => $row->get('student_id'),
            'first_name' => $row->get('first_name'),
            'last_name' => $row->get('last_name'),
            'email' => $row->get('email'),
            'course' => $row->get('course'),
            'year_level' => $row->get('year_level'),
        ]);
    }
}
