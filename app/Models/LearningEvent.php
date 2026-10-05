<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LearningEvent extends Model
{
    use HasFactory;

    protected $table = 'learning_events';

    protected $fillable = [
        'user_id',
        'event_type',
        'subject_type',
        'subject_id',
        'course_id',
        'properties',
        'session_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for a specific event type.
     */
    public function scopeOfType($query, string|array $type)
    {
        return is_array($type) ? $query->whereIn('event_type', $type) : $query->where('event_type', $type);
    }

    /**
     * Scope for time window.
     */
    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }
}
