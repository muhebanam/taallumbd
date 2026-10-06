<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'balance_type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
        'hold_until',
        'matured_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
        'hold_until' => 'datetime',
        'matured_at' => 'datetime',
    ];

    protected $appends = [
        'amount_bdt',
        'balance_after_bdt',
    ];

    public function wallet()
    {
        return $this->belongsTo(TeacherWallet::class, 'wallet_id');
    }

    public function getAmountBdtAttribute(): float
    {
        return (float) bcdiv((string) $this->amount, '100', 2);
    }

    public function getBalanceAfterBdtAttribute(): float
    {
        return (float) bcdiv((string) $this->balance_after, '100', 2);
    }
}
