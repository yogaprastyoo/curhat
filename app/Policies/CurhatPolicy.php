<?php

namespace App\Policies;

use App\Models\Curhat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CurhatPolicy
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
    public function view(User $user, Curhat $curhat): bool
    {
        if ($user->role === 'student') {
            return $curhat->student_id === $user->id;
        }

        if ($user->role === 'counselor') {
            return $curhat->assigned_to === $user->id;
        }
        if ($user->role === 'psychologist') {
            return $curhat->privacy_scope === 'counselor_psychologist'
                && $curhat->assigned_to === $user->id;
        }
        // Admin teknis tidak otomatis boleh membaca isi curhat.
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
    public function update(User $user, Curhat $curhat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Curhat $curhat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Curhat $curhat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Curhat $curhat): bool
    {
        return false;
    }
}
