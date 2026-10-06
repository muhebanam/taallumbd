<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurahResource extends JsonResource
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
            'number' => (int) $this->number,
            'name_arabic' => $this->name_arabic,
            'name_bangla' => $this->name_bangla,
            'name_transliteration' => $this->name_transliteration,
            'ayah_count' => (int) $this->ayah_count,
            'revelation_type' => $this->revelation_type,
            'revelation_order' => (int) $this->revelation_order,
            'ayahs' => AyahResource::collection($this->whenLoaded('ayahs')),
        ];
    }
}
