<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id',
        'identifier_searched',
        'ip_address',
        'user_agent',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }
}
