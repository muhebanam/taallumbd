<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isFollowing = false;

        if ($user && $this->relationLoaded('followers')) {
            $isFollowing = $this->followers->contains($user->id);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'designation' => $this->designation,
            'headline' => $this->headline,
            'short_bio' => $this->short_bio,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'cover_photo_url' => $this->cover_photo_url,
            'location' => $this->location,
            'email' => $this->show_email ? $this->email : null,
            'phone' => $this->show_phone ? $this->phone : null,
            'website' => $this->website,
            'specialties' => $this->specialties,
            'qualifications' => $this->qualifications,
            'is_verified' => (bool) $this->is_verified,
            'featured' => (bool) $this->featured,
            'consultation_enabled' => (bool) $this->consultation_enabled,
            'consultation_fee' => $this->consultation_fee,
            'consultation_fee_bdt' => $this->consultation_fee_bdt,
            'consultation_session_duration' => $this->consultation_session_duration,
            'is_following' => $isFollowing,
            'followers_count' => $this->followers_count ?? ($this->relationLoaded('followers') ? $this->followers->count() : null),
            'courses_count' => $this->courses_count ?? ($this->relationLoaded('courses') ? $this->courses->count() : null),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}
