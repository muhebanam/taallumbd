import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function MockPayment({ order }) {
    const [tab, setTab] = useState('bkash'); // bkash | nagad | manual | card
    const [senderPhone, setSenderPhone] = useState(order.user?.phone || '');
    const [transactionId, setTransactionId] = useState('');
    const [processing, setProcessing] = useState(false);

    const payableAmount = Math.max(0, Number(order.amount) - Number(order.discount_amount || 0));

    const handlePay = (method, customTrx = null) => {
        setProcessing(true);
        router.post(`/mock-payment/${order.id}/success`, {
            method: method,
            sender_phone: senderPhone,
            transaction_id: customTrx || transactionId || undefined,
        }, {
            onFinish: () => setProcessing(false),
        });
    };

    const handleManualSubmit = (e) => {
        e.preventDefault();
        if (!senderPhone || !transactionId) {
            alert('অনুগ্রহ করে মোবাইল নম্বর এবং Transaction ID (TrxID) প্রদান করুন।');
            return;
        }
        handlePay('manual_' + tab, transactionId);
    };

    return (
        <MainLayout>
            <Head title={`পেমেন্ট সম্পন্ন করুন — অর্ডার #${order.id} | আত-তাআল্লুম`} />

            <div className="bg-gradient-to-b from-[#102526] to-[#1A2E2F] py-10 text-white">
                <div className="mx-auto max-w-2xl px-4 text-center sm:px-6">
                    <span className="rounded-full bg-[#FFF99A]/20 px-3.5 py-1 text-xs font-semibold text-[#FFF99A]">
                        নিরাপদ পেমেন্ট গেটওয়ে
                    </span>
                    <h1 className="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        পেমেন্ট সম্পন্ন করুন
                    </h1>
                    <p className="mt-1 text-xs text-[#F8FAF8]/80">
                        অর্ডার নং: #{order.id} • সর্বমোট প্রদেয়: <strong className="text-[#FFF99A] text-sm">৳{payableAmount.toFixed(0)}</strong>
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Gateway Box */}
                <div className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-xl">
                    {/* Course Header Banner */}
                    <div className="rounded-2xl bg-gray-50 p-4 border border-gray-100 flex items-center justify-between">
                        <div>
                            <span className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">কোর্সের নাম:</span>
                            <h3 className="font-bold text-sm text-[#102526]">{order.course?.title}</h3>
                        </div>
                        <div className="text-right">
                            <span className="text-[10px] text-gray-500 block">প্রদেয় টাকা</span>
                            <span className="text-xl font-extrabold text-emerald-800">৳{payableAmount.toFixed(0)}</span>
                        </div>
                    </div>

                    {/* Method Selector Tabs */}
                    <div className="mt-6">
                        <label className="block text-xs font-bold text-gray-700 mb-2">
                            পেমেন্ট মাধ্যম নির্বাচন করুন:
                        </label>
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button
                                type="button"
                                onClick={() => setTab('bkash')}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    tab === 'bkash'
                                        ? 'border-pink-600 bg-pink-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-base">🟣</span>
                                <span className="block mt-1 font-bold text-xs text-pink-700">বিকাশ (bKash)</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setTab('nagad')}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    tab === 'nagad'
                                        ? 'border-orange-600 bg-orange-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-base">🟠</span>
                                <span className="block mt-1 font-bold text-xs text-orange-700">নগদ (Nagad)</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setTab('manual')}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    tab === 'manual'
                                        ? 'border-[#102526] bg-[#102526]/5 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-base">📱</span>
                                <span className="block mt-1 font-bold text-xs text-gray-800">TrxID যাচাই</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setTab('card')}
                                className={`rounded-2xl p-3 text-center border-2 transition ${
                                    tab === 'card'
                                        ? 'border-blue-600 bg-blue-50/50 shadow-sm'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <span className="block text-base">💳</span>
                                <span className="block mt-1 font-bold text-xs text-blue-700">কার্ড / ব্যাংক</span>
                            </button>
                        </div>
                    </div>

                    {/* Tab 1: bKash Panel */}
                    {tab === 'bkash' && (
                        <div className="mt-6 rounded-2xl border border-pink-200 bg-pink-50/40 p-6 space-y-4">
                            <div className="flex items-center justify-between border-b border-pink-200/60 pb-3">
                                <span className="font-bold text-sm text-pink-900 flex items-center gap-2">
                                    <span className="h-3 w-3 rounded-full bg-pink-600"></span>
                                    bKash Payment Gateway
                                </span>
                                <span className="text-xs font-bold text-pink-800">৳{payableAmount.toFixed(0)}</span>
                            </div>

                            <p className="text-xs text-gray-600 leading-relaxed">
                                বিকাশ অ্যাপ বা পেমেন্ট গেটওয়ের মাধ্যমে তাৎক্ষণিক লেনদেন সম্পন্ন করতে নিচের বাটনে চাপ দিন।
                            </p>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 mb-1">আপনার বিকাশ মোবাইল নম্বর</label>
                                <input
                                    type="text"
                                    value={senderPhone}
                                    onChange={(e) => setSenderPhone(e.target.value)}
                                    placeholder="০১৮xxxxxxxx"
                                    className="w-full rounded-xl border border-pink-300 px-4 py-2.5 text-sm focus:border-pink-600 focus:outline-none"
                                />
                            </div>

                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => handlePay('bkash')}
                                className="w-full rounded-xl bg-pink-600 py-3 text-center text-sm font-bold text-white shadow hover:bg-pink-700 disabled:opacity-50 transition"
                            >
                                {processing ? 'প্রক্রিয়াকরণ হচ্ছে...' : `বিকাশে ৳${payableAmount.toFixed(0)} পে করুন`}
                            </button>
                        </div>
                    )}

                    {/* Tab 2: Nagad Panel */}
                    {tab === 'nagad' && (
                        <div className="mt-6 rounded-2xl border border-orange-200 bg-orange-50/40 p-6 space-y-4">
                            <div className="flex items-center justify-between border-b border-orange-200/60 pb-3">
                                <span className="font-bold text-sm text-orange-900 flex items-center gap-2">
                                    <span className="h-3 w-3 rounded-full bg-orange-600"></span>
                                    Nagad Direct Gateway
                                </span>
                                <span className="text-xs font-bold text-orange-800">৳{payableAmount.toFixed(0)}</span>
                            </div>

                            <p className="text-xs text-gray-600 leading-relaxed">
                                নগদ অ্যাকাউন্ট থেকে সরাসরি পেমেন্ট করতে আপনার মোবাইল নম্বর নিশ্চিত করুন।
                            </p>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 mb-1">আপনার নগদ মোবাইল নম্বর</label>
                                <input
                                    type="text"
                                    value={senderPhone}
                                    onChange={(e) => setSenderPhone(e.target.value)}
                                    placeholder="০১৭xxxxxxxx"
                                    className="w-full rounded-xl border border-orange-300 px-4 py-2.5 text-sm focus:border-orange-600 focus:outline-none"
                                />
                            </div>

                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => handlePay('nagad')}
                                className="w-full rounded-xl bg-orange-600 py-3 text-center text-sm font-bold text-white shadow hover:bg-orange-700 disabled:opacity-50 transition"
                            >
                                {processing ? 'প্রক্রিয়াকরণ হচ্ছে...' : `নগদে ৳${payableAmount.toFixed(0)} পে করুন`}
                            </button>
                        </div>
                    )}

                    {/* Tab 3: Manual TrxID Verification */}
                    {tab === 'manual' && (
                        <form onSubmit={handleManualSubmit} className="mt-6 rounded-2xl border border-gray-300 bg-gray-50 p-6 space-y-4">
                            <div className="rounded-xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-950">
                                <p className="font-bold mb-1">ম্যানুয়াল সেন্ড মানি নির্দেশনা:</p>
                                <ol className="list-decimal list-inside space-y-1 text-gray-700">
                                    <li>আমাদের অফিশিয়াল বিকাশ/নগদ মার্চেন্ট নম্বর: <strong className="text-gray-900">০১৭১১-৮৮৯৯০০</strong></li>
                                    <li>ঠিক <strong className="text-emerald-800 font-bold">৳{payableAmount.toFixed(0)}</strong> টাকা সেন্ড মানি / ক্যাশ ইন করুন।</li>
                                    <li>লেনদেন শেষে প্রাপ্ত ট্রানজেকশন আইডি (TrxID) নিচে লিখে নিশ্চিত করুন।</li>
                                </ol>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label className="block text-xs font-bold text-gray-700 mb-1">যে নম্বর থেকে পাঠিয়েছেন *</label>
                                    <input
                                        type="text"
                                        required
                                        value={senderPhone}
                                        onChange={(e) => setSenderPhone(e.target.value)}
                                        placeholder="০১৭xxxxxxxx"
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs focus:border-[#102526] focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-700 mb-1">Transaction ID (TrxID) *</label>
                                    <input
                                        type="text"
                                        required
                                        value={transactionId}
                                        onChange={(e) => setTransactionId(e.target.value.toUpperCase())}
                                        placeholder="উদা: 9A8B7C6D5E"
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs uppercase focus:border-[#102526] focus:outline-none"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-[#102526] py-3 text-center text-sm font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F] disabled:opacity-50 transition"
                            >
                                {processing ? 'যাচাই করা হচ্ছে...' : 'TrxID যাচাই ও ভর্তি নিশ্চিত করুন'}
                            </button>
                        </form>
                    )}

                    {/* Tab 4: Card / SSLCommerz */}
                    {tab === 'card' && (
                        <div className="mt-6 rounded-2xl border border-blue-200 bg-blue-50/40 p-6 space-y-4">
                            <span className="font-bold text-sm text-blue-900 block border-b border-blue-200 pb-2">
                                ডেবিট/ক্রেডিট কার্ড ও ইন্টারনেট ব্যাংকিং (SSLCommerz)
                            </span>
                            <p className="text-xs text-gray-600">
                                ভিসা, মাস্টারকার্ড, ব্র্যাক ব্যাংক, ইসলামী ব্যাংক, সিটি টাচ সহ যেকোনো কার্ডের মাধ্যমে নিরাপদ পেমেন্ট করুন।
                            </p>
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => handlePay('sslcommerz')}
                                className="w-full rounded-xl bg-blue-700 py-3 text-center text-sm font-bold text-white shadow hover:bg-blue-800 disabled:opacity-50 transition"
                            >
                                {processing ? 'প্রক্রিয়াকরণ হচ্ছে...' : `কার্ডে ৳${payableAmount.toFixed(0)} পরিশোধ করুন`}
                            </button>
                        </div>
                    )}

                    <div className="mt-6 border-t border-gray-100 pt-4 text-center">
                        <p className="text-[11px] text-gray-400">
                            পেমেন্ট সম্পর্কিত কোনো সমস্যা হলে আমাদের হেল্পলাইনে কল করুন: <span className="font-bold text-gray-700">০১৮০০-১২৩৪৫৬</span>
                        </p>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
