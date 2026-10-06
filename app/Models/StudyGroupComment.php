<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGroupComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_group_post_id',
        'user_id',
        'body',
    ];

    public function post()
    {
        return $this->belongsTo(StudyGroupPost::class, 'study_group_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
