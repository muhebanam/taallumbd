<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isEnrolled = false;
        $progressPercentage = 0;

        if ($user) {
            $isEnrolled = $user->isEnrolledIn($this->id) || $user->id === $this->instructor_id || $user->isAdmin();
            if ($isEnrolled) {
                $enrollment = $user->enrollments()->where('course_id', $this->id)->first();
                $progressPercentage = $enrollment?->progress_percentage ?? 0;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail ? (str_starts_with($this->thumbnail, 'http') ? $this->thumbnail : asset('storage/'.$this->thumbnail)) : null,
            'promo_video_url' => $this->promo_video_url,
            'price' => (float) $this->price,
            'price_bdt' => number_format((float) $this->price, 2),
            'is_free' => (bool) $this->is_free,
            'category' => $this->category,
            'level' => $this->level ?? 'all_levels',
            'status' => $this->status,
            'duration' => $this->duration,
            'requirements' => $this->requirements,
            'what_you_will_learn' => $this->what_you_will_learn,
            'target_audience' => $this->target_audience,
            'is_enrolled' => $isEnrolled,
            'user_progress_percentage' => $progressPercentage,
            'instructor' => $this->instructor ? [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'avatar_url' => $this->instructor->avatar ? asset('storage/'.$this->instructor->avatar) : null,
                'bio' => $this->instructor->teacher?->bio,
                'designation' => $this->instructor->teacher?->designation,
                'slug' => $this->instructor->teacher?->slug,
            ] : null,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'total_lessons' => $this->lessons_count ?? ($this->relationLoaded('lessons') ? $this->lessons->count() : ($this->relationLoaded('sections') ? $this->sections->sum(fn ($s) => $s->lessons->count()) : 0)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
