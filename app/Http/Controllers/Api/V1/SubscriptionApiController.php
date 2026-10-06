<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    /**
     * Get available membership / subscription plans.
     */
    public function plans(Request $request): JsonResponse
    {
        $plans = [
            [
                'id' => 'monthly',
                'name' => 'মাসিক সদস্যপদ (Monthly)',
                'price_bdt' => 499,
                'interval' => 'month',
                'features' => [
                    'সকল প্রিমিয়াম কোর্সে আনলিমিটেড অ্যাক্সেস',
                    'বিজ্ঞ মুফতিদের সরাসরি প্রশ্নোত্তর অগ্রাধিকার',
                    'হিফজ ও কুরআন স্টাডি ট্র্যাকিং টুলস',
                    'কোর্স সমাপ্তির পর অটোমেটিক ডিজিটাল সার্টিফিকেট',
                ],
            ],
            [
                'id' => 'yearly',
                'name' => 'বাৎসরিক সদস্যপদ (Yearly VIP)',
                'price_bdt' => 4999,
                'interval' => 'year',
                'discount_percentage' => 16,
                'features' => [
                    'সকল প্রিমিয়াম কোর্সে ৩৬৫ দিন অ্যাক্সেস',
                    'স্কলারদের সাথে ১-অন-১ কনসালটেশনে বিশেষ ছাড়',
                    'অফলাইন ডাউনলোড সাপোর্ট (মোবাইল অ্যাপে)',
                    'সকল ফিচার ও লাইভ সেশনে আনলিমিটেড অংশগ্রহণ',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'currency' => 'BDT',
            'data' => $plans,
            'plans' => $plans,
        ]);
    }

    /**
     * Get authenticated user subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'is_subscribed' => false,
            'plan' => null,
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }
}
