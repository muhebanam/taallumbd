<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumItem extends Model
{
    protected $fillable = [
        'course_id',
        'section_id',
        'itemable_type',
        'itemable_id',
        'item_type',
        'title_snapshot',
        'sort_order',
        'is_required',
        'is_preview',
        'drip_type',
        'drip_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_preview' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
