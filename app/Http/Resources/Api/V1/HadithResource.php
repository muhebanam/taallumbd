<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HadithResource extends JsonResource
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
            'book_id' => $this->book_id,
            'book_slug' => $this->book?->slug,
            'book_name' => $this->book?->name_bangla,
            'chapter_id' => $this->chapter_id,
            'chapter_name' => $this->chapter?->name_bangla,
            'number' => (int) $this->number,
            'hadith_number_in_book' => $this->hadith_number_in_book,
            'text_arabic' => $this->text_arabic,
            'text_bangla' => $this->text_bangla,
            'narrator' => $this->narrator,
            'grade' => $this->grade,
            'grade_by' => $this->grade_by,
            'explanation' => $this->explanation,
        ];
    }
}
