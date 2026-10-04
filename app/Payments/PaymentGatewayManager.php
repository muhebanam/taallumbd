<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\Drivers\ManualGateway;
use App\Payments\Drivers\MockGateway;
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
        $name = $name ?: config('payments.default', 'manual');

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Create a driver instance.
     */
    protected function createDriver(string $name): PaymentGateway
    {
        return match ($name) {
            'manual', 'bkash', 'nagad', 'rocket' => new ManualGateway,
            'mock' => new MockGateway,
            default => throw new InvalidArgumentException("Payment gateway driver [{$name}] is not supported."),
        };
    }
}
