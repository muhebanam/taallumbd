<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BkashGateway implements PaymentGateway
{
    protected string $appKey;

    protected string $appSecret;

    protected string $username;

    protected string $password;

    protected string $mode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->appKey = (string) config('payments.gateways.bkash.app_key', '');
        $this->appSecret = (string) config('payments.gateways.bkash.app_secret', '');
        $this->username = (string) config('payments.gateways.bkash.username', '');
        $this->password = (string) config('payments.gateways.bkash.password', '');
        $this->mode = (string) config('payments.gateways.bkash.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'live'
            ? 'https://tokenized.pay.bka.sh/v2.0'
            : 'https://tokenized.sandbox.bka.sh/v2.0';
    }

    /**
     * Get or refresh tokenized auth token from bKash.
     */
    public function getToken(): string
    {
        $cacheKey = 'bkash_auth_token_'.md5($this->appKey.$this->mode);

        return Cache::remember($cacheKey, 3300, function () {
            $response = Http::withHeaders([
                'username' => $this->username,
                'password' => $this->password,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$this->baseUrl}/checkout/token/grant", [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('bKash টোকেন সংগ্রহে ত্রুটি: '.$response->status());
            }

            $json = $response->json();
            $token = $json['id_token'] ?? null;

            if (! $token) {
                $err = $json['statusMessage'] ?? 'bKash টোকেন রেসপন্স ব্যর্থ হয়েছে।';
                throw new RuntimeException('bKash Auth Error: '.$err);
            }

            return $token;
        });
    }

    public function initiate(Order $order): array
    {
        $token = $this->getToken();
        $invoiceNo = 'INV-'.$order->id.'-'.strtoupper(Str::random(6));

        $payload = [
            'mode' => '0011',
            'payerReference' => $order->user->phone ?: '01700000000',
            'callbackURL' => route('payments.callback', ['gateway' => 'bkash', 'order' => $order->id]),
            'amount' => number_format((float) $order->final_payable_amount, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $invoiceNo,
        ];

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'type' => 'initiate',
            'gateway_ref' => $invoiceNo,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'initiated',
            'payload' => ['invoice' => $invoiceNo, 'mode' => $this->mode],
            'ip_address' => request()->ip(),
        ]);

        $order->update(['payment_method' => 'bkash']);

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => $this->appKey,
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$this->baseUrl}/checkout/create", $payload);

        if (! $response->successful()) {
            throw new RuntimeException('bKash পেমেন্ট রিকোয়েস্টে ত্রুটি: '.$response->status());
        }

        $result = $response->json();

        if (isset($result['statusCode']) && $result['statusCode'] === '0000' && ! empty($result['bkashURL'])) {
            return [
                'success' => true,
                'gateway' => 'bkash',
                'redirect_url' => $result['bkashURL'],
                'payment_id' => $result['paymentID'] ?? null,
            ];
        }

        $msg = $result['statusMessage'] ?? 'bKash সেশন তৈরি করা সম্ভব হয়নি।';
        Log::error('bKash Init Failed: '.$msg, ['response' => $result]);

        return [
            'success' => false,
            'message' => $msg,
        ];
    }

    public function submitManualProof(Order $order, array $data): array
    {
        return (new ManualGateway)->submitManualProof($order, $data);
    }

    public function handleCallback(Request $request, Order $order): array
    {
        $status = strtolower((string) $request->input('status', ''));
        $paymentId = (string) $request->input('paymentID', '');

        if ($status === 'cancel') {
            return [
                'success' => false,
                'message' => 'বিকাশ পেমেন্ট বাতিল করা হয়েছে।',
            ];
        }

        if ($status === 'failure' || $status !== 'success') {
            return [
                'success' => false,
                'message' => 'বিকাশ পেমেন্ট সম্পন্ন হয়নি বা ব্যর্থ হয়েছে।',
            ];
        }

        if (empty($paymentId)) {
            return [
                'success' => false,
                'message' => 'অবৈধ বিকাশ পেমেন্ট রেফারেন্স (paymentID অনুপস্থিত)।',
            ];
        }

        return $this->executeAndConfirm($order, $paymentId);
    }

    public function handleIpn(Request $request): array
    {
        $paymentId = (string) $request->input('paymentID', '');
        $orderId = (int) $request->input('order_id', 0);

        if (empty($paymentId)) {
            return [
                'success' => false,
                'message' => 'Missing paymentID in bKash IPN payload.',
            ];
        }

        $order = Order::find($orderId);
        if (! $order) {
            $tx = PaymentTransaction::where('gateway_ref', $paymentId)->first();
            $order = $tx ? $tx->order : null;
        }

        if (! $order) {
            return [
                'success' => false,
                'message' => 'Order not found for given bKash payment reference.',
            ];
        }

        if ($order->status === 'paid') {
            return [
                'success' => true,
                'message' => 'Order already confirmed as paid.',
            ];
        }

        return $this->queryAndVerify($order, $paymentId);
    }

    public function verify(Order $order, ?array $payload = null): bool
    {
        $paymentId = $payload['paymentID'] ?? null;

        if (! $paymentId) {
            $tx = $order->paymentTransactions()->where('gateway', 'bkash')->latest()->first();
            $paymentId = $tx?->gateway_ref;
        }

        if (! $paymentId) {
            return false;
        }

        $res = $this->queryAndVerify($order, $paymentId);

        return ! empty($res['success']);
    }

    public function refund(Order $order, ?string $reason = null): bool
    {
        $payment = Payment::where('order_id', $order->id)
            ->where('payment_method', 'bkash')
            ->where('status', 'success')
            ->first();

        $trxId = $payment?->transaction_id;
        $raw = $payment?->raw_response ?? [];
        $paymentId = $raw['paymentID'] ?? $trxId;

        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => $this->appKey,
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$this->baseUrl}/checkout/payment/refund", [
            'paymentID' => $paymentId,
            'amount' => number_format((float) $order->final_payable_amount, 2, '.', ''),
            'trxID' => $trxId,
            'sku' => 'COURSE-'.$order->course_id,
            'reason' => $reason ?: 'Student requested course refund',
        ]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'type' => 'refund',
            'gateway_ref' => $trxId,
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
     * Execute payment on bKash backend and verify amounts server-side.
     */
    protected function executeAndConfirm(Order $order, string $paymentId): array
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => $this->appKey,
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$this->baseUrl}/checkout/execute", [
            'paymentID' => $paymentId,
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'bKash পেমেন্ট এক্সিকিউশন সার্ভারে যোগাযোগ ব্যর্থ হয়েছে।',
            ];
        }

        $result = $response->json();
        $code = (string) ($result['statusCode'] ?? '');

        if ($code !== '0000') {
            return [
                'success' => false,
                'message' => $result['statusMessage'] ?? 'bKash পেমেন্ট সম্পন্ন করা যায়নি (কোড: '.$code.')',
            ];
        }

        // Amount verification
        $paidAmount = (float) ($result['amount'] ?? 0);
        $expectedAmount = (float) $order->final_payable_amount;

        if (abs($paidAmount - $expectedAmount) >= 0.01) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'bkash',
                'type' => 'amount_mismatch',
                'gateway_ref' => $result['trxID'] ?? $paymentId,
                'amount' => $paidAmount,
                'currency' => 'BDT',
                'status' => 'tampered',
                'payload' => [
                    'expected' => $expectedAmount,
                    'paid' => $paidAmount,
                    'bkash_data' => $result,
                ],
                'ip_address' => request()->ip(),
            ]);

            throw new RuntimeException('পেমেন্টের পরিমাণ সঠিক নয় (অ্যামাউন্ট গরমিল সনাক্ত হয়েছে)।');
        }

        // Currency verification
        if (strtoupper((string) ($result['currency'] ?? '')) !== 'BDT') {
            throw new RuntimeException('মুদ্রা অসংলগ্ন (শুধুমাত্র BDT গ্রহণযোগ্য)।');
        }

        $trxId = (string) ($result['trxID'] ?? $paymentId);
        $customerPhone = $result['customerMsisdn'] ?? null;

        app(PaymentService::class)->confirm(
            $order,
            'bkash',
            $trxId,
            $customerPhone,
            $result
        );

        return [
            'success' => true,
            'transaction_id' => $trxId,
            'message' => 'বিকাশ পেমেন্ট সফলভাবে যাচাই ও নিশ্চিত করা হয়েছে!',
        ];
    }

    /**
     * Query payment status from bKash server.
     */
    protected function queryAndVerify(Order $order, string $paymentId): array
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => $this->appKey,
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$this->baseUrl}/checkout/payment/query", [
            'paymentID' => $paymentId,
        ]);

        if (! $response->successful()) {
            return ['success' => false, 'message' => 'bKash query failed.'];
        }

        $result = $response->json();
        $status = $result['transactionStatus'] ?? '';

        if (in_array(strtolower($status), ['completed', 'authorized'])) {
            $trxId = (string) ($result['trxID'] ?? $paymentId);
            $customerPhone = $result['customerMsisdn'] ?? null;

            app(PaymentService::class)->confirm(
                $order,
                'bkash',
                $trxId,
                $customerPhone,
                $result
            );

            return ['success' => true, 'transaction_id' => $trxId];
        }

        return ['success' => false, 'status' => $status];
    }

    /**
     * Refund a bKash order payment.
     */
    public function refund(Order $order, ?string $reason = null): bool
    {
        try {
            app(\App\Services\RefundService::class)->processRefund($order, $reason ?? 'bKash রিফান্ড');
            return true;
        } catch (\Throwable $e) {
            Log::error('bKash refund failed: ' . $e->getMessage());
            return false;
        }
    }
}
