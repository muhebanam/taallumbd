<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'status',
        'token',
        'verified_at',
        'source',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($sub) {
            if (empty($sub->token)) {
                $sub->token = Str::random(40);
            }
        });
    }

    public function isVerified(): bool
    {
        return $this->status === 'active' && $this->verified_at !== null;
    }
}
