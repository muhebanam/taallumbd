<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'live_class_id',
        'order_type',
        'amount',
        'currency',
        'amount_usd',
        'coupon_code',
        'discount_amount',
        'status',
        'payment_method',
        'sender_phone',
        'transaction_id',
        'screenshot_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_usd' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification' || ($this->status === 'pending' && ! empty($this->transaction_id));
    }

    public function getFinalPayableAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) ($this->discount_amount ?? 0));
    }
}
