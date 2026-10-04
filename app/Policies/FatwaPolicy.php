<?php

namespace App\Policies;

use App\Models\Fatwa;
use App\Models\User;

class FatwaPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(?User $user, Fatwa $fatwa): bool
    {
        if ($fatwa->status === 'published' && $fatwa->is_public) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $fatwa->user_id === $user->id || $user->isAdmin() || $user->isInstructor();
    }

    public function answer(User $user, Fatwa $fatwa): bool
    {
        return $user->isAdmin() || $user->isInstructor();
    }
}
