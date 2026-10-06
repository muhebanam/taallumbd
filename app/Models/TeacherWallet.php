<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'user_id',
        'balance',
        'pending_balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'integer',
        'pending_balance' => 'integer',
    ];

    protected $appends = [
        'balance_bdt',
        'pending_balance_bdt',
        'total_balance_bdt',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id')->latest();
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class, 'wallet_id')->latest();
    }

    /**
     * Get available balance formatted in BDT decimal.
     */
    public function getBalanceBdtAttribute(): float
    {
        return (float) bcdiv((string) $this->balance, '100', 2);
    }

    /**
     * Get pending balance formatted in BDT decimal.
     */
    public function getPendingBalanceBdtAttribute(): float
    {
        return (float) bcdiv((string) $this->pending_balance, '100', 2);
    }

    /**
     * Get total combined balance formatted in BDT decimal.
     */
    public function getTotalBalanceBdtAttribute(): float
    {
        $total = bcadd((string) $this->balance, (string) $this->pending_balance, 0);

        return (float) bcdiv($total, '100', 2);
    }
}
