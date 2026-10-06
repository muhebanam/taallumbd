<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail_url' => $this->thumbnail ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : asset('storage/'.$this->thumbnail)) : null,
            'price' => (float) $this->price,
            'price_bdt' => number_format((float) $this->price, 2),
            'is_free' => (bool) $this->is_free,
            'category' => $this->category,
            'level' => $this->level ?? 'all_levels',
            'status' => $this->status,
            'duration' => $this->duration,
            'total_lessons' => $this->lessons_count ?? ($this->relationLoaded('lessons') ? $this->lessons->count() : null),
            'instructor' => $this->instructor ? [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'avatar_url' => $this->instructor->avatar ? asset('storage/'.$this->instructor->avatar) : null,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
