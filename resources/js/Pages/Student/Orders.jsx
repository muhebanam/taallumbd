import { Head } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

const STATUS_BN = {
    pending: 'অপেক্ষমাণ',
    paid: 'পরিশোধিত',
    failed: 'ব্যর্থ',
    cancelled: 'বাতিল'
};

const STATUS_CLS = {
    pending: 'bg-amber-100 text-amber-800',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-700'
};

export default function Orders({ orders }) {
    return (
        <DashboardLayout title="আমার অর্ডারসমূহ">
            <Head title="অর্ডার সমূহ" />

            <div className="card overflow-hidden bg-white border border-brand/5 shadow-card">
                {orders.data.length ? (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-brand text-brand-cream border-b border-brand-deep">
                                    <tr>
                                        <th className="px-6 py-4 font-bold">অর্ডার আইডি</th>
                                        <th className="px-6 py-4 font-bold">কোর্স</th>
                                        <th className="px-6 py-4 font-bold">মূল্য</th>
                                        <th className="px-6 py-4 font-bold">পেমেন্ট পদ্ধতি</th>
                                        <th className="px-6 py-4 font-bold">অবস্থা</th>
                                        <th className="px-6 py-4 font-bold">তারিখ</th>
                                        <th className="px-6 py-4 font-bold text-right">রসিদ</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-brand/5">
                                    {orders.data.map((o) => (
                                        <tr key={o.id} className="hover:bg-brand-light transition">
                                            <td className="px-6 py-4 font-mono font-bold text-brand">#{o.id}</td>
                                            <td className="px-6 py-4 font-semibold text-brand-deep">{o.course?.title || 'কোর্স পাওয়া যায়নি'}</td>
                                            <td className="px-6 py-4 font-bold text-brand">৳ {Number(o.amount).toFixed(0)}</td>
                                            <td className="px-6 py-4 text-brand-text/70 uppercase">{o.payment_method || 'N/A'}</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${STATUS_CLS[o.status] || 'bg-gray-100'}`}>
                                                    {STATUS_BN[o.status] || o.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-brand-text/60">
                                                {new Date(o.created_at).toLocaleDateString('bn-BD', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                })}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                {o.status === 'paid' ? (
                                                    <a
                                                        href={`/orders/${o.id}/invoice`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 rounded-lg bg-[#102526] px-2.5 py-1 text-xs font-bold text-[#FFF99A] hover:bg-[#1A2E2F]"
                                                    >
                                                        ইনভয়েস
                                                    </a>
                                                ) : o.status === 'pending' ? (
                                                    <a
                                                        href={`/mock-payment/${o.id}`}
                                                        className="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-amber-700"
                                                    >
                                                        পে করুন
                                                    </a>
                                                ) : (
                                                    <span className="text-xs text-gray-400">—</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {orders.links && orders.links.length > 3 && (
                            <div className="p-4 border-t border-brand/5 bg-brand-light">
                                <Pagination links={orders.links} />
                            </div>
                        )}
                    </>
                ) : (
                    <div className="p-12 text-center">
                        <svg className="mx-auto h-12 w-12 text-brand-text/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p className="mt-4 text-brand-text/60 font-medium">আপনার কোন অর্ডারের তথ্য পাওয়া যায়নি।</p>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
