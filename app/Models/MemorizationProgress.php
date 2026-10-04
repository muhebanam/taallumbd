<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorizationProgress extends Model
{
    use HasFactory;

    protected $table = 'memorization_progress';

    protected $fillable = [
        'user_id', 'surah_id', 'ayah_from', 'ayah_to', 'status', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}
