<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'body',
        'meta_title',
        'meta_description',
        'is_published',
        'last_updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
