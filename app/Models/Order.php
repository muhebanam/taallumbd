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
        'amount',
        'coupon_code',
        'discount_amount',
        'status',
        'payment_method',
        'sender_phone',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFinalPayableAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) ($this->discount_amount ?? 0));
    }
}
