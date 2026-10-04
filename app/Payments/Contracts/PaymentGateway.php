<?php

namespace App\Payments\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Initiate payment process for the order.
     */
    public function initiate(Order $order): array;

    /**
     * Submit manual payment proof (TrxID, phone, screenshot).
     */
    public function submitManualProof(Order $order, array $data): array;

    /**
     * Verify payment status with the gateway or rules.
     */
    public function verify(Order $order, ?array $payload = null): bool;

    /**
     * Refund payment for the order.
     */
    public function refund(Order $order, ?string $reason = null): bool;
}
