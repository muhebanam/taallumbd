<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HadithChapter extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'number', 'title_arabic', 'title_bangla'];

    public function book()
    {
        return $this->belongsTo(HadithBook::class, 'book_id');
    }

    public function hadiths()
    {
        return $this->hasMany(Hadith::class, 'chapter_id')->orderBy('number');
    }
}
