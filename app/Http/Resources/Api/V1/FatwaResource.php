<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FatwaResource extends JsonResource
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
            'title' => $this->question_title,
            'question' => $this->question_body,
            'answer' => $this->answer_body,
            'references' => $this->references,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'answered_by' => $this->mufti ? [
                'id' => $this->mufti->id,
                'name' => $this->mufti->name,
                'designation' => $this->teacher?->designation,
            ] : null,
            'views_count' => (int) $this->views_count,
            'status' => $this->status,
            'is_private' => (bool) $this->is_private,
            'answered_at' => $this->answered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
