<?php

namespace App\Models;

use App\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'academic_year',
        'head_teacher_id',
        'room_number',
        'description',
        'status',
    ];

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_teacher_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CohortMember::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_members')
            ->wherePivot('role', 'student')
            ->withPivot('roll_number')
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_members')
            ->wherePivot('role', 'teacher')
            ->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'cohort_course')
            ->withPivot(['assigned_teacher_id', 'is_mandatory', 'start_date', 'end_date'])
            ->withTimestamps();
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function gradeBooks(): HasMany
    {
        return $this->hasMany(GradeBook::class);
    }
}
