<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserCreator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laravel\Fortify\Contracts\CreateNewUsers::class;
    }
}
