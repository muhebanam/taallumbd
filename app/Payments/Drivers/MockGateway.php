<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Request;

class MockGateway implements PaymentGateway
{
    public function initiate(Order $order): array
    {
        return [
            'gateway' => 'mock',
            'order_id' => $order->id,
            'amount' => $order->final_payable_amount,
            'redirect_url' => route('checkout.mock', ['order' => $order->id]),
        ];
    }

    public function submitManualProof(Order $order, array $data): array
    {
        return (new ManualGateway)->submitManualProof($order, $data);
    }

    public function verify(Order $order, ?array $payload = null): bool
    {
        $transactionId = $payload['transaction_id'] ?? ('MOCK-'.strtoupper(bin2hex(random_bytes(4))));

        app(PaymentService::class)->confirm(
            $order,
            'mock',
            $transactionId,
            $payload['sender_phone'] ?? '01711111111'
        );

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'mock',
            'type' => 'verify',
            'gateway_ref' => $transactionId,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'success',
            'payload' => ['mock' => true, 'verified_at' => now()->toIso8601String()],
            'ip_address' => Request::ip(),
        ]);

        return true;
    }

    public function refund(Order $order, ?string $reason = null): bool
    {
        $order->update(['status' => 'cancelled']);

        return true;
    }
}
