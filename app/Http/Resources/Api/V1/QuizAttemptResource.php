<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
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
            'quiz_id' => $this->quiz_id,
            'user_id' => $this->user_id,
            'score' => (float) $this->score,
            'status' => $this->status,
            'is_passed' => $this->status === 'passed',
            'passed' => $this->status === 'passed',
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
