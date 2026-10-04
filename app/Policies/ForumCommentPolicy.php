<?php

namespace App\Policies;

use App\Models\ForumComment;
use App\Models\User;

class ForumCommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, ForumComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, ForumComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
