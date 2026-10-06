<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGroupPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_group_id',
        'user_id',
        'body',
        'attachments',
        'is_pinned',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_pinned' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'study_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(StudyGroupComment::class)->latest();
    }
}
