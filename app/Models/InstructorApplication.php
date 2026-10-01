<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorApplication extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'expertise',
        'experience',
        'cv_link',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
