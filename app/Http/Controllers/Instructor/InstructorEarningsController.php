<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use App\Services\TeacherWalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorEarningsController extends Controller
{
    public function __construct(protected TeacherWalletService $walletService) {}

    /**
     * Display instructor earnings, wallet ledger, revenue chart and payout history.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $teacher = $wallet->teacher;

        // 1. Calculate KPI Metrics in BDT
        $availableBalanceBdt = $wallet->balance_bdt;
        $pendingBalanceBdt = $wallet->pending_balance_bdt;

        // Lifetime total earnings (all credits from course orders)
        $lifetimeEarningsPoisha = (int) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', 'order')
            ->where('type', 'credit')
            ->sum('amount');
        $lifetimeEarningsBdt = (float) bcdiv((string) $lifetimeEarningsPoisha, '100', 2);

        // Total Paid Out
        $totalPaidOutPoisha = (int) PayoutRequest::where('wallet_id', $wallet->id)
            ->where('status', 'paid')
            ->sum('amount');
        $totalPaidOutBdt = (float) bcdiv((string) $totalPaidOutPoisha, '100', 2);

        // Current pending payout requests
        $pendingPayoutsPoisha = (int) PayoutRequest::where('wallet_id', $wallet->id)
            ->where('status', 'pending')
            ->sum('amount');
        $pendingPayoutsBdt = (float) bcdiv((string) $pendingPayoutsPoisha, '100', 2);

        // 2. Monthly Revenue Trend (Last 6 Months)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $monthlyEarningsRaw = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', 'order')
            ->where('type', 'credit')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw("strftime('%Y-%m', created_at) as month_key, sum(amount) as total_poisha")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->pluck('total_poisha', 'month_key');

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $poisha = (int) ($monthlyEarningsRaw[$key] ?? 0);
            $chartData[] = [
                'month_key' => $key,
                'month_label' => $month->translatedFormat('F Y'),
                'amount_bdt' => (float) bcdiv((string) $poisha, '100', 2),
            ];
        }

        // 3. Transactions Ledger with Filters
        $filterType = $request->input('type'); // credit, debit
        $filterBalanceType = $request->input('balance_type'); // available, pending

        $transactionsQuery = WalletTransaction::where('wallet_id', $wallet->id);

        if ($filterType && in_array($filterType, ['credit', 'debit'])) {
            $transactionsQuery->where('type', $filterType);
        }

        if ($filterBalanceType && in_array($filterBalanceType, ['available', 'pending'])) {
            $transactionsQuery->where('balance_type', $filterBalanceType);
        }

        $transactions = $transactionsQuery->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'balance_type' => $t->balance_type,
                'amount_bdt' => $t->amount_bdt,
                'balance_after_bdt' => $t->balance_after_bdt,
                'reference_type' => $t->reference_type,
                'description' => $t->description,
                'hold_until' => $t->hold_until?->toIso8601String(),
                'matured_at' => $t->matured_at?->toIso8601String(),
                'created_at' => $t->created_at->toIso8601String(),
                'created_at_human' => $t->created_at->diffForHumans(),
            ]);

        // 4. Payout Requests History
        $payoutRequests = PayoutRequest::where('wallet_id', $wallet->id)
            ->latest('requested_at')
            ->take(20)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'amount_bdt' => $p->amount_bdt,
                'method' => strtoupper($p->method),
                'account_info' => $p->account_info,
                'status' => $p->status,
                'transaction_reference' => $p->transaction_reference,
                'rejection_reason' => $p->rejection_reason,
                'requested_at' => $p->requested_at?->format('d M, Y h:i A'),
                'processed_at' => $p->processed_at?->format('d M, Y h:i A'),
            ]);

        // Active revenue share rate for instructor
        $rates = $this->walletService->getRevenueShareRates(null, $teacher);

        return Inertia::render('Instructor/Earnings', [
            'metrics' => [
                'available_balance_bdt' => $availableBalanceBdt,
                'pending_balance_bdt' => $pendingBalanceBdt,
                'total_balance_bdt' => $wallet->total_balance_bdt,
                'lifetime_earnings_bdt' => $lifetimeEarningsBdt,
                'total_paid_out_bdt' => $totalPaidOutBdt,
                'pending_payouts_bdt' => $pendingPayoutsBdt,
                'instructor_share_percentage' => $rates['instructor_percentage'],
                'min_payout_amount_bdt' => (float) bcdiv((string) config('wallet.min_payout_amount_poisha', 50000), '100', 2),
            ],
            'chart_data' => $chartData,
            'transactions' => $transactions,
            'payout_requests' => $payoutRequests,
            'filters' => [
                'type' => $filterType ?: 'all',
                'balance_type' => $filterBalanceType ?: 'all',
            ],
            'teacher' => [
                'id' => $teacher?->id,
                'name' => $teacher?->name ?: $user->name,
                'consultation_fee_bdt' => $teacher?->consultation_fee_bdt,
                'consultation_enabled' => (bool) $teacher?->consultation_enabled,
            ],
        ]);
    }

    /**
     * Submit payout withdrawal request.
     */
    public function requestPayout(Request $request)
    {
        $minBdt = (float) bcdiv((string) config('wallet.min_payout_amount_poisha', 50000), '100', 2);

        $request->validate([
            'amount_bdt' => "required|numeric|min:{$minBdt}",
            'method' => 'required|string|in:bkash,nagad,rocket,bank',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ], [
            'amount_bdt.min' => "সর্বনিম্ন উত্তোলনের পরিমাণ {$minBdt} টাকা।",
            'method.required' => 'উত্তোলনের মাধ্যম নির্বাচন করুন।',
            'account_number.required' => 'অ্যাকাউন্ট বা মোবাইল নম্বর প্রদান করুন।',
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $teacher = $wallet->teacher;

        $amountPoisha = (int) bcmul((string) $request->input('amount_bdt'), '100', 0);

        $accountInfo = [
            'method' => $request->input('method'),
            'account_number' => $request->input('account_number'),
            'account_holder_name' => $request->input('account_holder_name'),
            'bank_name' => $request->input('bank_name'),
            'branch_name' => $request->input('branch_name'),
            'routing_number' => $request->input('routing_number'),
        ];

        try {
            $this->walletService->requestPayout(
                teacher: $teacher,
                amountPoisha: $amountPoisha,
                method: $request->input('method'),
                accountInfo: $accountInfo,
                notes: $request->input('notes')
            );

            return back()->with('success', 'পেআউট অনুরোধটি সফলভাবে গৃহীত হয়েছে। অ্যাডমিন অনুমোদনের পর অর্থ প্রদান করা হবে।');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Printable / PDF Monthly Statement
     */
    public function statement(Request $request, int $year, int $month)
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $teacher = $wallet->teacher;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $monthCreditsPoisha = (int) $transactions->where('type', 'credit')->sum('amount');
        $monthDebitsPoisha = (int) $transactions->where('type', 'debit')->sum('amount');

        $payoutsInMonth = PayoutRequest::where('wallet_id', $wallet->id)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->get();

        return view('statements.monthly', [
            'teacher' => $teacher,
            'user' => $user,
            'wallet' => $wallet,
            'year' => $year,
            'month' => $month,
            'period_label' => $startDate->translatedFormat('F Y'),
            'transactions' => $transactions,
            'month_credits_bdt' => (float) bcdiv((string) $monthCreditsPoisha, '100', 2),
            'month_debits_bdt' => (float) bcdiv((string) $monthDebitsPoisha, '100', 2),
            'payouts' => $payoutsInMonth,
            'statement_no' => sprintf('STMT-%d%02d-%04d', $year, $month, $teacher->id),
            'generated_at' => now()->format('d M, Y h:i A'),
        ]);
    }
}
