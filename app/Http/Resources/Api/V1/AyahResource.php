<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AyahResource extends JsonResource
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
            'surah_id' => $this->surah_id,
            'number' => (int) $this->number,
            'number_in_quran' => (int) $this->number_in_quran,
            'text_uthmani' => $this->text_uthmani,
            'juz' => (int) $this->juz,
            'sajdah' => (bool) $this->sajdah,
            'translation_bangla' => $this->banglaTranslation?->translation_text,
            'audio_url' => sprintf('https://cdn.islamic.network/quran/audio/128/ar.alafasy/%d.mp3', $this->number_in_quran),
        ];
    }
}
