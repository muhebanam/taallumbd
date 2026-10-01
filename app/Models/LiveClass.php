<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveClass extends Model
{
    protected $table = 'live_classes';

    protected $fillable = ['course_id', 'title', 'meeting_url', 'start_time', 'duration'];

    protected $casts = [
        'start_time' => 'datetime',
        'duration' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function curriculumItem()
    {
        return $this->morphOne(CurriculumItem::class, 'itemable');
    }
}
