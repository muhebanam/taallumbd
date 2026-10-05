<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderApprovedMail;
use App\Mail\OrderRejectedMail;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Teacher;
use App\Payments\PaymentGatewayManager;
use App\Services\AuditLoggerService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/** Central admin moderation: approve/reject member-submitted courses, articles, fatawa. */
class ModerationController extends Controller
{
    public function courses(Request $request)
    {
        return Inertia::render('Admin/Courses', [
            'courses' => Course::with('instructor:id,name')->withCount('enrollments')
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function updateCourseStatus(Request $request, Course $course)
    {
        $validated = $request->validate(['status' => 'required|in:draft,pending,published,rejected']);
        $oldStatus = $course->status;
        $course->update($validated);

        AuditLoggerService::log(
            action: 'course.status_updated',
            modelType: Course::class,
            modelId: $course->id,
            payload: [
                'course_title' => $course->title,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
            ]
        );

        return back()->with('success', 'কোর্স স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function articles(Request $request)
    {
        return Inertia::render('Admin/Articles', [
            'articles' => Article::with(['author:id,name', 'category:id,name'])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function updateArticleStatus(Request $request, Article $article)
    {
        $data = $request->validate(['status' => 'required|in:draft,pending,published,rejected']);
        $oldStatus = $article->status;
        $article->update([...$data, 'published_at' => $data['status'] === 'published' ? now() : $article->published_at]);

        AuditLoggerService::log(
            action: 'article.status_updated',
            modelType: Article::class,
            modelId: $article->id,
            payload: [
                'article_title' => $article->title,
                'old_status' => $oldStatus,
                'new_status' => $data['status'],
            ]
        );

        return back()->with('success', 'প্রবন্ধ স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function fatawa(Request $request)
    {
        return Inertia::render('Admin/Fatawa', [
            'fatawa' => Fatwa::with([
                'category:id,name',
                'mufti:id,name',
                'teacher.user:id,name',
                'assignedScholar.user:id,name',
                'relatedCourse:id,title',
            ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
            'scholars' => Teacher::with('user:id,name')->get(['id', 'user_id', 'name', 'designation', 'slug']),
            'courses' => Course::where('status', 'published')->get(['id', 'title']),
        ]);
    }

    /** Only admin or instructor (scholar) may answer — enforced in routes middleware too. */
    public function answerFatwa(Request $request, Fatwa $fatwa)
    {
        $data = $request->validate([
            'answer_body' => 'nullable|string',
            'status' => 'required|in:pending,answered,published,rejected',
            'assigned_scholar_id' => 'nullable|exists:teachers,id',
            'related_course_id' => 'nullable|exists:courses,id',
            'references' => 'nullable|string',
        ]);

        $updates = [
            'status' => $data['status'],
            'assigned_scholar_id' => $data['assigned_scholar_id'] ?? $fatwa->assigned_scholar_id,
            'related_course_id' => $data['related_course_id'] ?? $fatwa->related_course_id,
            'references' => $data['references'] ?? $fatwa->references,
        ];

        if (! empty($data['answer_body'])) {
            $updates['answer_body'] = $data['answer_body'];
            $updates['answered_by'] = $request->user()->id;
            $updates['answered_at'] = now();
        }

        if ($data['status'] === 'published') {
            $updates['published_at'] = $fatwa->published_at ?? now();
        }

        $fatwa->update($updates);

        return back()->with('success', 'ফাতওয়া ও গবেষণা ডাটা সফলভাবে সংরক্ষিত হয়েছে।');
    }

    public function orders(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $orders = Order::with(['user:id,name,email,phone', 'course:id,title', 'payments'])
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('transaction_id', 'like', "%{$term}%")
                        ->orWhere('sender_phone', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sevenDaysAgo = now()->subDays(7);
        $orders->getCollection()->transform(function ($order) use ($sevenDaysAgo) {
            $order->is_overdue = ($order->status === 'pending_verification' && $order->created_at <= $sevenDaysAgo);
            $order->screenshot_url = $order->screenshot_path ? route('orders.screenshot', $order) : null;

            return $order;
        });

        $counts = [
            'all' => Order::count(),
            'pending_verification' => Order::where('status', 'pending_verification')->count(),
            'overdue' => Order::where('status', 'pending_verification')->where('created_at', '<=', $sevenDaysAgo)->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    public function viewScreenshot(Order $order)
    {
        abort_unless($order->screenshot_path, 404, 'কোনো স্ক্রিনশট সংযুক্ত নেই।');

        $diskName = config('filesystems.private_disk', 'local');
        $disk = Storage::disk($diskName);

        abort_unless($disk->exists($order->screenshot_path), 404, 'ফাইলটি পাওয়া যায়নি।');

        $mime = $disk->mimeType($order->screenshot_path) ?: 'image/png';

        return response($disk->get($order->screenshot_path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="screenshot-'.$order->id.'.png"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approveOrder(Request $request, Order $order, PaymentService $payments)
    {
        abort_unless(in_array($order->status, ['pending', 'pending_verification']), 400, 'এই অর্ডারটি পেন্ডিং নয়।');

        $payments->confirm(
            $order,
            $order->payment_method ?: 'manual',
            $order->transaction_id,
            $order->sender_phone
        );

        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->queue(new OrderApprovedMail($order));
            } catch (\Throwable $e) {
                Log::warning("Failed to queue order approval email: {$e->getMessage()}");
            }
        }

        return back()->with('success', "অর্ডার #{$order->id} সফলভাবে অনুমোদন করা হয়েছে এবং শিক্ষার্থীকে কোর্সে যুক্ত করা হয়েছে।");
    }

    public function rejectOrder(Request $request, Order $order)
    {
        abort_unless(in_array($order->status, ['pending', 'pending_verification']), 400, 'শুধুমাত্র অপেক্ষমাণ অর্ডার বাতিল করা সম্ভব।');

        $reason = $request->input('reason', 'প্রদত্ত তথ্য অনুযায়ী পেমেন্ট যাচাই করা সম্ভব হয়নি।');
        $order->update(['status' => 'cancelled']);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $order->payment_method ?: 'manual',
            'type' => 'rejected',
            'gateway_ref' => $order->transaction_id,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'rejected',
            'payload' => [
                'reason' => $reason,
                'rejected_by' => $request->user()->id,
                'rejected_at' => now()->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
        ]);

        AuditLoggerService::log(
            action: 'order.rejected',
            modelType: Order::class,
            modelId: $order->id,
            payload: [
                'order_id' => $order->id,
                'reason' => $reason,
                'transaction_id' => $order->transaction_id,
            ]
        );

        if ($order->user?->email) {
            try {
                Mail::to($order->user->email)->queue(new OrderRejectedMail($order, $reason));
            } catch (\Throwable $e) {
                Log::warning("Failed to queue order rejection email: {$e->getMessage()}");
            }
        }

        return back()->with('info', "অর্ডার #{$order->id} বাতিল করা হয়েছে।");
    }

    public function refundOrder(Request $request, Order $order, PaymentGatewayManager $gatewayManager)
    {
        abort_unless($order->status === 'paid', 400, 'শুধুমাত্র পরিশোধিত অর্ডার রিফান্ড করা সম্ভব।');

        $reason = $request->input('reason', 'প্রশাসনিক নীতিমালা অনুযায়ী রিফান্ড প্রদান করা হয়েছে।');

        // 1. If payment method is automated gateway, call gateway refund API
        $method = $order->payment_method;
        if (in_array($method, ['sslcommerz', 'bkash']) && $gatewayManager->isGatewayEnabled($method)) {
            try {
                $driver = $gatewayManager->driver($method);
                $driver->refund($order, $reason);
            } catch (\Throwable $e) {
                Log::error("Gateway refund failed for order #{$order->id}: {$e->getMessage()}");

                return back()->with('error', 'গেটওয়ে রিফান্ড ব্যর্থ হয়েছে: '.$e->getMessage());
            }
        } else {
            // For manual / mock payments, record a refund transaction
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $method ?: 'manual',
                'type' => 'refund',
                'gateway_ref' => $order->transaction_id,
                'amount' => $order->final_payable_amount,
                'currency' => 'BDT',
                'status' => 'refunded',
                'payload' => [
                    'reason' => $reason,
                    'refunded_by' => $request->user()->id,
                    'refunded_at' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
            ]);
        }

        // 2. Mark order as cancelled
        $order->update(['status' => 'cancelled']);

        // 3. Revoke enrollment (status => cancelled)
        $enrollment = Enrollment::where('user_id', $order->user_id)
            ->where('course_id', $order->course_id)
            ->first();

        if ($enrollment) {
            $enrollment->update(['status' => 'cancelled']);
        }

        // 4. Audit Log
        AuditLoggerService::log(
            action: 'order.refunded',
            modelType: Order::class,
            modelId: $order->id,
            payload: [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'course_id' => $order->course_id,
                'amount' => $order->final_payable_amount,
                'gateway' => $method,
                'reason' => $reason,
            ]
        );

        return back()->with('success', "অর্ডার #{$order->id} সফলভাবে রিফান্ড করা হয়েছে এবং কোর্সের এনরোলমেন্ট বাতিল করা হয়েছে।");
    }

    public function enrollments()
    {
        return Inertia::render('Admin/Enrollments', [
            'enrollments' => Enrollment::with(['user:id,name,email', 'course:id,title'])->latest()->paginate(20),
        ]);
    }

    public function contactMessages()
    {
        return Inertia::render('Admin/ContactMessages', [
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }

    public function markMessageRead(ContactMessage $message)
    {
        $message->update(['status' => 'read']);

        return back();
    }
}
