<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogPolicy
{
   public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $user->hasRole('admin');
    }
}
