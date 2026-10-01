<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['course_id', 'lesson_id', 'title', 'description', 'deadline', 'total_marks'];
    protected $casts = ['deadline' => 'datetime'];
    public function course() { return $this->belongsTo(Course::class); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }
}
