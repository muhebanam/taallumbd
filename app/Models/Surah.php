<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'name_arabic', 'name_bangla', 'name_transliteration',
        'ayah_count', 'revelation_type', 'revelation_order',
    ];

    public function ayahs()
    {
        return $this->hasMany(Ayah::class)->orderBy('number');
    }

    public function memorizationProgress()
    {
        return $this->hasMany(MemorizationProgress::class);
    }
}
