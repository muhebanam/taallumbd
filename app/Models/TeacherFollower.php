<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherFollower extends Model
{
    protected $table = 'teacher_followers';
    protected $fillable = ['teacher_id', 'user_id'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
