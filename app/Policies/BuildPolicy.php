<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Build;
use App\Models\User;

class BuildPolicy
{
    public function view(User $user, Build $build): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Ghost and viewer cannot trigger builds
        return in_array($user->role, [UserRole::Creator, UserRole::Admin, UserRole::Publisher]);
    }

    public function delete(User $user, Build $build): bool
    {
        if ($user->isGhost()) {
            return false;
        }

        if ($user->isCreator()) {
            return true;
        }

        // Admins can delete builds of non-creator users (for report resolution)
        if ($user->role === UserRole::Admin) {
            return !$build->user->isCreator();
        }

        // Viewers cannot delete builds
        if ($user->role === \App\Enums\UserRole::Viewer) {
            return false;
        }

        // Publishers can only delete their own builds
        return $user->id === $build->user_id;
    }
}
