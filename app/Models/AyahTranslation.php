<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class AyahTranslation extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['ayah_id', 'language', 'translator', 'text'];

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
        ];
    }
}
