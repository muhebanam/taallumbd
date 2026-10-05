<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SslCommerzGateway implements PaymentGateway
{
    protected string $storeId;

    protected string $storePassword;

    protected string $mode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->storeId = (string) config('payments.gateways.sslcommerz.store_id', '');
        $this->storePassword = (string) config('payments.gateways.sslcommerz.store_password', '');
        $this->mode = (string) config('payments.gateways.sslcommerz.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'live'
            ? 'https://securepay.sslcommerz.com'
            : 'https://sandbox.sslcommerz.com';
    }

    public function initiate(Order $order): array
    {
        $tranId = 'SSL-'.strtoupper(Str::random(12));

        $payload = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => number_format((float) $order->final_payable_amount, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $tranId,
            'success_url' => route('payments.callback', ['gateway' => 'sslcommerz', 'order' => $order->id, 'status' => 'success']),
            'fail_url' => route('payments.callback', ['gateway' => 'sslcommerz', 'order' => $order->id, 'status' => 'fail']),
            'cancel_url' => route('payments.callback', ['gateway' => 'sslcommerz', 'order' => $order->id, 'status' => 'cancel']),
            'ipn_url' => route('payments.ipn', ['gateway' => 'sslcommerz']),
            'cus_name' => $order->user->name,
            'cus_email' => $order->user->email,
            'cus_phone' => $order->user->phone ?: '01700000000',
            'cus_add1' => 'Dhaka, Bangladesh',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'shipping_method' => 'NO',
            'product_name' => $order->course->title,
            'product_category' => 'Online Education',
            'product_profile' => 'non-physical-goods',
            'value_a' => (string) $order->id,
        ];

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'type' => 'initiate',
            'gateway_ref' => $tranId,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'initiated',
            'payload' => ['mode' => $this->mode, 'tran_id' => $tranId],
            'ip_address' => request()->ip(),
        ]);

        $order->update(['payment_method' => 'sslcommerz']);

        $response = Http::asForm()->timeout(15)->post("{$this->baseUrl}/gwprocess/v4/api.php", $payload);

        if (! $response->successful()) {
            throw new RuntimeException('SSLCommerz গেটওয়ে সংযোগে ত্রুটি ঘটেছে: '.$response->status());
        }

        $result = $response->json();

        if (isset($result['status']) && $result['status'] === 'SUCCESS' && ! empty($result['GatewayPageURL'])) {
            return [
                'success' => true,
                'gateway' => 'sslcommerz',
                'redirect_url' => $result['GatewayPageURL'],
                'sessionkey' => $result['sessionkey'] ?? null,
                'tran_id' => $tranId,
            ];
        }

        $errorMessage = $result['failedreason'] ?? 'SSLCommerz সেশন তৈরি করা সম্ভব হয়নি।';
        Log::error('SSLCommerz Init Failed: '.$errorMessage, ['response' => $result]);

        return [
            'success' => false,
            'message' => $errorMessage,
        ];
    }

    public function submitManualProof(Order $order, array $data): array
    {
        return (new ManualGateway)->submitManualProof($order, $data);
    }

    public function handleCallback(Request $request, Order $order): array
    {
        $status = strtoupper((string) $request->input('status', ''));
        $valId = (string) $request->input('val_id', '');

        if ($status === 'FAILED') {
            return [
                'success' => false,
                'message' => 'পেমেন্ট ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ];
        }

        if ($status === 'CANCELLED') {
            return [
                'success' => false,
                'message' => 'পেমেন্ট বাতিল করা হয়েছে।',
            ];
        }

        if (empty($valId)) {
            return [
                'success' => false,
                'message' => 'অবৈধ রেসপন্স: ভ্যালিডেশন আইডি পাওয়া যায়নি।',
            ];
        }

        return $this->validateAndConfirm($order, $valId);
    }

    public function handleIpn(Request $request): array
    {
        $valId = (string) $request->input('val_id', '');
        $orderId = (int) $request->input('value_a', 0);
        $tranId = (string) $request->input('tran_id', '');

        if (empty($valId)) {
            return [
                'success' => false,
                'message' => 'Missing val_id in IPN notification.',
            ];
        }

        $order = Order::find($orderId);
        if (! $order && $tranId) {
            $tx = PaymentTransaction::where('gateway_ref', $tranId)->first();
            $order = $tx ? $tx->order : null;
        }

        if (! $order) {
            return [
                'success' => false,
                'message' => 'Order not found for given IPN reference.',
            ];
        }

        // Idempotency: if already paid, return success immediately
        if ($order->status === 'paid') {
            return [
                'success' => true,
                'message' => 'Order is already marked as paid.',
            ];
        }

        return $this->validateAndConfirm($order, $valId);
    }

    public function verify(Order $order, ?array $payload = null): bool
    {
        $valId = $payload['val_id'] ?? null;

        if (! $valId) {
            // Find recent transaction ref
            $lastTx = $order->paymentTransactions()->where('gateway', 'sslcommerz')->latest()->first();
            $valId = $lastTx?->gateway_ref;
        }

        if (! $valId) {
            return false;
        }

        $res = $this->validateAndConfirm($order, $valId);

        return ! empty($res['success']);
    }

    public function refund(Order $order, ?string $reason = null): bool
    {
        $payment = Payment::where('order_id', $order->id)
            ->where('payment_method', 'sslcommerz')
            ->where('status', 'success')
            ->first();

        $bankTranId = $payment?->transaction_id;

        $response = Http::asForm()->timeout(15)->post("{$this->baseUrl}/validator/api/merchantTransIDvalidationAPI.php", [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'bank_tran_id' => $bankTranId,
            'refund_amount' => number_format((float) $order->final_payable_amount, 2, '.', ''),
            'refund_remarks' => $reason ?: 'Student requested refund',
            'format' => 'json',
            'v' => 1,
        ]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'type' => 'refund',
            'gateway_ref' => $bankTranId,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'refunded',
            'payload' => [
                'reason' => $reason,
                'response' => $response->json(),
            ],
            'ip_address' => request()->ip(),
        ]);

        return true;
    }

    /**
     * Server-side SSLCommerz validator API query to prevent client-side redirect tampering.
     */
    protected function validateAndConfirm(Order $order, string $valId): array
    {
        $validatorUrl = "{$this->baseUrl}/validator/api/validationserverAPI.php";

        $response = Http::timeout(15)->get($validatorUrl, [
            'val_id' => $valId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json',
            'v' => 1,
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'SSLCommerz ভ্যালিডেশন সার্ভারে যোগাযোগ সম্ভব হয়নি।',
            ];
        }

        $valData = $response->json();
        $status = strtoupper((string) ($valData['status'] ?? ''));

        if (! in_array($status, ['VALID', 'VALIDATED'])) {
            return [
                'success' => false,
                'message' => 'পেমেন্ট ভ্যালিডেশন ব্যর্থ হয়েছে। স্ট্যাটাস: '.$status,
            ];
        }

        // Amount verification
        $paidAmount = (float) ($valData['amount'] ?? 0);
        $expectedAmount = (float) $order->final_payable_amount;

        if (abs($paidAmount - $expectedAmount) >= 0.01) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'sslcommerz',
                'type' => 'amount_mismatch',
                'gateway_ref' => $valId,
                'amount' => $paidAmount,
                'currency' => $valData['currency'] ?? 'BDT',
                'status' => 'tampered',
                'payload' => [
                    'expected' => $expectedAmount,
                    'paid' => $paidAmount,
                    'val_data' => $valData,
                ],
                'ip_address' => request()->ip(),
            ]);

            throw new RuntimeException('পেমেন্টের পরিমাণ সঠিক নয় (অ্যামাউন্ট গরমিল সনাক্ত হয়েছে)।');
        }

        // Currency verification
        if (strtoupper((string) ($valData['currency'] ?? '')) !== 'BDT') {
            throw new RuntimeException('মুদ্রা অসংলগ্ন (শুধুমাত্র BDT গ্রহণযোগ্য)।');
        }

        $trxId = (string) ($valData['bank_tran_id'] ?? $valId);
        $cardIssuer = $valData['card_issuer'] ?? ($valData['card_type'] ?? 'SSLCommerz');

        app(PaymentService::class)->confirm(
            $order,
            'sslcommerz',
            $trxId,
            $valData['card_no'] ?? null,
            $valData
        );

        return [
            'success' => true,
            'transaction_id' => $trxId,
            'message' => 'SSLCommerz পেমেন্ট সফলভাবে যাচাই ও নিশ্চিত করা হয়েছে!',
        ];
    }
}
