<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar_url' => $this->avatar ? (str_starts_with($this->avatar, 'http') ? $this->avatar : asset('storage/'.$this->avatar)) : null,
            'is_instructor' => $this->role === 'instructor',
            'is_admin' => $this->role === 'admin',
            'is_student' => $this->role === 'student',
            'email_verified' => $this->email_verified_at !== null,
            'referral_code' => $this->referral_code,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
