<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>পেমেন্ট সফল ও অনুমোদিত</title>
</head>
<body style="font-family: 'Hind Siliguri', 'Segoe UI', Tahoma, sans-serif; background-color: #f8faf8; margin: 0; padding: 20px; color: #102526;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #102526; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="color: #FFF99A; margin: 0; font-size: 24px;">আত-তাআল্লুম</h1>
            <p style="margin: 4px 0 0; font-size: 13px; color: #d1d5db;">ডিজিটাল ইসলামী একাডেমি</p>
        </div>
        <div style="padding: 24px;">
            <h2 style="font-size: 18px; color: #065f46; margin-top: 0;">আলহামদুলিল্লাহ! আপনার পেমেন্ট অনুমোদিত হয়েছে</h2>
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                আসসালামু আলাইকুম <strong>{{ $order->user?->name }}</strong>,<br><br>
                আপনার অর্ডার <strong>#{{ $order->id }}</strong>-এর পেমেন্ট সফলভাবে যাচাই ও অনুমোদন করা হয়েছে। আপনি এখন কোর্সের সকল পাঠ ও রিসোর্সে আজীবন প্রবেশাধিকার পেয়েছেন।
            </p>
            <div style="background-color: #f9fafb; border: 1px solid #f3f4f6; border-radius: 12px; padding: 16px; margin: 20px 0;">
                <p style="margin: 0 0 8px; font-size: 13px;"><strong>কোর্সের নাম:</strong> {{ $order->course?->title }}</p>
                <p style="margin: 0 0 8px; font-size: 13px;"><strong>পরিশোধিত অর্থ:</strong> ৳{{ number_format($order->final_payable_amount, 2) }}</p>
                <p style="margin: 0; font-size: 13px;"><strong>ট্রানজ্যাকশন আইডি (TrxID):</strong> {{ $order->transaction_id }}</p>
            </div>
            <div style="text-align: center; margin: 28px 0 16px;">
                <a href="{{ url('/student/courses/' . $order->course?->id) }}" style="background-color: #102526; color: #FFF99A; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; display: inline-block;">
                    কোর্সে প্রবেশ করুন &rarr;
                </a>
            </div>
        </div>
        <div style="background-color: #f8faf8; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #f3f4f6;">
            © {{ date('Y') }} আত-তাআল্লুম। সর্বস্বত্ব সংরক্ষিত।
        </div>
    </div>
</body>
</html>
