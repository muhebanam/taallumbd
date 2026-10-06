<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = ['user_id', 'course_id', 'lesson_id', 'is_completed', 'last_position_seconds', 'completed_at'];

    protected $casts = [
        'is_completed' => 'boolean',
        'last_position_seconds' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
