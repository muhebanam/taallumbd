<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Hadith extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'book_id', 'chapter_id', 'number', 'hadith_number_in_book',
        'text_arabic', 'text_bangla', 'narrator', 'grade', 'grade_by', 'explanation',
    ];

    public function book()
    {
        return $this->belongsTo(HadithBook::class, 'book_id');
    }

    public function chapter()
    {
        return $this->belongsTo(HadithChapter::class, 'chapter_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'text_arabic' => $this->text_arabic,
            'text_bangla' => $this->text_bangla,
            'narrator' => $this->narrator,
            'explanation' => strip_tags($this->explanation ?? ''),
        ];
    }
}
