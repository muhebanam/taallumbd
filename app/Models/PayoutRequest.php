<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'user_id',
        'wallet_id',
        'amount',
        'method',
        'account_info',
        'status',
        'transaction_reference',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'notes',
        'requested_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'account_info' => 'encrypted:array',
        'processed_at' => 'datetime',
        'requested_at' => 'datetime',
    ];

    protected $appends = [
        'amount_bdt',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(TeacherWallet::class, 'wallet_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getAmountBdtAttribute(): float
    {
        return (float) bcdiv((string) $this->amount, '100', 2);
    }
}
