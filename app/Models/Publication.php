<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Publication extends Model
{
    use Searchable;

    protected $fillable = ['category_id', 'user_id', 'title', 'slug', 'description', 'type', 'file_url', 'external_url', 'thumbnail', 'status', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function contentReviews()
    {
        return $this->morphMany(ContentReview::class, 'reviewable')->latest();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => strip_tags($this->description ?? ''),
            'type' => $this->type,
        ];
    }
}
