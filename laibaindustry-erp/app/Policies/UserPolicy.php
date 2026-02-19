<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function update(User $user, User $model): bool
    {
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            return false;
        }

        if ($user->role === 'manager' && $model->role === 'admin') {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            return false;
        }

        if ($user->id === $model->id) {
            return false;
        }

        if ($model->role === 'admin') {
            if ($user->role === 'manager') {
                return false;
            }
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return false;
            }
        }

        return true;
    }
}
