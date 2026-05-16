<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function update(User $user): bool
    {
        return $user->can('roles.edit');
    }
}
