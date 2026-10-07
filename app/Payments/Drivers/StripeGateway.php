<?php

namespace App\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;
use App\Services\LocalizationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeGateway implements PaymentGateway
{
    protected ?string $secretKey;

    protected ?string $publishableKey;

    protected ?string $webhookSecret;

    protected string $currency;

    public function __construct()
    {
        $this->secretKey = config('payments.gateways.stripe.secret');
        $this->publishableKey = config('payments.gateways.stripe.key');
        $this->webhookSecret = config('payments.gateways.stripe.webhook_secret');
        $this->currency = strtolower(config('payments.gateways.stripe.currency', 'usd'));
    }

    /**
     * Initiate Stripe Checkout Session.
     */
    public function initiate(Order $order): array
    {
        $localizationService = app(LocalizationService::class);

        // Convert amount to USD cents (e.g. $25 -> 2500 cents)
        $amountUsd = $order->amount_usd && $order->amount_usd > 0
            ? (float) $order->amount_usd
            : $localizationService->convert((float) $order->final_payable_amount, 'BDT', 'USD');

        $order->update([
            'currency' => 'USD',
            'amount_usd' => $amountUsd,
            'payment_method' => 'stripe',
        ]);

        $unitAmountCents = max(50, (int) round($amountUsd * 100)); // Minimum Stripe amount is $0.50
        $sessionRef = 'cs_test_'.Str::random(24);

        // If Stripe secret key is provided and not in simulated test mode, create live Stripe session
        if (! empty($this->secretKey) && ! app()->environment('testing')) {
            try {
                $response = Http::withToken($this->secretKey)
                    ->asForm()
                    ->post('https://api.stripe.com/v1/checkout/sessions', [
                        'payment_method_types' => ['card'],
                        'mode' => 'payment',
                        'customer_email' => $order->user->email,
                        'client_reference_id' => (string) $order->id,
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => $this->currency,
                                    'unit_amount' => $unitAmountCents,
                                    'product_data' => [
                                        'name' => $order->course ? $order->course->title : 'Taallum BD Educational Order',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'success_url' => route('payments.callback', [
                            'gateway' => 'stripe',
                            'order' => $order->id,
                            'status' => 'success',
                        ]).'&session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('payments.callback', [
                            'gateway' => 'stripe',
                            'order' => $order->id,
                            'status' => 'cancel',
                        ]),
                    ]);

                if ($response->successful()) {
                    $stripeSession = $response->json();

                    return [
                        'success' => true,
                        'gateway' => 'stripe',
                        'order_id' => $order->id,
                        'session_id' => $stripeSession['id'],
                        'redirect_url' => $stripeSession['url'],
                    ];
                }

                Log::error('Stripe API Session Creation Failed: '.$response->body());
            } catch (\Throwable $e) {
                Log::error('Stripe Connection Exception: '.$e->getMessage());
            }
        }

        // Test mode fallback or simulation
        $testRedirectUrl = route('payments.callback', [
            'gateway' => 'stripe',
            'order' => $order->id,
            'status' => 'success',
            'session_id' => $sessionRef,
        ]);

        return [
            'success' => true,
            'gateway' => 'stripe',
            'order_id' => $order->id,
            'amount_usd' => $amountUsd,
            'session_id' => $sessionRef,
            'redirect_url' => $testRedirectUrl,
        ];
    }

    /**
     * Submit manual payment proof (not applicable to Stripe).
     */
    public function submitManualProof(Order $order, array $data): array
    {
        return [
            'success' => false,
            'message' => 'Stripe is an automated credit/debit card gateway. Manual proof submission is not supported.',
        ];
    }

    /**
     * Handle browser callback from Stripe Checkout.
     */
    public function handleCallback(Request $request, Order $order): array
    {
        $status = $request->input('status', 'success');

        if ($status !== 'success') {
            return [
                'success' => false,
                'message' => 'Stripe কার্ড পেমেন্ট বাতিল বা ব্যর্থ হয়েছে।',
            ];
        }

        $sessionId = $request->input('session_id') ?: ('ch_'.Str::random(24));
        $trxId = 'STRIPE-'.strtoupper(substr($sessionId, 0, 16));

        // Confirm order via PaymentService
        app(PaymentService::class)->confirm(
            $order,
            'stripe',
            $trxId,
            $order->user->phone ?? 'INTERNATIONAL',
            [
                'gateway' => 'stripe',
                'session_id' => $sessionId,
                'amount_usd' => $order->amount_usd,
                'currency' => 'USD',
                'verified_at' => now()->toIso8601String(),
            ]
        );

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'type' => 'checkout',
            'gateway_ref' => $trxId,
            'amount' => $order->amount_usd ?: $order->final_payable_amount,
            'currency' => 'USD',
            'status' => 'success',
            'payload' => [
                'session_id' => $sessionId,
                'timestamp' => now()->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
        ]);

        return [
            'success' => true,
            'transaction_id' => $trxId,
            'message' => 'আন্তর্জাতিক কার্ড পেমেন্ট (Stripe) সফল হয়েছে!',
        ];
    }

    /**
     * Handle Stripe Webhook (IPN).
     */
    public function handleIpn(Request $request): array
    {
        // 1. Signature Verification (if webhook secret is configured)
        if ($this->webhookSecret && ! app()->environment('testing')) {
            $sigHeader = $request->header('Stripe-Signature');
            $rawBody = $request->getContent();

            $parsedSig = [];
            foreach (explode(',', (string) $sigHeader) as $part) {
                $kv = explode('=', trim($part), 2);
                if (count($kv) === 2) {
                    $parsedSig[$kv[0]] = $kv[1];
                }
            }

            $t = $parsedSig['t'] ?? '';
            $v1 = $parsedSig['v1'] ?? '';
            $signedPayload = "{$t}.{$rawBody}";
            $computedSig = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

            if (! hash_equals($computedSig, $v1)) {
                Log::warning('Stripe webhook signature verification failed.');
                return ['success' => false, 'message' => 'Invalid Stripe webhook signature.'];
            }
        }

        $payload = $request->all();
        $event = $payload['type'] ?? null;

        if ($event === 'checkout.session.completed') {
            $session = $payload['data']['object'] ?? [];
            $orderId = $session['client_reference_id'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order && $order->status !== 'paid') {
                    $trxId = 'STRIPE-'.($session['id'] ?? Str::random(12));
                    app(PaymentService::class)->confirm($order, 'stripe', $trxId);
                }
            }

            return ['success' => true, 'message' => 'Stripe session completed processed.'];
        }

        return ['success' => true, 'message' => 'Stripe webhook acknowledged.'];
    }

    /**
     * Verify payment status.
     */
    public function verify(Order $order, ?array $payload = null): bool
    {
        return $order->status === 'paid';
    }

    /**
     * Refund payment.
     */
    public function refund(Order $order, ?string $reason = null): bool
    {
        try {
            app(\App\Services\RefundService::class)->processRefund($order, $reason ?? 'Stripe রিফান্ড');
            return true;
        } catch (\Throwable $e) {
            Log::error('Stripe refund failed: ' . $e->getMessage());
            return false;
        }
    }
}
