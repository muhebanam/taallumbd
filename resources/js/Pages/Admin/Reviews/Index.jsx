import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

const STATUS_MAP = {
    draft: { label: 'খসড়া', color: 'bg-slate-100 text-slate-700 border-slate-200' },
    in_review: { label: 'সম্পাদকীয় রিভিউ', color: 'bg-amber-100 text-amber-800 border-amber-300' },
    scholar_review: { label: 'স্কলার রিভিউ', color: 'bg-purple-100 text-purple-800 border-purple-300' },
    approved: { label: 'অনুমোদিত', color: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
    published: { label: 'প্রকাশিত', color: 'bg-teal-100 text-teal-800 border-teal-300' },
    rejected: { label: 'প্রত্যাখ্যাত', color: 'bg-rose-100 text-rose-800 border-rose-300' },
};

const TYPE_MAP = {
    course: { label: 'কোর্স', badge: 'bg-blue-50 text-blue-700 border-blue-200' },
    article: { label: 'প্রবন্ধ', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    fatwa: { label: 'ফাতাওয়া', badge: 'bg-purple-50 text-purple-700 border-purple-200' },
    publication: { label: 'প্রকাশনা', badge: 'bg-amber-50 text-amber-700 border-amber-200' },
};

export default function ReviewQueueIndex({ items, filters, counts, userRole }) {
    const handleFilterChange = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        if (!value || value === 'all') delete newFilters[key];
        router.get('/admin/reviews', newFilters, { preserveState: true });
    };

    return (
        <DashboardLayout title="কনটেন্ট রিভিউ কিউ — ওয়ার্কফ্লো ও অনুমোদন">
            <Head title="কনটেন্ট রিভিউ কিউ" />

            {/* Header with Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div className="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                    <p className="text-xs font-semibold text-amber-700 uppercase">সম্পাদকীয় রিভিউ পেন্ডিং</p>
                    <p className="text-2xl font-black text-amber-900 mt-1">{counts.in_review || 0}</p>
                    <span className="text-[11px] text-amber-600">এডিটর পর্যালোচনার অপেক্ষায়</span>
                </div>
                <div className="rounded-2xl border border-purple-200 bg-purple-50/50 p-4">
                    <p className="text-xs font-semibold text-purple-700 uppercase">স্কলার রিভিউ পেন্ডিং</p>
                    <p className="text-2xl font-black text-purple-900 mt-1">{counts.scholar_review || 0}</p>
                    <span className="text-[11px] text-purple-600">স্কলার শরিয়াহ মূল্যায়নের অপেক্ষায়</span>
                </div>
                <div className="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4">
                    <p className="text-xs font-semibold text-emerald-700 uppercase">চূড়ান্ত অনুমোদিত</p>
                    <p className="text-2xl font-black text-emerald-900 mt-1">{counts.approved || 0}</p>
                    <span className="text-[11px] text-emerald-600">প্রকাশের জন্য প্রস্তুত কনটেন্ট</span>
                </div>
            </div>

            {/* Filter Bar */}
            <div className="rounded-2xl bg-white border border-slate-200/80 p-4 mb-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-xs font-semibold text-slate-500 mr-1">ধরণ:</span>
                    {[
                        ['all', 'সব'],
                        ['course', 'কোর্স'],
                        ['article', 'প্রবন্ধ'],
                        ['fatwa', 'ফাতাওয়া'],
                        ['publication', 'প্রকাশনা'],
                    ].map(([t, label]) => (
                        <button
                            key={t}
                            onClick={() => handleFilterChange('type', t)}
                            className={`rounded-full px-3.5 py-1 text-xs font-medium transition ${
                                (filters.type || 'all') === t
                                    ? 'bg-[#1A2E2F] text-[#FFF99A]'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            {label}
                        </button>
                    ))}
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-xs font-semibold text-slate-500 mr-1">স্ট্যাটাস:</span>
                    {[
                        ['pending', 'অপেক্ষমাণ (সব)'],
                        ['in_review', 'সম্পাদকীয় রিভিউ'],
                        ['scholar_review', 'স্কলার রিভিউ'],
                        ['approved', 'অনুমোদিত'],
                        ['published', 'প্রকাশিত'],
                        ['rejected', 'প্রত্যাখ্যাত'],
                        ['all', 'সকল অবস্থা'],
                    ].map(([s, label]) => (
                        <button
                            key={s}
                            onClick={() => handleFilterChange('status', s)}
                            className={`rounded-full px-3 py-1 text-xs font-medium transition ${
                                (filters.status || 'pending') === s
                                    ? 'bg-[#1A2E2F] text-[#FFF99A]'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            {label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Review Items List */}
            <div className="rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                {items && items.length > 0 ? (
                    <div className="divide-y divide-slate-100">
                        {items.map((item) => {
                            const statusInfo = STATUS_MAP[item.status] || { label: item.status, color: 'bg-slate-100' };
                            const typeInfo = TYPE_MAP[item.type] || { label: item.type, badge: 'bg-slate-100' };

                            return (
                                <div
                                    key={`${item.type}-${item.id}`}
                                    className="p-5 sm:p-6 hover:bg-slate-50/70 transition flex flex-col md:flex-row md:items-center justify-between gap-4"
                                >
                                    <div className="space-y-1.5 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className={`rounded-md border px-2 py-0.5 text-[11px] font-bold ${typeInfo.badge}`}>
                                                {typeInfo.label}
                                            </span>
                                            <span className={`rounded-full border px-2.5 py-0.5 text-[11px] font-semibold ${statusInfo.color}`}>
                                                {statusInfo.label}
                                            </span>
                                            {item.is_certified && (
                                                <span className="rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                                                    ★ স্কলার প্রত্যায়িত
                                                </span>
                                            )}
                                        </div>

                                        <h3 className="text-base font-bold text-[#142425] hover:text-[#1A2E2F]">
                                            <Link href={`/admin/reviews/${item.type}/${item.id}`}>
                                                {item.title}
                                            </Link>
                                        </h3>

                                        <p className="text-xs text-slate-500">
                                            লেখক/উস্তায: <span className="font-semibold text-slate-700">{item.author}</span> • বিভাগ: {item.category} • সর্বশেষ আপডেট: {new Date(item.updated_at).toLocaleDateString('bn-BD')}
                                        </p>

                                        {item.latest_review && (
                                            <div className="mt-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                                                <span className="font-semibold text-slate-800">সর্বশেষ মন্তব্য ({item.latest_review.reviewer?.name}):</span>{' '}
                                                {item.latest_review.notes || item.latest_review.decision}
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <Link
                                            href={`/admin/reviews/${item.type}/${item.id}`}
                                            className="btn-primary text-xs !py-2 !px-4 whitespace-nowrap"
                                        >
                                            রিভিউ ও সিদ্ধান্ত গ্রহণ →
                                        </Link>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="p-16 text-center text-slate-500">
                        <div className="text-4xl mb-3">📋</div>
                        <h4 className="font-bold text-slate-700 text-lg">কোনো অপেক্ষমাণ কনটেন্ট পাওয়া যায়নি</h4>
                        <p className="text-xs text-slate-400 mt-1">নির্বাচিত ফিল্টার অনুযায়ী বর্তমানে কোনো রিভিউ পেন্ডিং নেই।</p>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
