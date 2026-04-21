<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    public function view(User $currentUser, User $user): bool
    {
        return $currentUser->isAdmin();
    }

    public function create(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->isAdmin();
    }

    public function delete(User $currentUser, User $user): bool
    {
        // Admin cannot delete themselves
        return $currentUser->isAdmin() && $currentUser->id !== $user->id;
    }
}
