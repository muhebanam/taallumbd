<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_group_id',
        'user_id',
        'role', // owner, moderator, member
        'status', // active, pending_approval, banned
        'weekly_goal_progress',
        'weekly_goal_completed',
        'joined_at',
    ];

    protected $casts = [
        'weekly_goal_progress' => 'integer',
        'weekly_goal_completed' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'study_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
