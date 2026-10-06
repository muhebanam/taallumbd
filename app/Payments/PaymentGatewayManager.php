<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\Drivers\BkashGateway;
use App\Payments\Drivers\ManualGateway;
use App\Payments\Drivers\MockGateway;
use App\Payments\Drivers\SslCommerzGateway;
use App\Payments\Drivers\StripeGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * Cache of resolved gateway instances.
     */
    protected array $drivers = [];

    /**
     * Get a payment gateway driver instance.
     */
    public function driver(?string $name = null): PaymentGateway
    {
        $name = strtolower($name ?: config('payments.default', 'manual'));

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Check if a given payment gateway is enabled.
     */
    public function isGatewayEnabled(string $name): bool
    {
        $name = strtolower($name);

        if ($name === 'manual') {
            return (bool) config('payments.gateways.manual.enabled', true);
        }

        if ($name === 'sslcommerz') {
            return (bool) config('payments.gateways.sslcommerz.enabled', false);
        }

        if ($name === 'bkash' || $name === 'bkash_tokenized') {
            return (bool) config('payments.gateways.bkash.enabled', false);
        }

        if ($name === 'stripe') {
            return (bool) config('payments.gateways.stripe.enabled', true);
        }

        if ($name === 'mock') {
            return (bool) (app()->environment('local', 'testing') && config('payments.mock_enabled', false));
        }

        return false;
    }

    /**
     * Get list of enabled gateways for checkout UI.
     */
    public function getAvailableGateways(): array
    {
        $gateways = [];

        // Automated bKash Tokenized Checkout
        if ($this->isGatewayEnabled('bkash')) {
            $gateways[] = [
                'id' => 'bkash',
                'name' => 'বিকাশ (bKash Auto Checkout)',
                'type' => 'automated',
                'badge' => 'তাৎক্ষণিক সক্রিয়',
                'icon' => 'bkash',
            ];
        }

        // Automated SSLCommerz Checkout
        if ($this->isGatewayEnabled('sslcommerz')) {
            $gateways[] = [
                'id' => 'sslcommerz',
                'name' => 'কার্ড / ইন্টারনেট ব্যাংকিং (SSLCommerz)',
                'type' => 'automated',
                'badge' => 'কার্ড, নেট ব্যাংকিং, মোবাইল ব্যাংকিং',
                'icon' => 'sslcommerz',
            ];
        }

        // International Stripe Checkout
        if ($this->isGatewayEnabled('stripe')) {
            $gateways[] = [
                'id' => 'stripe',
                'name' => 'আন্তর্জাতিক ক্রেডিট / ডেবিট কার্ড (Stripe)',
                'type' => 'automated',
                'badge' => 'USD / Global Card',
                'icon' => 'stripe',
            ];
        }

        // Manual Payment (bKash, Nagad, Rocket) - Always active default
        if ($this->isGatewayEnabled('manual')) {
            $gateways[] = [
                'id' => 'manual',
                'name' => 'ম্যানুয়াল পেমেন্ট (bKash / Nagad / Rocket)',
                'type' => 'manual',
                'badge' => 'অ্যাডমিন ভেরিফিকেশন',
                'icon' => 'manual',
            ];
        }

        // Mock payment in dev/test
        if ($this->isGatewayEnabled('mock')) {
            $gateways[] = [
                'id' => 'mock',
                'name' => 'মক পেমেন্ট (Dev Only)',
                'type' => 'mock',
                'badge' => 'ডেভেলপমেন্ট টেস্ট',
                'icon' => 'mock',
            ];
        }

        return $gateways;
    }

    /**
     * Create a driver instance.
     */
    protected function createDriver(string $name): PaymentGateway
    {
        return match ($name) {
            'sslcommerz' => new SslCommerzGateway,
            'bkash', 'bkash_tokenized' => new BkashGateway,
            'stripe' => new StripeGateway,
            'manual', 'nagad', 'rocket', 'manual_bkash', 'manual_nagad', 'manual_rocket' => new ManualGateway,
            'mock' => new MockGateway,
            default => throw new InvalidArgumentException("Payment gateway driver [{$name}] is not supported."),
        };
    }
}
