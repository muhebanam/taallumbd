import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function Orders({ orders }) {
    const [processingId, setProcessingId] = useState(null);

    const handleApprove = (orderId) => {
        if (!confirm(`আপনি কি নিশ্চিত যে অর্ডার #${orderId} অনুমোদন করতে চান? এটি শিক্ষার্থীকে কোর্সে অ্যাক্সেস প্রদান করবে।`)) {
            return;
        }
        setProcessingId(orderId);
        router.post(`/admin/orders/${orderId}/approve`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessingId(null),
        });
    };

    const handleReject = (orderId) => {
        if (!confirm(`আপনি কি নিশ্চিত যে অর্ডার #${orderId} বাতিল করতে চান?`)) {
            return;
        }
        setProcessingId(orderId);
        router.post(`/admin/orders/${orderId}/reject`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessingId(null),
        });
    };

    return (
        <DashboardLayout title="অর্ডার ও পেমেন্ট ম্যানেজমেন্ট">
            <Head title="অর্ডার ও পেমেন্ট — অ্যাডমিন" />

            <div className="card overflow-x-auto">
                <div className="border-b border-gray-100 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-bold text-gray-900">সকল শিক্ষার্থী অর্ডার</h2>
                        <p className="text-xs text-gray-500 mt-0.5">ম্যানুয়াল bKash/Nagad/Rocket পেমেন্ট ভেরিফাই ও অনুমোদন করুন</p>
                    </div>
                </div>

                <table className="w-full text-left text-sm">
                    <thead className="bg-[#102526] text-[#FFF99A]">
                        <tr>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">#ID</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">শিক্ষার্থী</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">কোর্স</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">পরিমাণ</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">মাধ্যম ও TrxID</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider">অবস্থা</th>
                            <th className="px-4 py-3 text-xs font-bold uppercase tracking-wider text-right">পদক্ষেপ</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {orders.data.map((o) => {
                            const isPendingVerification = o.status === 'pending' && o.transaction_id;
                            const isPaid = o.status === 'paid';
                            const isCancelled = o.status === 'cancelled';

                            return (
                                <tr key={o.id} className={isPendingVerification ? 'bg-amber-50/40 hover:bg-amber-50/70 transition' : 'hover:bg-gray-50/80 transition'}>
                                    <td className="px-4 py-3 font-mono font-bold text-gray-700">#{o.id}</td>
                                    <td className="px-4 py-3">
                                        <div className="font-bold text-gray-900">{o.user?.name}</div>
                                        <div className="text-xs text-gray-500">{o.user?.email}</div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-semibold text-gray-800 line-clamp-1 max-w-[200px]" title={o.course?.title}>
                                            {o.course?.title}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 font-bold text-emerald-800 whitespace-nowrap">
                                        ৳ {Number(o.amount).toFixed(0)}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        <span className="font-semibold uppercase text-xs text-gray-700">{o.payment_method ?? '-'}</span>
                                        {o.transaction_id && (
                                            <div className="text-xs font-mono font-bold text-indigo-700 mt-0.5">
                                                TrxID: {o.transaction_id}
                                            </div>
                                        )}
                                        {o.sender_phone && (
                                            <div className="text-[11px] text-gray-500">
                                                মোবাইল: {o.sender_phone}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        {isPaid && (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                ✅ পরিশোধিত
                                            </span>
                                        )}
                                        {isPendingVerification && (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                                ⏳ যাচাইকরণাধীন
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
                                    <td className="px-4 py-3 text-right whitespace-nowrap">
                                        <div className="inline-flex items-center gap-1.5">
                                            {isPendingVerification && (
                                                <>
                                                    <button
                                                        type="button"
                                                        disabled={processingId === o.id}
                                                        onClick={() => handleApprove(o.id)}
                                                        className="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow hover:bg-emerald-700 disabled:opacity-50"
                                                    >
                                                        {processingId === o.id ? '...' : 'অনুমোদন'}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        disabled={processingId === o.id}
                                                        onClick={() => handleReject(o.id)}
                                                        className="rounded-lg bg-rose-100 px-2.5 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-200 disabled:opacity-50"
                                                    >
                                                        বাতিল
                                                    </button>
                                                </>
                                            )}
                                            <Link
                                                href={`/orders/${o.id}/invoice`}
                                                className="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                            >
                                                রসিদ
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
            <Pagination links={orders.links} />
        </DashboardLayout>
    );
}
