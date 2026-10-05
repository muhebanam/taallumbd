<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'reviewer_id',
        'reviewer_role',
        'from_status',
        'to_status',
        'decision',
        'notes',
        'version',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
