<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReputationPoint extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action', // accepted_answer, upvote_received, course_completed, goal_completed, etc.
        'points',
        'source_type',
        'source_id',
        'awarded_by_user_id',
        'created_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by_user_id');
    }
}
