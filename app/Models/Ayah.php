<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Ayah extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'surah_id', 'number', 'number_in_quran', 'text_uthmani', 'juz', 'sajdah',
    ];

    protected $casts = [
        'sajdah' => 'boolean',
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function translations()
    {
        return $this->hasMany(AyahTranslation::class);
    }

    public function banglaTranslation()
    {
        return $this->hasOne(AyahTranslation::class)->where('language', 'bn');
    }

    public function tafsirs()
    {
        return $this->hasMany(AyahTafsir::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'text_uthmani' => $this->text_uthmani,
        ];
    }
}
