import React from 'react';
import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';
import Pagination from '../../../Components/Pagination';

export default function ReviewsIndex({ reviews }) {
    const handleApprove = (rId) => {
        router.post(`/admin/teacher-reviews/${rId}/approve`, {}, { preserveScroll: true });
    };

    const handleReject = (rId) => {
        if (confirm('আপনি কি নিশ্চিত যে এই রিভিউটি বাতিল করতে চান?')) {
            router.post(`/admin/teacher-reviews/${rId}/reject`, {}, { preserveScroll: true });
        }
    };

    return (
        <DashboardLayout title="শিক্ষকদের রিভিউ মডারেশন">
            <Head title="রিভিউ মডারেশন" />

            <div className="card overflow-x-auto border border-slate-150 rounded-2xl bg-white shadow-sm">
                <table className="w-full text-left text-sm border-collapse">
                    <thead className="bg-[#102526] text-white">
                        <tr>
                            <th className="px-4 py-3 text-right font-bold">শিক্ষার্থী ও মন্তব্য</th>
                            <th className="px-4 py-3">শিক্ষক</th>
                            <th className="px-4 py-3">কোর্স</th>
                            <th className="px-4 py-3 text-center">রেটিং</th>
                            <th className="px-4 py-3 text-center">স্ট্যাটাস</th>
                            <th className="px-4 py-3 text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {reviews.data.length > 0 ? (
                            reviews.data.map((r) => (
                                <tr key={r.id} className="hover:bg-slate-50/50">
                                    <td className="px-4 py-3 text-right max-w-md">
                                        <h5 className="font-bold text-slate-800 text-sm">{r.user?.name}</h5>
                                        {r.review ? (
                                            <p className="text-xs text-slate-500 mt-1 italic">"{r.review}"</p>
                                        ) : (
                                            <span className="text-[10px] text-slate-400 italic">কোনো মন্তব্য নেই</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 font-semibold text-slate-700">{r.teacher?.name}</td>
                                    <td className="px-4 py-3 text-xs text-slate-500 font-semibold">{r.course?.title ?? 'N/A'}</td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="flex items-center justify-center text-amber-500">
                                            {[...Array(r.rating)].map((_, i) => (
                                                <span key={i}>★</span>
                                            ))}
                                            {[...Array(5 - r.rating)].map((_, i) => (
                                                <span key={i} className="text-slate-200">★</span>
                                            ))}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <span className={`inline-block rounded-md border px-2 py-0.5 text-[10px] font-bold ${
                                            r.status === 'approved'
                                                ? 'bg-emerald-50 border-emerald-100 text-emerald-800'
                                                : r.status === 'pending'
                                                ? 'bg-amber-50 border-amber-100 text-amber-800'
                                                : 'bg-red-50 border-red-100 text-red-800'
                                        }`}>
                                            {r.status === 'approved' ? 'অনুমোদিত' : r.status === 'pending' ? 'পেন্ডিং' : 'বাতিল'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="flex items-center justify-center gap-2">
                                            {r.status === 'pending' && (
                                                <>
                                                    <button
                                                        onClick={() => handleApprove(r.id)}
                                                        className="text-xs font-bold text-emerald-600 hover:underline"
                                                    >
                                                        অনুমোদন
                                                    </button>
                                                    <button
                                                        onClick={() => handleReject(r.id)}
                                                        className="text-xs font-bold text-red-500 hover:underline"
                                                    >
                                                        বাতিল
                                                    </button>
                                                </>
                                            )}
                                            {r.status !== 'pending' && (
                                                <span className="text-xs text-slate-400 font-bold">N/A</span>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="6" className="px-4 py-12 text-center text-slate-400 font-semibold">
                                    কোনো রিভিউ পাওয়া যায়নি।
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-6 flex justify-center">
                <Pagination links={reviews.links} />
            </div>
        </DashboardLayout>
    );
}
