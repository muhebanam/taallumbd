<?php

namespace App\Models;

use App\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'cohort_id',
        'course_id',
        'created_by',
        'title',
        'description',
        'exam_type',
        'duration_minutes',
        'total_marks',
        'pass_marks',
        'start_time',
        'end_time',
        'question_bank',
        'randomize_questions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'question_bank' => 'array',
            'randomize_questions' => 'boolean',
            'duration_minutes' => 'integer',
            'total_marks' => 'float',
            'pass_marks' => 'float',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class);
    }
}
