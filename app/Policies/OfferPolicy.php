<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OfferPolicy
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
    public function update(User $user, Offer $offer): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Offer $offer): bool
    {
        return $user->hasRole('admin');
    }
}
