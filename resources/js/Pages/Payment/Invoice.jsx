import { Head, Link } from '@inertiajs/react';

export default function Invoice({ order }) {
    const handlePrint = () => {
        window.print();
    };

    const payableAmount = Math.max(0, Number(order.amount) - Number(order.discount_amount || 0));

    return (
        <div className="min-h-screen bg-gray-100 py-10 print:bg-white print:py-0">
            <Head title={`রসিদ ও ইনভয়েস #${order.id} — আত-তাআল্লুম`} />

            <div className="mx-auto max-w-2xl px-4 sm:px-6">
                {/* Print & Back Navigation */}
                <div className="mb-6 flex items-center justify-between print:hidden">
                    <Link
                        href="/dashboard/orders"
                        className="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 hover:text-gray-900"
                    >
                        &larr; আমার সকল অর্ডার
                    </Link>

                    <button
                        onClick={handlePrint}
                        className="inline-flex items-center gap-1.5 rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                    >
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        রসিদ প্রিন্ট করুন
                    </button>
                </div>

                {/* Printable Invoice Sheet */}
                <div className="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 sm:p-12 shadow-lg print:border-none print:shadow-none print:p-0">
                    {/* Watermark Logo */}
                    <div className="pointer-events-none absolute inset-0 flex items-center justify-center opacity-5">
                        <span className="font-amiri text-8xl text-[#102526]">আত-তাআল্লুম</span>
                    </div>

                    {/* Invoice Top Bar */}
                    <div className="flex items-center justify-between border-b border-gray-200 pb-6">
                        <div>
                            <span className="font-amiri text-2xl font-bold text-[#102526]">আত-তাআল্লুম</span>
                            <p className="text-[11px] text-gray-500">ডিজিটাল ইসলামী একাডেমি ও স্কলার নেটওয়ার্ক</p>
                            <p className="text-[10px] text-gray-400">www.taallumbd.com • support@taallumbd.com</p>
                        </div>
                        <div className="text-right">
                            <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                পরিশোধিত (PAID)
                            </span>
                            <p className="mt-2 text-xs font-bold text-gray-800">ইনভয়েস #{order.id}</p>
                            <p className="text-[11px] text-gray-400">
                                তারিখ: {new Date(order.updated_at || order.created_at).toLocaleDateString('bn-BD', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })}
                            </p>
                        </div>
                    </div>

                    {/* Billed To */}
                    <div className="mt-6 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span className="font-bold text-gray-500 block uppercase tracking-wider text-[10px]">শিক্ষার্থীর বিবরণ:</span>
                            <p className="font-bold text-gray-900 mt-1">{order.user?.name}</p>
                            <p className="text-gray-600">{order.user?.email}</p>
                            {order.user?.phone && <p className="text-gray-600">{order.user?.phone}</p>}
                        </div>

                        <div className="text-right">
                            <span className="font-bold text-gray-500 block uppercase tracking-wider text-[10px]">পেমেন্ট মাধ্যম:</span>
                            <p className="font-bold text-emerald-800 mt-1 uppercase">{order.payment_method}</p>
                            {order.transaction_id && (
                                <p className="font-mono text-gray-700 mt-0.5">TrxID: {order.transaction_id}</p>
                            )}
                        </div>
                    </div>

                    {/* Item Details Table */}
                    <div className="mt-8 overflow-hidden rounded-2xl border border-gray-200">
                        <table className="min-w-full divide-y divide-gray-200 text-xs">
                            <thead className="bg-gray-50 font-bold text-gray-700">
                                <tr>
                                    <th className="px-4 py-3 text-left">কোর্সের বিবরণ</th>
                                    <th className="px-4 py-3 text-center">উস্তায</th>
                                    <th className="px-4 py-3 text-right">মূল্য</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 bg-white">
                                <tr>
                                    <td className="px-4 py-3 font-semibold text-gray-900">
                                        {order.course?.title}
                                    </td>
                                    <td className="px-4 py-3 text-center text-gray-600">
                                        {order.course?.instructor?.name || 'মুদাররিস'}
                                    </td>
                                    <td className="px-4 py-3 text-right font-bold text-gray-900">
                                        ৳{Number(order.amount).toFixed(0)}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {/* Summary Calculation */}
                    <div className="mt-6 flex justify-end">
                        <div className="w-64 space-y-2 text-xs">
                            <div className="flex justify-between text-gray-600">
                                <span>কোর্স ফি:</span>
                                <span>৳{Number(order.amount).toFixed(0)}</span>
                            </div>

                            {Number(order.discount_amount) > 0 && (
                                <div className="flex justify-between text-emerald-700 font-semibold">
                                    <span>কুপন ডিসকাউন্ট ({order.coupon_code}):</span>
                                    <span>- ৳{Number(order.discount_amount).toFixed(0)}</span>
                                </div>
                            )}

                            <div className="border-t border-gray-200 pt-2 flex justify-between font-bold text-sm text-[#102526]">
                                <span>সর্বমোট পরিশোধিত:</span>
                                <span className="text-base text-emerald-800">৳{payableAmount.toFixed(0)}</span>
                            </div>
                        </div>
                    </div>

                    {/* Official Seal / Signature */}
                    <div className="mt-12 flex items-center justify-between border-t border-gray-100 pt-6 text-[11px] text-gray-400">
                        <div>
                            <p className="font-semibold text-gray-600">আত-তাআল্লুম ফাইন্যান্স বিভাগ</p>
                            <p>স্বয়ংক্রিয় কম্পিউটার জেনারেটেড রসিদ, স্বাক্ষরের প্রয়োজন নেই।</p>
                        </div>
                        <div className="flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-[#102526]/30 text-[#102526] font-bold text-[9px] uppercase text-center p-1">
                            TaallumBD Verified
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
