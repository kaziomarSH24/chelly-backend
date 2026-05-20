<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BannerPolicy
{
   /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Banner $banner): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Banner $banner): bool
    {
        return $user->hasRole('admin');
    }
}
