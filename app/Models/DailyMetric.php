<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    use HasFactory;

    protected $table = 'daily_metrics';

    protected $fillable = [
        'date',
        'metric_key',
        'metric_value',
        'breakdown',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'metric_value' => 'float',
            'breakdown' => 'array',
        ];
    }

    /**
     * Scope for a specific metric key.
     */
    public function scopeKey($query, string $key)
    {
        return $query->where('metric_key', $key);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }
}
