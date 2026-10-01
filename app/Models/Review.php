<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'course_id', 'teacher_id', 'rating', 'comment', 'status'];
    protected $appends = ['review'];

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }

    public function getReviewAttribute()
    {
        return $this->comment;
    }
}
