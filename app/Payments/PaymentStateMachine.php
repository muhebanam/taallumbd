<?php

namespace App\Payments;

use App\Models\Order;
use InvalidArgumentException;

class PaymentStateMachine
{
    public const STATE_INITIATED = 'initiated';
    public const STATE_PENDING = 'pending';
    public const STATE_PAID = 'paid';
    public const STATE_COMPLETED = 'completed';
    public const STATE_FAILED = 'failed';
    public const STATE_EXPIRED = 'expired';
    public const STATE_CANCELLED = 'cancelled';
    public const STATE_REFUNDED = 'refunded';

    /**
     * Strict map of allowed status transitions.
     */
    protected const ALLOWED_TRANSITIONS = [
        self::STATE_INITIATED => [
            self::STATE_PENDING,
            self::STATE_PAID,
            self::STATE_FAILED,
            self::STATE_CANCELLED,
            self::STATE_EXPIRED,
        ],
        self::STATE_PENDING => [
            self::STATE_PAID,
            self::STATE_FAILED,
            self::STATE_CANCELLED,
            self::STATE_EXPIRED,
        ],
        self::STATE_PAID => [
            self::STATE_COMPLETED,
            self::STATE_REFUNDED,
        ],
        self::STATE_COMPLETED => [
            self::STATE_REFUNDED,
        ],
        self::STATE_FAILED => [
            self::STATE_INITIATED,
            self::STATE_PENDING,
        ],
        self::STATE_EXPIRED => [
            self::STATE_INITIATED,
        ],
        self::STATE_CANCELLED => [
            self::STATE_INITIATED,
        ],
        self::STATE_REFUNDED => [], // Terminal state
    ];

    /**
     * Check whether a transition from one state to another is valid.
     */
    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    /**
     * Safely transition an Order to a target state, enforcing valid lifecycle transitions.
     *
     * @throws InvalidArgumentException
     */
    public static function transition(Order $order, string $targetState, ?string $reason = null): Order
    {
        $current = $order->status ?? self::STATE_INITIATED;

        if (! self::canTransition($current, $targetState)) {
            throw new InvalidArgumentException(
                "অবৈধ পেমেন্ট স্টেট ট্রানজিশন: '{$current}' থেকে '{$targetState}'-এ যাওয়া সম্ভব নয়।"
            );
        }

        $order->status = $targetState;
        $order->save();

        return $order;
    }
}
