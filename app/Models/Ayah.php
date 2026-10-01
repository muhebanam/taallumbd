<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    use HasFactory;

    protected $fillable = [
        'surah_id', 'number', 'number_in_quran', 'text_uthmani', 'juz', 'sajdah'
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
}
