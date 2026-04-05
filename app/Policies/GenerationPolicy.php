<?php

namespace App\Policies;

use App\Models\Generation;
use App\Models\User;

class GenerationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    public function create(User $user): bool
    {
        return $user->credits > 0;
    }

    public function update(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    public function delete(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    public function restore(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    public function forceDelete(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }
}
