<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonBookmark extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'lesson_id', 'timestamp_seconds', 'title'];

    protected $casts = [
        'timestamp_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
