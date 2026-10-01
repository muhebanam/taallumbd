<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'level', 'icon', 'duration', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'learning_path_courses')
            ->withPivot('sort_order')
            ->orderBy('learning_path_courses.sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
