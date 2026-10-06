<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPathEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_path_id',
        'status',
        'completed_courses_count',
        'total_courses_count',
        'progress_percentage',
        'enrolled_at',
        'completed_at',
        'certificate_id',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'completed_courses_count' => 'integer',
        'total_courses_count' => 'integer',
        'progress_percentage' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->progress_percentage >= 100;
    }
}
