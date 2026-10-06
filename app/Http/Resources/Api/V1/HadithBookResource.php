<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HadithBookResource extends JsonResource
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
            'name_arabic' => $this->name_arabic,
            'name_bangla' => $this->name_bangla,
            'name_english' => $this->name_english,
            'slug' => $this->slug,
            'author' => $this->author,
            'total_hadith' => (int) ($this->total_hadith ?? ($this->relationLoaded('hadiths') ? $this->hadiths->count() : 0)),
            'description' => $this->description,
            'chapters' => $this->whenLoaded('chapters', fn () => $this->chapters->map(fn ($c) => [
                'id' => $c->id,
                'number' => (int) $c->number,
                'name_arabic' => $c->name_arabic,
                'name_bangla' => $c->name_bangla,
                'name_english' => $c->name_english,
            ])),
        ];
    }
}
