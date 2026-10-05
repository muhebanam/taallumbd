<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fatwa extends Model
{
    use HasFactory;

    protected $table = 'fatawa';

    protected $fillable = [
        'question_user_id', 'answered_by', 'category_id', 'teacher_id', 'assigned_scholar_id',
        'related_course_id', 'questioner_name', 'questioner_email', 'questioner_phone',
        'question_title', 'question_body', 'answer_body', 'references', 'is_private',
        'status', 'views_count', 'published_at', 'answered_at',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'published_at' => 'datetime',
        'answered_at' => 'datetime',
        'views_count' => 'integer',
    ];

    protected $appends = ['subject'];

    protected static function booted(): void
    {
        $clearCache = function () {
            cache()->forget('homepage_latest_fatawa');
            cache()->forget('fatawa_stats');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function mufti()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function assignedScholar()
    {
        return $this->belongsTo(Teacher::class, 'assigned_scholar_id');
    }

    public function relatedCourse()
    {
        return $this->belongsTo(Course::class, 'related_course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'question_user_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->where('is_private', false);
    }

    public function getSubjectAttribute()
    {
        return $this->question_title;
    }
}
