<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Payments\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class ManualGateway implements PaymentGateway
{
    public function initiate(Order $order): array
    {
        $methods = config('payments.gateways.manual.methods', []);

        // Override from dynamic settings if configured
        foreach ($methods as $key => $conf) {
            $dbNumber = Setting::get("payment_manual_{$key}_number");
            if ($dbNumber) {
                $methods[$key]['number'] = $dbNumber;
            }
            $dbType = Setting::get("payment_manual_{$key}_type");
            if ($dbType) {
                $methods[$key]['type'] = $dbType;
            }
        }

        return [
            'gateway' => 'manual',
            'order_id' => $order->id,
            'amount' => $order->final_payable_amount,
            'methods' => $methods,
        ];
    }

    public function submitManualProof(Order $order, array $data): array
    {
        $method = $data['payment_method'] ?? 'bkash';
        $transactionId = strtoupper(trim($data['transaction_id'] ?? ''));
        $senderPhone = trim($data['sender_phone'] ?? '');

        // Prevent duplicate transaction ID usage on already paid orders
        $exists = Payment::where('transaction_id', $transactionId)
            ->where('status', 'success')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'transaction_id' => 'এই ট্রানজ্যাকশন আইডি (TrxID) দিয়ে ইতোমধ্যে একটি পেমেন্ট যাচাই সম্পন্ন হয়েছে। নতুন ট্রানজ্যাকশন আইডি প্রদান করুন।',
            ]);
        }

        // Check if already used in a pending verification order by another user
        $pendingExists = Order::where('transaction_id', $transactionId)
            ->where('id', '!=', $order->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'transaction_id' => 'এই ট্রানজ্যাকশন আইডিটি ইতিমধ্যে অন্য একটি অপেক্ষমাণ অর্ডারে জমা দেওয়া হয়েছে।',
            ]);
        }

        $order->update([
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'sender_phone' => $senderPhone,
            'status' => 'pending', // remains pending until admin moderation verifies
        ]);

        // Log payment transaction
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $method,
            'type' => 'manual_submit',
            'gateway_ref' => $transactionId,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'pending_verification',
            'payload' => [
                'sender_phone' => $senderPhone,
                'method' => $method,
                'submitted_at' => now()->toIso8601String(),
                'ip' => Request::ip(),
            ],
            'ip_address' => Request::ip(),
        ]);

        return [
            'success' => true,
            'status' => 'pending_verification',
            'message' => 'পেমেন্ট তথ্য সফলভাবে জমা দেওয়া হয়েছে। অ্যাডমিন পর্যালোচনার পর কোর্সটি সক্রিয় করা হবে।',
        ];
    }

    public function handleCallback(\Illuminate\Http\Request $request, Order $order): array
    {
        return [
            'success' => false,
            'message' => 'Manual gateway does not support automated browser callbacks.',
        ];
    }

    public function handleIpn(\Illuminate\Http\Request $request): array
    {
        return [
            'success' => false,
            'message' => 'Manual gateway does not support IPN notifications.',
        ];
    }

    public function verify(Order $order, ?array $payload = null): bool
    {
        return $order->transaction_id !== null && $order->status === 'paid';
    }

    public function refund(Order $order, ?string $reason = null): bool
    {
        try {
            app(\App\Services\RefundService::class)->processRefund($order, $reason ?? 'ম্যানুয়াল পেমেন্ট রিফান্ড');
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Manual refund failed: ' . $e->getMessage());
            return false;
        }
    }
}
