<?php

namespace App\Policies;

use App\Models\CompensationCode;
use App\Models\User;

class CompensationCodePolicy
{
    /**
     * Determine whether the user can view any compensation codes.
     * Only admins can view all codes.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the compensation code.
     * Users can only view their own codes; admins can view all.
     */
    public function view(User $user, CompensationCode $compensationCode): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Clients can only view their own codes
        return $compensationCode->user_id === $user->id;
    }

    /**
     * Determine whether the user can create compensation codes.
     * Only admins can create codes.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the compensation code.
     * Only admins can update codes, and only if not yet used.
     */
    public function update(User $user, CompensationCode $compensationCode): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        // Prevent editing codes that have already been used
        return $compensationCode->uses_count === 0;
    }

    /**
     * Determine whether the user can delete the compensation code.
     * Only admins can delete codes, and only if not yet used.
     */
    public function delete(User $user, CompensationCode $compensationCode): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        // Prevent deleting codes that have already been used
        return $compensationCode->uses_count === 0;
    }

    /**
     * Determine whether the user can restore the compensation code.
     * Only admins.
     */
    public function restore(User $user, CompensationCode $compensationCode): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the compensation code.
     * Only admins.
     */
    public function forceDelete(User $user, CompensationCode $compensationCode): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can use (apply) the compensation code.
     * Clients can only use their own codes; admins can't use them (only clients during reservation).
     */
    public function use(User $user, CompensationCode $compensationCode): bool
    {
        // Only the code owner can use it
        return $compensationCode->user_id === $user->id;
    }
}
