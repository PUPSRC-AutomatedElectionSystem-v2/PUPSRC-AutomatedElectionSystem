<?php

namespace Modules\OrganizationAdmin\Policies;

use App\Models\Central\User;
use Modules\OrganizationAdmin\Models\VoterStatus;

class VoterStatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VoterStatus $voterStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VoterStatus $voterStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VoterStatus $voterStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VoterStatus $voterStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VoterStatus $voterStatus): bool
    {
        return false;
    }
}
