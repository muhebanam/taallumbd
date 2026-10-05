<?php

namespace App\Payments\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

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
     * Handle browser callback from payment gateway (redirect with validation).
     */
    public function handleCallback(Request $request, Order $order): array;

    /**
     * Handle server-to-server IPN / webhook notification.
     */
    public function handleIpn(Request $request): array;

    /**
     * Verify payment status with the gateway or server-side query.
     */
    public function verify(Order $order, ?array $payload = null): bool;

    /**
     * Refund payment for the order.
     */
    public function refund(Order $order, ?string $reason = null): bool;
}
