<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
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
            'certificate_code' => $this->certificate_code,
            'course' => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
                'slug' => $this->course?->slug,
            ],
            'student_name' => $this->user?->name,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'verification_url' => url('/verify/'.$this->certificate_code),
            'status' => $this->status ?? 'valid',
            'is_revoked' => (bool) ($this->is_revoked ?? false),
            'revoked_reason' => $this->revoked_reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
