<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'section_id', 'title', 'slug', 'content', 'video_url', 'lecture_sheet', 'is_preview', 'sort_order'];

    protected $casts = ['is_preview' => 'boolean'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function getIsFreePreviewAttribute(): bool
    {
        return (bool) ($this->attributes['is_preview'] ?? false);
    }

    public function getIsFreeAttribute(): bool
    {
        return (bool) ($this->attributes['is_preview'] ?? false);
    }
}
