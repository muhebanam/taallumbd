<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;

class ForumPostPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, ForumPost $post): bool
    {
        return $post->user_id === $user->id;
    }

    public function delete(User $user, ForumPost $post): bool
    {
        return $post->user_id === $user->id;
    }
}
