<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Payments\PaymentGatewayManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile {--dry-run : Only check without confirming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile pending payment transactions older than 30 minutes via gateway verify APIs';

    /**
     * Execute the console command.
     */
    public function handle(PaymentGatewayManager $gatewayManager): int
    {
        $this->info('Starting payment reconciliation...');

        // Find orders pending older than 30 minutes with an automated gateway
        $cutoff = now()->subMinutes(30);

        $pendingOrders = Order::whereIn('status', ['pending', 'pending_verification'])
            ->where('created_at', '<=', $cutoff)
            ->where(function ($query) {
                $query->whereIn('payment_method', ['sslcommerz', 'bkash'])
                    ->orWhereHas('paymentTransactions', function ($tq) {
                        $tq->whereIn('gateway', ['sslcommerz', 'bkash']);
                    });
            })
            ->with('paymentTransactions')
            ->get();

        $this->info("Found {$pendingOrders->count()} candidate order(s) for reconciliation.");

        $reconciledCount = 0;

        foreach ($pendingOrders as $order) {
            $gateway = in_array($order->payment_method, ['sslcommerz', 'bkash'])
                ? $order->payment_method
                : $order->paymentTransactions()->whereIn('gateway', ['sslcommerz', 'bkash'])->latest()->value('gateway');

            if (! $gateway || ! $gatewayManager->isGatewayEnabled($gateway)) {
                $this->line("Skipping order #{$order->id}: gateway [{$gateway}] is disabled or not found.");

                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("[Dry Run] Would verify order #{$order->id} with [{$gateway}].");

                continue;
            }

            try {
                $driver = $gatewayManager->driver($gateway);
                $this->line("Verifying order #{$order->id} with [{$gateway}]...");

                $isVerified = $driver->verify($order);

                if ($isVerified) {
                    $reconciledCount++;
                    $this->info("Order #{$order->id} successfully reconciled and confirmed!");
                    Log::info("Payment Reconciled: Order #{$order->id} confirmed via {$gateway} verify API.");
                } else {
                    $this->comment("Order #{$order->id} was not verified by gateway.");
                }
            } catch (\Throwable $e) {
                $this->error("Error reconciling order #{$order->id}: {$e->getMessage()}");
                Log::warning("Reconciliation error for Order #{$order->id}: {$e->getMessage()}");
            }
        }

        $this->info("Reconciliation completed. {$reconciledCount} order(s) confirmed.");

        return Command::SUCCESS;
    }
}
