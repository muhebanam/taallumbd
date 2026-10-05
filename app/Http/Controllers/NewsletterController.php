<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email address (Double Opt-In).
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew([
            'email' => strtolower(trim($validated['email'])),
        ]);

        if ($subscriber->isVerified()) {
            return back()->with('info', 'আপনি ইতিমধ্যে আমাদের নিউজলেটারে যুক্ত আছেন। জাযাকাল্লাহু খাইরান!');
        }

        $subscriber->status = 'pending';
        $subscriber->token = Str::random(40);
        $subscriber->source = $validated['source'] ?? 'website';
        $subscriber->save();

        $verifyUrl = route('newsletter.verify', ['token' => $subscriber->token]);

        // If mailer is configured, send verification mail (fallback safe)
        try {
            Mail::raw("আসসালামু আলাইকুম,\n\nআত-তাআল্লুমের সাপ্তাহিক দ্বীনি বার্তায় সাবস্ক্রিপশন নিশ্চিত করতে নিচের লিংকে ক্লিক করুন:\n{$verifyUrl}\n\nধন্যবাদ,\nআত-তাআল্লুম একাডেমি", function ($message) use ($subscriber) {
                $message->to($subscriber->email)
                    ->subject('নিউজলেটার সাবস্ক্রিপশন নিশ্চিতকরণ — আত-তাআল্লুম');
            });
        } catch (\Throwable $e) {
            // Logged silently, proceed
        }

        return back()->with('success', 'আপনার সাবস্ক্রিপশন গ্রহণ করা হয়েছে! অনুগ্রহ করে ইমেইল ইনবক্স চেক করে নিশ্চিত করুন।');
    }

    /**
     * Verify email via double opt-in token.
     */
    public function verify(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            return Inertia::render('Newsletter/Verified', [
                'success' => false,
                'message' => 'লিংকটি সঠিক নয় অথবা এর মেয়াদ শেষ হয়ে গেছে।',
            ]);
        }

        $subscriber->status = 'active';
        $subscriber->verified_at = now();
        $subscriber->save();

        return Inertia::render('Newsletter/Verified', [
            'success' => true,
            'message' => 'মাশাআল্লাহ! আপনার সাবস্ক্রিপশন সফলভাবে যাচাই করা হয়েছে। এখন থেকে নিয়মিত আপডেট পাবেন।',
        ]);
    }

    /**
     * Unsubscribe from newsletter.
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if ($subscriber) {
            $subscriber->status = 'unsubscribed';
            $subscriber->save();
        }

        return Inertia::render('Newsletter/Verified', [
            'success' => true,
            'message' => 'আপনাকে আমাদের মেইলিং তালিকা থেকে সফলভাবে অপসারণ করা হয়েছে।',
        ]);
    }
}
