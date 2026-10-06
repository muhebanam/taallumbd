<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_post_id',
        'user_id',
        'parent_id',
        'body',
        'is_scholar_verified',
        'verified_by_scholar_id',
        'verified_at',
        'upvotes_count',
        'downvotes_count',
    ];

    protected $casts = [
        'is_scholar_verified' => 'boolean',
        'verified_at' => 'datetime',
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function parent()
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumComment::class, 'parent_id')->with('user:id,name,role')->oldest();
    }

    public function verifiedByScholar()
    {
        return $this->belongsTo(User::class, 'verified_by_scholar_id');
    }

    public function likes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function reports()
    {
        return $this->morphMany(ContentReport::class, 'reportable');
    }
}
