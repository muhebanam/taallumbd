<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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

        if ($user) {
            $isEnrolled = $user->isEnrolledIn($this->section?->course_id ?? $this->course_id)
                || $user->id === ($this->section?->course?->instructor_id ?? null)
                || $user->isAdmin();
        }

        $isAccessible = $this->is_free || $isEnrolled;

        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'duration' => $this->duration,
            'duration_minutes' => $this->duration_minutes ?? ($this->duration ? round($this->duration / 60) : 0),
            'sort_order' => $this->sort_order,
            'is_free_preview' => (bool) $this->is_free,
            'is_accessible' => $isAccessible,
            'video_provider' => $this->video_provider,
            // Full content and video URL only if accessible
            'content' => $isAccessible ? $this->content : null,
            'video_url' => $isAccessible ? $this->video_url : null,
            'video_stream_url' => $isAccessible ? $this->video_url : null,
            'video_storage_path' => $isAccessible && $this->video_storage_path ? asset('storage/'.$this->video_storage_path) : null,
            'attachments' => $isAccessible ? $this->attachments : null,
            'quiz' => $this->whenLoaded('quiz', fn () => new QuizResource($this->quiz)),
        ];
    }
}
