<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'total_marks' => (int) $this->total_marks,
            'pass_marks' => (int) $this->pass_marks,
            'questions' => $this->questions?->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
                'marks' => (int) $q->marks,
                'options' => $q->options?->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                ]),
            ]),
        ];
    }
}
