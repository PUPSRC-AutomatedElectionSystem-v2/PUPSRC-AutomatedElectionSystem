<?php

namespace App\Listeners;

use App\Events\ShouldSetupTenantSpace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetupTenantSpaceListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShouldSetupTenantSpace $event): void
    {
        //
    }
}
