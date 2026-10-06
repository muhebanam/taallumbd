<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PayoutRequest;
use App\Models\RevenueShare;
use App\Models\Teacher;
use App\Models\TeacherWallet;
use App\Models\WalletTransaction;
use App\Services\AuditLoggerService;
use App\Services\TeacherWalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPayoutController extends Controller
{
    public function __construct(protected TeacherWalletService $walletService) {}

    /**
     * Display payouts queue, financial liabilities, and revenue share rules.
     */
    public function index(Request $request)
    {
        // 1. Platform-wide Wallet Metrics
        $totalAvailablePoisha = (int) TeacherWallet::sum('balance');
        $totalPendingPoisha = (int) TeacherWallet::sum('pending_balance');
        $totalPaidOutPoisha = (int) PayoutRequest::where('status', 'paid')->sum('amount');
        $pendingRequestsCount = PayoutRequest::where('status', 'pending')->count();
        $pendingRequestsPoisha = (int) PayoutRequest::where('status', 'pending')->sum('amount');

        // 2. Filter Payout Requests
        $status = $request->input('status', 'pending');
        $query = PayoutRequest::with(['teacher.user', 'processedBy']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payouts = $query->latest('requested_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($p) => [
                'id' => $p->id,
                'teacher' => [
                    'id' => $p->teacher_id,
                    'name' => $p->teacher?->name ?? 'শিক্ষক',
                    'email' => $p->teacher?->user?->email,
                    'slug' => $p->teacher?->slug,
                    'avatar_url' => $p->teacher?->avatar_url,
                ],
                'amount_bdt' => $p->amount_bdt,
                'method' => strtoupper($p->method),
                'account_info' => $p->account_info,
                'status' => $p->status,
                'transaction_reference' => $p->transaction_reference,
                'rejection_reason' => $p->rejection_reason,
                'notes' => $p->notes,
                'requested_at' => $p->requested_at?->format('d M, Y h:i A'),
                'processed_at' => $p->processed_at?->format('d M, Y h:i A'),
                'processed_by' => $p->processedBy?->name,
            ]);

        // 3. Revenue Share Overrides
        $revenueShares = RevenueShare::with(['course:id,title,slug', 'teacher:id,name,slug'])
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'course_id' => $r->course_id,
                'course_title' => $r->course?->title,
                'teacher_id' => $r->teacher_id,
                'teacher_name' => $r->teacher?->name,
                'instructor_share_percentage' => (float) $r->instructor_share_percentage,
                'platform_share_percentage' => (float) $r->platform_share_percentage,
                'is_active' => $r->is_active,
                'notes' => $r->notes,
            ]);

        // List of courses and teachers for setting up revenue share overrides
        $courses = Course::select('id', 'title')->orderBy('title')->get();
        $teachers = Teacher::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Payouts', [
            'metrics' => [
                'total_available_bdt' => (float) bcdiv((string) $totalAvailablePoisha, '100', 2),
                'total_pending_bdt' => (float) bcdiv((string) $totalPendingPoisha, '100', 2),
                'total_paid_out_bdt' => (float) bcdiv((string) $totalPaidOutPoisha, '100', 2),
                'pending_requests_count' => $pendingRequestsCount,
                'pending_requests_bdt' => (float) bcdiv((string) $pendingRequestsPoisha, '100', 2),
                'default_instructor_percentage' => (float) config('wallet.default_instructor_percentage', 70.00),
                'default_platform_percentage' => (float) config('wallet.default_platform_percentage', 30.00),
            ],
            'payouts' => $payouts,
            'revenue_shares' => $revenueShares,
            'courses' => $courses,
            'teachers' => $teachers,
            'current_status' => $status,
        ]);
    }

    /**
     * Approve and mark payout request as paid.
     */
    public function approve(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $this->walletService->approvePayout(
                payout: $payout,
                admin: $request->user(),
                transactionReference: $request->input('transaction_reference'),
                notes: $request->input('notes')
            );

            return back()->with('success', "পেআউট #{$payout->id} সফলভাবে পরিশোধিত হিসেবে চিহ্নিত করা হয়েছে।");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject payout request and refund amount back to teacher wallet.
     */
    public function reject(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'বাতিল করার কারণ উল্লেখ করা আবশ্যক।',
        ]);

        try {
            $this->walletService->rejectPayout(
                payout: $payout,
                admin: $request->user(),
                reason: $request->input('reason')
            );

            return back()->with('success', "পেআউট #{$payout->id} বাতিল করা হয়েছে এবং অর্থ শিক্ষকের ওয়ালেটে ফেরত দেওয়া হয়েছে।");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create or update custom revenue share override.
     */
    public function saveRevenueShare(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:revenue_shares,id',
            'course_id' => 'nullable|exists:courses,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'instructor_share_percentage' => 'required|numeric|min:0|max:100',
            'platform_share_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $instPct = (float) $request->input('instructor_share_percentage');
        $platPct = (float) $request->input('platform_share_percentage');

        if (abs(($instPct + $platPct) - 100.0) > 0.01) {
            return back()->with('error', 'শিক্ষক ও প্ল্যাটফর্মের শতকরা হারের যোগফল অবশ্যই ১০০% হতে হবে।');
        }

        $share = RevenueShare::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'course_id' => $request->input('course_id'),
                'teacher_id' => $request->input('teacher_id'),
                'instructor_share_percentage' => $instPct,
                'platform_share_percentage' => $platPct,
                'notes' => $request->input('notes'),
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        AuditLoggerService::log(
            action: 'revenue_share.saved',
            modelType: RevenueShare::class,
            modelId: $share->id,
            payload: [
                'admin_id' => $request->user()->id,
                'instructor_percentage' => $instPct,
                'platform_percentage' => $platPct,
            ]
        );

        return back()->with('success', 'রেভিনিউ শেয়ার নিয়মটি সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Delete custom revenue share override.
     */
    public function deleteRevenueShare(RevenueShare $revenueShare)
    {
        $revenueShare->delete();

        AuditLoggerService::log(
            action: 'revenue_share.deleted',
            modelType: RevenueShare::class,
            modelId: $revenueShare->id,
            payload: []
        );

        return back()->with('success', 'রেভিনিউ শেয়ার নিয়মটি মুছে ফেলা হয়েছে।');
    }

    /**
     * Admin view of a teacher's monthly statement.
     */
    public function statement(Teacher $teacher, int $year, int $month)
    {
        $wallet = $this->walletService->getOrCreateWallet($teacher);

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
            'user' => $teacher->user,
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
