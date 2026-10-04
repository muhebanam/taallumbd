<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id', 'category_id', 'title', 'slug', 'short_description', 'description',
        'thumbnail', 'price', 'is_free', 'level', 'duration', 'learn_points', 'requirements', 'status',
        'enrollment_limit', 'enrollment_start', 'enrollment_end', 'completion_requirements',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'learn_points' => 'array',
        'requirements' => 'array',
        'completion_requirements' => 'array',
        'enrollment_start' => 'datetime',
        'enrollment_end' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function curriculumItems()
    {
        return $this->hasMany(CurriculumItem::class);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopePubliclyVisible($q)
    {
        return $q->whereIn('status', ['published', 'coming_soon']);
    }

    public function scopeEnrollable($q)
    {
        return $q->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('enrollment_start')
                    ->orWhere('enrollment_start', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('enrollment_end')
                    ->orWhere('enrollment_end', '>=', now());
            });
    }
}
