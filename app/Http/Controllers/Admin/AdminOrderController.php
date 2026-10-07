<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController extends Controller
{
    public function __construct(
        protected RefundService $refundService
    ) {}

    /**
     * Display a listing of orders for administration.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $orders = Order::with(['user:id,name,email,phone', 'course:id,title,slug'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('course', fn ($c) => $c->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Process an admin refund on a paid order.
     */
    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->refundService->processRefund(
                $order,
                $request->input('reason'),
                $request->user()
            );

            return back()->with('success', "অর্ডার #{$order->id} সফলভাবে রিফান্ড করা হয়েছে।");
        } catch (\Throwable $e) {
            return back()->with('error', 'রিফান্ড প্রক্রিয়ায় ত্রুটি: ' . $e->getMessage());
        }
    }
}
