<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Package $package): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Only publishers, admins and creator can create packages
        return in_array($user->role, [UserRole::Creator, UserRole::Admin, UserRole::Publisher]);
    }

    public function update(User $user, Package $package): bool
    {
        if ($user->isGhost()) {
            return false;
        }
        // Creator can edit anything; everyone else only their own
        return $user->isCreator() || $user->id === $package->user_id;
    }

    public function delete(User $user, Package $package): bool
    {
        if ($user->isGhost()) {
            return false;
        }
        // Creator can delete anything; everyone else only their own
        return $user->isCreator() || $user->id === $package->user_id;
    }
}
