<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>পেমেন্ট তথ্য সংক্রান্ত নোটিশ</title>
</head>
<body style="font-family: 'Hind Siliguri', 'Segoe UI', Tahoma, sans-serif; background-color: #f8faf8; margin: 0; padding: 20px; color: #102526;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #102526; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="color: #FFF99A; margin: 0; font-size: 24px;">আত-তাআল্লুম</h1>
            <p style="margin: 4px 0 0; font-size: 13px; color: #d1d5db;">ডিজিটাল ইসলামী একাডেমি</p>
        </div>
        <div style="padding: 24px;">
            <h2 style="font-size: 18px; color: #991b1b; margin-top: 0;">অর্ডার #{{ $order->id }}-এর পেমেন্ট যাচাই সংক্রান্ত তথ্য</h2>
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                আসসালামু আলাইকুম <strong>{{ $order->user?->name }}</strong>,<br><br>
                আপনার প্রেরিত ট্রানজ্যাকশন আইডি ও পেমেন্ট তথ্য অনুযায়ী পেমেন্ট যাচাই করা সম্ভব হয়নি।
            </p>
            <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 12px; padding: 16px; margin: 20px 0;">
                <p style="margin: 0 0 8px; font-size: 13px; color: #991b1b;"><strong>বাতিলের কারণ:</strong></p>
                <p style="margin: 0; font-size: 14px; color: #7f1d1d;">{{ $reason }}</p>
            </div>
            <p style="font-size: 13px; line-height: 1.6; color: #4b5563;">
                যদি আপনি ইতিমধ্যে সঠিক পেমেন্ট করে থাকেন বা কোনো ভুল হয়ে থাকে, অনুগ্রহ করে সঠিক TrxID সহ পুনরায় জমা দিন অথবা আমাদের সাপোর্ট হেল্পলাইনে যোগাযোগ করুন।
            </p>
            <div style="text-align: center; margin: 28px 0 16px;">
                <a href="{{ url('/checkout/' . $order->course?->id) }}" style="background-color: #102526; color: #FFF99A; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; display: inline-block;">
                    পুনরায় চেষ্টা করুন
                </a>
            </div>
        </div>
        <div style="background-color: #f8faf8; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #f3f4f6;">
            © {{ date('Y') }} আত-তাআল্লুম। সর্বস্বত্ব সংরক্ষিত।
        </div>
    </div>
</body>
</html>
