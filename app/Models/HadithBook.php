<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HadithBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_arabic', 'name_bangla', 'name_english', 'slug', 'author', 'total_hadith', 'description'
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function chapters()
    {
        return $this->hasMany(HadithChapter::class, 'book_id')->orderBy('number');
    }

    public function hadiths()
    {
        return $this->hasMany(Hadith::class, 'book_id')->orderBy('number');
    }
}
