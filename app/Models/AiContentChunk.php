<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiContentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'chunkable_type',
        'chunkable_id',
        'title',
        'content',
        'source_reference',
        'source_url',
        'embedding',
        'token_count',
    ];

    protected $casts = [
        'embedding' => 'array',
        'token_count' => 'integer',
    ];

    public function chunkable(): MorphTo
    {
        return $this->morphTo();
    }
}
