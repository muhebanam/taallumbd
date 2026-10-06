<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feature',
        'prompt_hash',
        'prompt_redacted',
        'response',
        'sources',
        'tokens',
        'cost',
        'latency_ms',
        'flagged',
        'flag_reason',
        'feedback_rating',
        'feedback_notes',
        'scholar_reviewed',
        'scholar_reviewed_by',
        'scholar_notes',
    ];

    protected $casts = [
        'sources' => 'array',
        'tokens' => 'integer',
        'cost' => 'decimal:6',
        'latency_ms' => 'integer',
        'flagged' => 'boolean',
        'feedback_rating' => 'integer',
        'scholar_reviewed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scholar_reviewed_by');
    }

    public function scopeFlagged($query)
    {
        return $query->where('flagged', true);
    }

    public function scopePendingScholarReview($query)
    {
        return $query->where('flagged', true)->where('scholar_reviewed', false);
    }
}
