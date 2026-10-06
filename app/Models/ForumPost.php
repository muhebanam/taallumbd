<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'category_id',
        'title',
        'topic',
        'tags',
        'body',
        'views_count',
        'upvotes_count',
        'downvotes_count',
        'status',
        'is_pinned',
        'is_solved',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_solved' => 'boolean',
        'views_count' => 'integer',
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class)->latest();
    }

    public function likes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function reports()
    {
        return $this->morphMany(ContentReport::class, 'reportable');
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->where('vote_type', 'upvote')->exists();
    }

    public function userVote(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->likes()->where('user_id', $user->id)->value('vote_type');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
