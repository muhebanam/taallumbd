<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyahTafsir extends Model
{
    use HasFactory;

    protected $table = 'ayah_tafsir';

    protected $fillable = ['ayah_id', 'source', 'text'];

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }
}
