import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function Orders({ orders, filters = {}, counts = {} }) {
    const [processingId, setProcessingId] = useState(null);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [activeScreenshot, setActiveScreenshot] = useState(null);
    const [rejectingOrder, setRejectingOrder] = useState(null);
    const [rejectReason, setRejectReason] = useState('প্রদত্ত তথ্য অনুযায়ী পেমেন্ট যাচাই করা সম্ভব হয়নি।');
    const [refundingOrder, setRefundingOrder] = useState(null);
    const [refundReason, setRefundReason] = useState('প্রশাসনিক নীতিমালা অনুযায়ী রিফান্ড প্রদান করা হয়েছে।');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/orders', {
            status: filters.status || undefined,
            search: searchTerm || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterStatus = (statusKey) => {
        router.get('/admin/orders', {
            status: statusKey === 'all' ? undefined : statusKey,
            search: searchTerm || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleApprove = (orderId) => {
        if (!confirm(`আপনি কি নিশ্চিত যে অর্ডার #${orderId} অনুমোদন করতে চান? এটি শিক্ষার্থীকে কোর্সে অ্যাক্সেস প্রদান করবে এবং ইমেইল নোটিফিকেশন পাঠাবে।`)) {
            return;
        }
        setProcessingId(orderId);
        router.post(`/admin/orders/${orderId}/approve`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessingId(null),
        });
    };

    const submitReject = (e) => {
        e.preventDefault();
        if (!rejectingOrder) return;

        setProcessingId(rejectingOrder.id);
        router.post(`/admin/orders/${rejectingOrder.id}/reject`, {
            reason: rejectReason,
        }, {
            preserveScroll: true,
            onFinish: () => {
                setProcessingId(null);
                setRejectingOrder(null);
            },
        });
    };

    const submitRefund = (e) => {
        e.preventDefault();
        if (!refundingOrder) return;

        setProcessingId(refundingOrder.id);
        router.post(`/admin/orders/${refundingOrder.id}/refund`, {
            reason: refundReason,
        }, {
            preserveScroll: true,
            onFinish: () => {
                setProcessingId(null);
                setRefundingOrder(null);
            },
        });
    };

    return (
        <DashboardLayout title="অর্ডার ও পেমেন্ট ম্যানেজমেন্ট">
            <Head title="অর্ডার ও পেমেন্ট — অ্যাডমিন" />

            <div className="space-y-6">
                {/* Header & Stats Banner */}
                <div className="rounded-3xl bg-gradient-to-r from-[#102526] to-[#1A2E2F] p-6 text-white shadow-lg">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="rounded-full bg-[#FFF99A]/20 px-3 py-1 text-xs font-semibold text-[#FFF99A]">
                                ম্যানুয়াল গেটওয়ে ভেরিফিকেশন কিউ
                            </span>
                            <h2 className="mt-2 text-xl font-extrabold text-white sm:text-2xl">
                                শিক্ষার্থী পেমেন্ট যাচাই ও অনুমোদন
                            </h2>
                            <p className="mt-1 text-xs text-gray-300">
                                bKash, Nagad ও Rocket সেন্ড মানির TrxID এবং স্ক্রিনশট মিলিয়ে অর্ডার অনুমোদন করুন
                            </p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link
                                href="/admin/settings/payments"
                                className="inline-flex items-center gap-2 rounded-2xl bg-[#FFF99A] px-4 py-2.5 text-xs font-bold text-[#102526] shadow hover:bg-[#FFF99A]/90 transition"
                            >
                                ⚙️ পেমেন্ট গেটওয়ে সেটিংস
                            </Link>
                        </div>
                    </div>

                    {/* Quick Counts */}
                    <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5 text-center text-xs">
                        <div className="rounded-2xl bg-white/10 p-3 backdrop-blur-sm">
                            <span className="block text-gray-300">মোট অর্ডার</span>
                            <span className="text-xl font-bold text-white">{counts.all ?? orders.total ?? 0}</span>
                        </div>
                        <div className="rounded-2xl bg-amber-500/20 border border-amber-400/40 p-3 backdrop-blur-sm">
                            <span className="block text-amber-200">যাচাই অপেক্ষমাণ</span>
                            <span className="text-xl font-bold text-[#FFF99A]">{counts.pending_verification ?? 0}</span>
                        </div>
                        {counts.overdue > 0 && (
                            <div className="rounded-2xl bg-rose-500/20 border border-rose-400/40 p-3 backdrop-blur-sm">
                                <span className="block text-rose-200">⚠️ ৭+ দিন অপেক্ষমাণ</span>
                                <span className="text-xl font-bold text-rose-300">{counts.overdue}</span>
                            </div>
                        )}
                        <div className="rounded-2xl bg-emerald-500/20 border border-emerald-400/30 p-3 backdrop-blur-sm">
                            <span className="block text-emerald-200">পরিশোধিত</span>
                            <span className="text-xl font-bold text-emerald-300">{counts.paid ?? 0}</span>
                        </div>
                        <div className="rounded-2xl bg-white/10 p-3 backdrop-blur-sm">
                            <span className="block text-gray-300">বাতিলকৃত</span>
                            <span className="text-xl font-bold text-gray-300">{counts.cancelled ?? 0}</span>
                        </div>
                    </div>
                </div>

                {/* Filters & Search Toolbar */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-2xl bg-white p-4 shadow-sm border border-gray-100">
                    {/* Status Tabs */}
                    <div className="flex flex-wrap gap-2 text-xs font-semibold">
                        {[
                            { key: 'all', label: 'সকল' },
                            { key: 'pending_verification', label: '⏳ যাচাই অপেক্ষমাণ' },
                            { key: 'paid', label: '✅ পরিশোধিত' },
                            { key: 'pending', label: '🕒 অপরিশোধিত' },
                            { key: 'cancelled', label: '❌ বাতিল' },
                        ].map((tab) => {
                            const isSelected = (filters.status === tab.key) || (!filters.status && tab.key === 'all');
                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => handleFilterStatus(tab.key)}
                                    className={`rounded-xl px-3.5 py-2 transition ${
                                        isSelected
                                            ? 'bg-[#102526] text-[#FFF99A] font-bold shadow-sm'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            );
                        })}
                    </div>

                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="flex items-center gap-2">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="TrxID, নাম বা মোবাইল দিয়ে খুঁজুন..."
                            className="rounded-xl border border-gray-200 px-3.5 py-2 text-xs focus:border-[#102526] focus:outline-none w-64"
                        />
                        <button
                            type="submit"
                            className="rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-[#FFF99A] hover:bg-[#1A2E2F]"
                        >
                            অনুসন্ধান
                        </button>
                    </form>
                </div>

                {/* Orders Table */}
                <div className="card overflow-x-auto bg-white rounded-3xl border border-gray-100 shadow-sm">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-[#102526] text-[#FFF99A]">
                            <tr>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">#ID</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">শিক্ষার্থী</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">কোর্স</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">পরিমাণ</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">মাধ্যম ও TrxID</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">স্ক্রিনশট</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider">অবস্থা</th>
                                <th className="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-right">পদক্ষেপ</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {orders.data?.length > 0 ? (
                                orders.data.map((o) => {
                                    const isPendingVerification = o.status === 'pending_verification' || (o.status === 'pending' && o.transaction_id);
                                    const isPaid = o.status === 'paid';
                                    const isCancelled = o.status === 'cancelled';
                                    const isOverdue = o.is_overdue;

                                    return (
                                        <tr
                                            key={o.id}
                                            className={`transition ${
                                                isOverdue
                                                    ? 'bg-rose-50/70 border-l-4 border-l-rose-600 hover:bg-rose-50'
                                                    : isPendingVerification
                                                    ? 'bg-amber-50/50 border-l-4 border-l-amber-500 hover:bg-amber-50/80'
                                                    : 'hover:bg-gray-50/80'
                                            }`}
                                        >
                                            <td className="px-4 py-3.5 font-mono font-bold text-gray-700">
                                                #{o.id}
                                                {isOverdue && (
                                                    <span className="block mt-0.5 text-[10px] font-bold text-rose-700">
                                                        ⚠️ ৭+ দিন
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <div className="font-bold text-gray-900">{o.user?.name}</div>
                                                <div className="text-xs text-gray-500">{o.user?.email}</div>
                                                {o.user?.phone && (
                                                    <div className="text-[11px] text-gray-400 font-mono">{o.user?.phone}</div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <div className="font-semibold text-gray-800 line-clamp-1 max-w-[180px]" title={o.course?.title}>
                                                    {o.course?.title}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3.5 font-bold text-emerald-800 whitespace-nowrap">
                                                ৳ {Number(o.amount).toFixed(0)}
                                                {o.discount_amount > 0 && (
                                                    <div className="text-[10px] text-gray-400 line-through">
                                                        ৳ {Number(o.amount) + Number(o.discount_amount)}
                                                    </div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5 whitespace-nowrap">
                                                <span className="inline-block font-semibold uppercase text-xs text-gray-700 bg-gray-100 rounded-lg px-2 py-0.5">
                                                    {o.payment_method ?? 'MANUAL'}
                                                </span>
                                                {o.transaction_id && (
                                                    <div className="text-xs font-mono font-bold text-indigo-700 mt-1">
                                                        Trx: {o.transaction_id}
                                                    </div>
                                                )}
                                                {o.sender_phone && (
                                                    <div className="text-[11px] text-gray-500 font-mono">
                                                        প্রেরক: {o.sender_phone}
                                                    </div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5 whitespace-nowrap">
                                                {o.screenshot_url || o.screenshot_path ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => setActiveScreenshot(o.screenshot_url || `/admin/orders/${o.id}/screenshot`)}
                                                        className="inline-flex items-center gap-1 rounded-xl bg-indigo-50 border border-indigo-200 px-2.5 py-1 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"
                                                    >
                                                        📷 প্রমাণ দেখুন
                                                    </button>
                                                ) : (
                                                    <span className="text-xs text-gray-400">নেই</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5 whitespace-nowrap">
                                                {isPaid && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                        ✅ পরিশোধিত
                                                    </span>
                                                )}
                                                {isPendingVerification && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                                        ⏳ যাচাই অপেক্ষমাণ
                                                    </span>
                                                )}
                                                {o.status === 'pending' && !o.transaction_id && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-600">
                                                        🕒 অপরিশোধিত
                                                    </span>
                                                )}
                                                {isCancelled && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                                                        ❌ বাতিল
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3.5 text-right whitespace-nowrap">
                                                <div className="inline-flex items-center gap-1.5">
                                                    {isPendingVerification && (
                                                        <>
                                                            <button
                                                                type="button"
                                                                disabled={processingId === o.id}
                                                                onClick={() => handleApprove(o.id)}
                                                                className="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow hover:bg-emerald-700 disabled:opacity-50 transition"
                                                            >
                                                                {processingId === o.id ? '...' : 'অনুমোদন'}
                                                            </button>
                                                            <button
                                                                type="button"
                                                                disabled={processingId === o.id}
                                                                onClick={() => {
                                                                    setRejectingOrder(o);
                                                                    setRejectReason('প্রদত্ত তথ্য অনুযায়ী পেমেন্ট যাচাই করা সম্ভব হয়নি।');
                                                                }}
                                                                className="rounded-xl bg-rose-100 px-2.5 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-200 disabled:opacity-50 transition"
                                                            >
                                                                বাতিল
                                                            </button>
                                                        </>
                                                    )}
                                                    {isPaid && (
                                                        <button
                                                            type="button"
                                                            disabled={processingId === o.id}
                                                            onClick={() => {
                                                                setRefundingOrder(o);
                                                                setRefundReason('প্রশাসনিক নীতিমালা অনুযায়ী রিফান্ড প্রদান করা হয়েছে।');
                                                            }}
                                                            className="rounded-xl bg-rose-50 border border-rose-200 px-2.5 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 disabled:opacity-50 transition"
                                                        >
                                                            রিফান্ড
                                                        </button>
                                                    )}
                                                    <Link
                                                        href={`/orders/${o.id}/invoice`}
                                                        className="rounded-xl border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                                    >
                                                        রসিদ
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="8" className="p-8 text-center text-xs text-gray-500">
                                        কোনো অর্ডারের তথ্য পাওয়া যায়নি।
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={orders.links} />
            </div>

            {/* Screenshot Preview Modal */}
            {activeScreenshot && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                    <div className="relative max-w-2xl w-full rounded-3xl bg-white p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 className="font-bold text-sm text-gray-900">পেমেন্ট প্রুফ স্ক্রিনশট</h3>
                            <button
                                type="button"
                                onClick={() => setActiveScreenshot(null)}
                                className="text-gray-400 hover:text-gray-700 text-lg font-bold"
                            >
                                ✕
                            </button>
                        </div>
                        <div className="mt-4 max-h-[70vh] overflow-auto flex items-center justify-center bg-gray-50 rounded-2xl p-2 border border-gray-200">
                            <img
                                src={activeScreenshot}
                                alt="Payment Screenshot"
                                className="max-w-full rounded-xl object-contain"
                            />
                        </div>
                        <div className="mt-4 flex justify-between items-center text-xs">
                            <a
                                href={activeScreenshot}
                                target="_blank"
                                rel="noreferrer"
                                className="text-indigo-600 hover:underline font-bold"
                            >
                                পূর্ণাঙ্গ আকারে ওপেন করুন &rarr;
                            </a>
                            <button
                                type="button"
                                onClick={() => setActiveScreenshot(null)}
                                className="rounded-xl bg-gray-100 px-4 py-2 font-bold text-gray-700 hover:bg-gray-200"
                            >
                                বন্ধ করুন
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Rejection Modal with Reason */}
            {rejectingOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                    <form onSubmit={submitReject} className="relative max-w-md w-full rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 className="font-bold text-base text-rose-700">
                                অর্ডার #{rejectingOrder.id} বাতিল করুন
                            </h3>
                            <button
                                type="button"
                                onClick={() => setRejectingOrder(null)}
                                className="text-gray-400 hover:text-gray-700 text-lg font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <p className="text-xs text-gray-600">
                            শিক্ষার্থীর নাম: <strong>{rejectingOrder.user?.name}</strong><br />
                            প্রদত্ত TrxID: <strong>{rejectingOrder.transaction_id || 'নেই'}</strong>
                        </p>

                        <div>
                            <label className="block text-xs font-bold text-gray-700 mb-1">
                                বাতিলের কারণ (শিক্ষার্থীকে ইমেইলে জানানো হবে):
                            </label>
                            <textarea
                                rows="3"
                                required
                                value={rejectReason}
                                onChange={(e) => setRejectReason(e.target.value)}
                                className="w-full rounded-2xl border border-gray-300 p-3 text-xs focus:border-rose-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div className="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setRejectingOrder(null)}
                                className="rounded-xl bg-gray-100 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200"
                            >
                                ফিরে যান
                            </button>
                            <button
                                type="submit"
                                disabled={processingId === rejectingOrder.id}
                                className="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-rose-700 disabled:opacity-50"
                            >
                                {processingId === rejectingOrder.id ? 'বাতিল হচ্ছে...' : 'নিশ্চিত বাতিল করুন'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Refund Modal with Reason */}
            {refundingOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                    <form onSubmit={submitRefund} className="relative max-w-md w-full rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 className="font-bold text-base text-rose-700">
                                অর্ডার #{refundingOrder.id} রিফান্ড করুন
                            </h3>
                            <button
                                type="button"
                                onClick={() => setRefundingOrder(null)}
                                className="text-gray-400 hover:text-gray-700 text-lg font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <p className="text-xs text-gray-600">
                            শিক্ষার্থীর নাম: <strong>{refundingOrder.user?.name}</strong><br />
                            কোর্স: <strong>{refundingOrder.course?.title}</strong><br />
                            পেমেন্ট মাধ্যম: <strong className="uppercase">{refundingOrder.payment_method || 'MANUAL'}</strong><br />
                            রিফান্ডযোগ্য পরিমাণ: <strong className="text-emerald-700">৳{Number(refundingOrder.amount).toFixed(0)}</strong>
                        </p>

                        <div className="rounded-xl bg-amber-50 border border-amber-200 p-3 text-[11px] text-amber-900 leading-relaxed">
                            ⚠️ <strong>সতর্কতা:</strong> রিফান্ড সম্পন্ন হলে গেটওয়েতে রিফান্ড রিকোয়েস্ট পাঠানো হবে, শিক্ষার্থীর কোর্সের এনরোলমেন্ট বাতিল করা হবে এবং অডিট লগে রেকর্ড সংরক্ষিত হবে।
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-gray-700 mb-1">
                                রিফান্ডের কারণ (অডিট লগে সংরক্ষিত হবে):
                            </label>
                            <textarea
                                rows="3"
                                required
                                value={refundReason}
                                onChange={(e) => setRefundReason(e.target.value)}
                                className="w-full rounded-2xl border border-gray-300 p-3 text-xs focus:border-rose-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div className="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setRefundingOrder(null)}
                                className="rounded-xl bg-gray-100 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200"
                            >
                                ফিরে যান
                            </button>
                            <button
                                type="submit"
                                disabled={processingId === refundingOrder.id}
                                className="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-rose-700 disabled:opacity-50"
                            >
                                {processingId === refundingOrder.id ? 'রিফান্ড হচ্ছে...' : 'নিশ্চিত রিফান্ড প্রদান করুন'}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </DashboardLayout>
    );
}
