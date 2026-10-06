<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarSessionRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_class_id',
        'user_id',
        'order_id',
        'fee_paid',
        'status', // registered, attended, cancelled
        'booking_notes',
        'attended_at',
    ];

    protected $casts = [
        'fee_paid' => 'decimal:2',
        'attended_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LiveClass::class, 'live_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
