<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'user_id', 'course_id', 'certificate_no', 'issued_at', 'file_path',
        'revoked_at', 'revoked_reason', 'revoked_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function verifications()
    {
        return $this->hasMany(CertificateVerification::class);
    }

    protected $appends = ['verification_url', 'qr_code_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cert) {
            if (empty($cert->uuid)) {
                $cert->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/'.($this->uuid ?: $this->certificate_no));
    }

    public function getQrCodeUrlAttribute(): string
    {
        $verifyUrl = urlencode($this->verification_url);

        return "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={$verifyUrl}&color=10-37-38&bgcolor=ffffff";
    }
}
