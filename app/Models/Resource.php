<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = ['course_id', 'title', 'file_path', 'url'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function curriculumItem()
    {
        return $this->morphOne(CurriculumItem::class, 'itemable');
    }
}
