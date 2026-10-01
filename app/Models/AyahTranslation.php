<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyahTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['ayah_id', 'language', 'translator', 'text'];

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }
}
