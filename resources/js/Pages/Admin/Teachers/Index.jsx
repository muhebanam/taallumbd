import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';
import Pagination from '../../../Components/Pagination';

export default function TeachersIndex({ teachers }) {
    const handleVerify = (teacherId) => {
        router.post(`/admin/teachers/${teacherId}/verify`, {}, { preserveScroll: true });
    };

    const handleFeature = (teacherId) => {
        router.post(`/admin/teachers/${teacherId}/feature`, {}, { preserveScroll: true });
    };

    const handleActivate = (teacherId) => {
        router.post(`/admin/teachers/${teacherId}/activate`, {}, { preserveScroll: true });
    };

    const handleReject = (teacherId) => {
        router.post(`/admin/teachers/${teacherId}/reject`, {}, { preserveScroll: true });
    };

    const handleDelete = (teacherId) => {
        if (confirm('আপনি কি নিশ্চিত যে এই শিক্ষকের প্রোফাইলটি ডিলিট করতে চান?')) {
            router.delete(`/admin/teachers/${teacherId}`, { preserveScroll: true });
        }
    };

    const getStatusBadge = (status) => {
        const styles = {
            active: 'bg-emerald-50 text-emerald-800 border-emerald-100',
            pending: 'bg-amber-50 text-amber-800 border-amber-100',
            inactive: 'bg-slate-100 text-slate-600 border-slate-200',
            rejected: 'bg-red-50 text-red-800 border-red-100'
        };
        const labels = {
            active: 'সক্রিয়',
            pending: 'পেন্ডিং',
            inactive: 'নিষ্ক্রিয়',
            rejected: 'প্রত্যাখ্যাত'
        };
        return (
            <span className={`inline-block rounded-md border px-2 py-0.5 text-xs font-semibold ${styles[status] ?? styles.pending}`}>
                {labels[status] ?? status}
            </span>
        );
    };

    return (
        <DashboardLayout title="শিক্ষকমণ্ডলী ব্যবস্থাপনা">
            <Head title="শিক্ষক প্রোফাইল" />

            <div className="mb-6 flex justify-between items-center flex-wrap gap-4">
                <h2 className="text-sm font-semibold text-slate-500">মোট নিবন্ধিত শিক্ষক: {teachers.total} জন</h2>
                <Link
                    href="/admin/teachers/create"
                    className="inline-flex items-center justify-center rounded-xl bg-[#102526] hover:bg-[#1A2E2F] text-white text-xs font-bold px-4 py-2.5 shadow-sm transition-all duration-300"
                >
                    নতুন শিক্ষক প্রোফাইল তৈরি করুন
                </Link>
            </div>

            <div className="card overflow-x-auto border border-slate-150 rounded-2xl bg-white shadow-sm">
                <table className="w-full text-left text-sm border-collapse">
                    <thead className="bg-[#102526] text-white">
                        <tr>
                            <th className="px-4 py-3 text-right">নাম</th>
                            <th className="px-4 py-3">ইউজার লিংক</th>
                            <th className="px-4 py-3">পদবি</th>
                            <th className="px-4 py-3 text-center">ফলোয়ার / কোর্স</th>
                            <th className="px-4 py-3 text-center">ভেরিফিকেশন</th>
                            <th className="px-4 py-3 text-center">ফিচার্ড</th>
                            <th className="px-4 py-3 text-center">স্ট্যাটাস</th>
                            <th className="px-4 py-3 text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {teachers.data.length > 0 ? (
                            teachers.data.map((t) => (
                                <tr key={t.id} className="hover:bg-slate-50/50">
                                    <td className="px-4 py-3 text-right font-bold text-slate-800">
                                        <div className="flex items-center justify-end gap-2">
                                            {t.is_verified && (
                                                <span className="text-amber-500" title="যাচাইকৃত">★</span>
                                            )}
                                            {t.featured && (
                                                <span className="text-blue-500" title="ফিচার্ড">⚡</span>
                                            )}
                                            <Link href={`/teachers/${t.slug}`} className="hover:underline">
                                                {t.name}
                                            </Link>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-slate-500 font-semibold">
                                        {t.user ? `${t.user.name} (${t.user.email})` : 'কোনো ইউজার লিঙ্কড নেই'}
                                    </td>
                                    <td className="px-4 py-3 font-semibold text-emerald-800 text-xs">{t.designation}</td>
                                    <td className="px-4 py-3 text-center text-slate-600 font-bold">
                                        {t.followers_count ?? 0} / {t.courses_count ?? 0}
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <button
                                            onClick={() => handleVerify(t.id)}
                                            className={`rounded-lg px-2.5 py-1 text-xs font-bold border transition-colors ${
                                                t.is_verified 
                                                    ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100' 
                                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                            }`}
                                        >
                                            {t.is_verified ? 'যাচাইকৃত' : 'যাচাই করুন'}
                                        </button>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <button
                                            onClick={() => handleFeature(t.id)}
                                            className={`rounded-lg px-2.5 py-1 text-xs font-bold border transition-colors ${
                                                t.featured 
                                                    ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' 
                                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                            }`}
                                        >
                                            {t.featured ? 'ফিচার্ড' : 'ফিচার করুন'}
                                        </button>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        {getStatusBadge(t.status)}
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="flex items-center justify-center gap-2">
                                            {t.status === 'pending' && (
                                                <>
                                                    <button 
                                                        onClick={() => handleActivate(t.id)}
                                                        className="text-xs font-bold text-emerald-600 hover:underline"
                                                    >
                                                        অনুমোদন
                                                    </button>
                                                    <button 
                                                        onClick={() => handleReject(t.id)}
                                                        className="text-xs font-bold text-red-500 hover:underline"
                                                    >
                                                        বাতিল
                                                    </button>
                                                </>
                                            )}
                                            <Link
                                                href={`/admin/teachers/${t.slug}/edit`}
                                                className="text-xs font-bold text-blue-600 hover:underline"
                                            >
                                                সম্পাদনা
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(t.id)}
                                                className="text-xs font-bold text-red-600 hover:underline"
                                            >
                                                মুছে ফেলুন
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="8" className="px-4 py-12 text-center text-slate-400 font-semibold">
                                    কোনো শিক্ষক প্রোফাইল পাওয়া যায়নি।
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-6 flex justify-center">
                <Pagination links={teachers.links} />
            </div>
        </DashboardLayout>
    );
}
