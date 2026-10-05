import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function MockPayment({ order, paymentSettings = {}, isMockAllowed = false }) {
    // Default active tab to first enabled method or 'bkash'
    const availableMethods = ['bkash', 'nagad', 'rocket'];
    const [selectedMethod, setSelectedMethod] = useState(() => {
        if (paymentSettings.bkash?.enabled) return 'bkash';
        if (paymentSettings.nagad?.enabled) return 'nagad';
        if (paymentSettings.rocket?.enabled) return 'rocket';
        return 'bkash';
    });

    const [senderPhone, setSenderPhone] = useState(order.user?.phone || '');
    const [transactionId, setTransactionId] = useState('');
    const [screenshot, setScreenshot] = useState(null);
    const [copied, setCopied] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [validationError, setValidationError] = useState('');

    const payableAmount = Math.max(0, Number(order.amount) - Number(order.discount_amount || 0));

    const currentSetting = paymentSettings[selectedMethod] || {
        number: '০১৭xxxxxxxx',
        type: 'personal',
        instructions: 'অ্যাপ ওপেন করে সেন্ড মানি করুন।',
    };

    const handleCopyNumber = () => {
        if (currentSetting.number) {
            navigator.clipboard.writeText(currentSetting.number);
            setCopied(true);
            setTimeout(() => setCopied(false), 2500);
        }
    };

    // Method-based TrxID format guidance & client validation
    const validateTrxId = (val, method) => {
        const cleaned = val.trim().toUpperCase();
        if (!cleaned) return 'ট্রানজ্যাকশন আইডি (TrxID) আবশ্যক।';
        if (!/^[A-Z0-9]+$/.test(cleaned)) return 'TrxID শুধুমাত্র ইংরেজি বর্ণ ও সংখ্যা বিশিষ্ট হতে হবে।';

        if (method === 'bkash' && cleaned.length !== 10) {
            return 'বিকাশ TrxID সাধারণত ১০ অক্ষরের হয়ে থাকে (উদা: 9A8B7C6D5E)।';
        }
        if (method === 'nagad' && cleaned.length !== 8) {
            return 'নগদ TrxID সাধারণত ৮ অক্ষরের হয়ে থাকে (উদা: 8F2A1B9C)।';
        }
        if (method === 'rocket' && (cleaned.length < 8 || cleaned.length > 12)) {
            return 'রকেট TrxID সাধারণত ৮ থেকে ১২ অক্ষরের হয়ে থাকে।';
        }
        return '';
    };

    const handleManualSubmit = (e) => {
        e.preventDefault();
        setValidationError('');

        // BD Phone validation
        const bdPhoneRegex = /^(?:\+88|88)?01[3-9]\d{8}$/;
        if (!bdPhoneRegex.test(senderPhone.trim())) {
            setValidationError('সঠিক বাংলাদেশি মোবাইল নম্বর দিন (যেমন: 01712345678)');
            return;
        }

        // TrxID validation
        const trxErr = validateTrxId(transactionId, selectedMethod);
        if (trxErr) {
            setValidationError(trxErr);
            return;
        }

        setProcessing(true);

        const formData = new FormData();
        formData.append('method', selectedMethod);
        formData.append('sender_phone', senderPhone.trim());
        formData.append('transaction_id', transactionId.trim().toUpperCase());
        if (screenshot) {
            formData.append('screenshot', screenshot);
        }

        router.post(`/payment/${order.id}/manual-submit`, formData, {
            forceFormData: true,
            onFinish: () => setProcessing(false),
            onError: (errs) => {
                if (errs.transaction_id) setValidationError(errs.transaction_id);
                else if (errs.sender_phone) setValidationError(errs.sender_phone);
                else if (errs.screenshot) setValidationError(errs.screenshot);
            },
        });
    };

    const handleInstantTestMock = () => {
        if (!confirm('এটি শুধুমাত্র ডেভেলপমেন্ট ও টেস্টিংয়ের জন্য সরাসরি মক পেমেন্ট অনুমোদন করবে। এগিয়ে যাবেন?')) {
            return;
        }
        setProcessing(true);
        router.post(`/mock-payment/${order.id}/success`, {
            method: selectedMethod,
            sender_phone: senderPhone || '01711000000',
            transaction_id: 'MOCK' + Math.random().toString(36).substring(2, 10).toUpperCase(),
        }, {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <MainLayout>
            <Head title={`ম্যানুয়াল পেমেন্ট সম্পন্ন করুন — অর্ডার #${order.id} | আত-তাআল্লুম`} />

            <div className="bg-gradient-to-b from-[#102526] to-[#1A2E2F] py-10 text-white">
                <div className="mx-auto max-w-2xl px-4 text-center sm:px-6">
                    <span className="rounded-full bg-[#FFF99A]/20 px-3.5 py-1 text-xs font-semibold text-[#FFF99A]">
                        নিরাপদ ম্যানুয়াল পেমেন্ট
                    </span>
                    <h1 className="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        পেমেন্ট সম্পন্ন করুন
                    </h1>
                    <p className="mt-1 text-xs text-[#F8FAF8]/80">
                        অর্ডার নং: #{order.id} • পরিশোধযোগ্য পরিমাণ: <strong className="text-[#FFF99A] text-base font-bold">৳{payableAmount.toFixed(0)}</strong>
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-xl space-y-6">
                    {/* Course Summary Banner */}
                    <div className="rounded-2xl bg-gray-50 p-4 border border-gray-100 flex items-center justify-between">
                        <div>
                            <span className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">কোর্সের বিবরণ:</span>
                            <h3 className="font-bold text-sm text-[#102526]">{order.course?.title}</h3>
                        </div>
                        <div className="text-right">
                            <span className="text-[10px] text-gray-500 block">মোট প্রদেয়</span>
                            <span className="text-xl font-extrabold text-emerald-800">৳{payableAmount.toFixed(0)}</span>
                        </div>
                    </div>

                    {/* Method Selection Tabs */}
                    <div>
                        <label className="block text-xs font-bold text-gray-700 mb-2">
                            ১. পেমেন্ট মাধ্যম নির্বাচন করুন:
                        </label>
                        <div className="grid grid-cols-3 gap-3">
                            {/* bKash */}
                            <button
                                type="button"
                                onClick={() => { setSelectedMethod('bkash'); setValidationError(''); }}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    selectedMethod === 'bkash'
                                        ? 'border-pink-600 bg-pink-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-xl">🟣</span>
                                <span className="block mt-1 font-bold text-xs text-pink-700">বিকাশ (bKash)</span>
                            </button>

                            {/* Nagad */}
                            <button
                                type="button"
                                onClick={() => { setSelectedMethod('nagad'); setValidationError(''); }}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    selectedMethod === 'nagad'
                                        ? 'border-orange-600 bg-orange-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-xl">🟠</span>
                                <span className="block mt-1 font-bold text-xs text-orange-700">নগদ (Nagad)</span>
                            </button>

                            {/* Rocket */}
                            <button
                                type="button"
                                onClick={() => { setSelectedMethod('rocket'); setValidationError(''); }}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    selectedMethod === 'rocket'
                                        ? 'border-purple-600 bg-purple-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-xl">🚀</span>
                                <span className="block mt-1 font-bold text-xs text-purple-700">রকেট (Rocket)</span>
                            </button>
                        </div>
                    </div>

                    {/* Step-by-Step Payment Instructions Card */}
                    <div className="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 space-y-3">
                        <div className="flex items-center justify-between border-b border-amber-200/80 pb-3">
                            <span className="font-bold text-xs text-amber-950 uppercase tracking-wide">
                                ২. সেন্ড মানি নির্দেশনা ও নম্বর:
                            </span>
                            <span className="rounded-full bg-amber-200/80 px-2.5 py-0.5 text-[11px] font-bold text-amber-900">
                                {currentSetting.type === 'merchant' ? 'মার্চেন্ট পেমেন্ট' : 'ব্যক্তিগত (Personal) Send Money'}
                            </span>
                        </div>

                        {/* Receiving Number & Copy Button */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-amber-200">
                            <div>
                                <span className="text-[11px] text-gray-500 block">আমাদের অফিশিয়াল প্রাপক নম্বর:</span>
                                <span className="font-mono text-lg font-extrabold text-[#102526] tracking-wider">
                                    {currentSetting.number}
                                </span>
                            </div>
                            <button
                                type="button"
                                onClick={handleCopyNumber}
                                className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F] transition"
                            >
                                {copied ? '✓ নম্বর কপি হয়েছে!' : '📋 নম্বর কপি করুন'}
                            </button>
                        </div>

                        {/* Step Details */}
                        <ol className="list-decimal list-inside space-y-1.5 text-xs text-amber-950 leading-relaxed pt-1">
                            <li>আপনার <strong>{selectedMethod.toUpperCase()}</strong> অ্যাপ ওপেন করে <strong>"সেন্ড মানি"</strong> অপশনে যান।</li>
                            <li>উপরের নম্বরে ঠিক <strong className="text-emerald-800 font-extrabold">৳{payableAmount.toFixed(0)}</strong> টাকা সেন্ড করুন।</li>
                            {currentSetting.instructions && (
                                <li className="text-gray-700 italic">{currentSetting.instructions}</li>
                            )}
                            <li>লেনদেন শেষে প্রাপ্ত ট্রানজেকশন আইডি (TrxID) ও প্রেরক নম্বর নিচে লিখে জমা দিন।</li>
                        </ol>
                    </div>

                    {/* Manual Submission Form */}
                    <form onSubmit={handleManualSubmit} className="space-y-4">
                        <h4 className="text-xs font-bold text-gray-800 uppercase tracking-wider">
                            ৩. পেমেন্টের তথ্য নিশ্চিতকরণ:
                        </h4>

                        {validationError && (
                            <div className="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs font-semibold text-rose-800">
                                ⚠️ {validationError}
                            </div>
                        )}

                        <div className="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label className="block text-xs font-bold text-gray-700 mb-1">
                                    যে নম্বর থেকে টাকা পাঠিয়েছেন *
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={senderPhone}
                                    onChange={(e) => setSenderPhone(e.target.value)}
                                    placeholder="০১৭xxxxxxxx"
                                    className="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs focus:border-[#102526] focus:outline-none"
                                />
                                <span className="text-[10px] text-gray-400 mt-0.5 block">১১ ডিজিটের বাংলাদেশি মোবাইল নম্বর</span>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-700 mb-1">
                                    ট্রানজ্যাকশন আইডি (TrxID) *
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={transactionId}
                                    onChange={(e) => setTransactionId(e.target.value.toUpperCase())}
                                    placeholder="উদা: 9A8B7C6D5E"
                                    className="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-mono uppercase focus:border-[#102526] focus:outline-none"
                                />
                                <span className="text-[10px] text-gray-400 mt-0.5 block">এসএমএস-এ প্রাপ্ত TrxID লিখুন</span>
                            </div>
                        </div>

                        {/* Optional Screenshot */}
                        <div>
                            <label className="block text-xs font-bold text-gray-700 mb-1">
                                পেমেন্ট স্ক্রিনশট (ঐচ্ছিক — দ্রুত ভেরিফিকেশনের জন্য সহায়ক)
                            </label>
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                onChange={(e) => setScreenshot(e.target.files[0] || null)}
                                className="w-full rounded-xl border border-gray-200 p-2 text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                            />
                            <span className="text-[10px] text-gray-400 mt-0.5 block">সর্বোচ্চ ৩ মেগাবাইট (JPG, PNG বা WEBP)</span>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-2xl bg-[#102526] py-3.5 text-center text-sm font-bold text-[#FFF99A] shadow-md hover:bg-[#1A2E2F] disabled:opacity-50 transition"
                        >
                            {processing ? 'তথ্য যাচাই ও জমা হচ্ছে...' : 'পেমেন্ট তথ্য জমা দিন &rarr;'}
                        </button>
                    </form>

                    {/* Developer Mock Testing Shortcut */}
                    {isMockAllowed && (
                        <div className="rounded-2xl border border-dashed border-indigo-200 bg-indigo-50/50 p-4 text-center">
                            <span className="text-[11px] font-bold text-indigo-900 block mb-1">
                                🛠️ ডেভেলপমেন্ট / লোকাল টেস্টিং মোড
                            </span>
                            <p className="text-[10px] text-indigo-700 mb-3">
                                এটি শুধুমাত্র লোকাল অথবা টেস্ট এনভায়রনমেন্টে সক্রিয় থাকে। প্রোডাকশনে এই অপশন পুরোপুরি নিষ্ক্রিয় (404)।
                            </p>
                            <button
                                type="button"
                                disabled={processing}
                                onClick={handleInstantTestMock}
                                className="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-700 disabled:opacity-50 transition"
                            >
                                তাৎক্ষণিক টেস্ট ভর্তি সম্পন্ন করুন (Instant Mock)
                            </button>
                        </div>
                    )}

                    <div className="border-t border-gray-100 pt-4 text-center">
                        <p className="text-[11px] text-gray-500">
                            পেমেন্ট সংক্রান্ত সহায়তার জন্য যোগাযোগ করুন: <strong className="text-gray-700">support@taallumbd.com</strong>
                        </p>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
