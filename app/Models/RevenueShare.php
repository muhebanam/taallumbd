<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'teacher_id',
        'instructor_share_percentage',
        'platform_share_percentage',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'instructor_share_percentage' => 'decimal:2',
        'platform_share_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
