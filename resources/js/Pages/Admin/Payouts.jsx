import React, { useState } from 'react';
import { Head, useForm, router, Link } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';

export default function Payouts({
    metrics,
    payouts,
    revenue_shares = [],
    courses = [],
    teachers = [],
    current_status = 'pending',
}) {
    const [activeTab, setActiveTab] = useState('queue'); // 'queue' | 'shares'
    const [approveModalPayout, setApproveModalPayout] = useState(null);
    const [rejectModalPayout, setRejectModalPayout] = useState(null);
    const [shareModalOpen, setShareModalOpen] = useState(false);

    // Form for approving payout
    const approveForm = useForm({
        transaction_reference: '',
        notes: '',
    });

    // Form for rejecting payout
    const rejectForm = useForm({
        reason: '',
    });

    // Form for revenue share override
    const shareForm = useForm({
        id: null,
        course_id: '',
        teacher_id: '',
        instructor_share_percentage: '70',
        platform_share_percentage: '30',
        notes: '',
        is_active: true,
    });

    const handleStatusFilter = (status) => {
        router.get(
            route('admin.payouts.index'),
            { status },
            { preserveState: true, replace: true }
        );
    };

    const submitApproval = (e) => {
        e.preventDefault();
        approveForm.post(route('admin.payouts.approve', approveModalPayout.id), {
            onSuccess: () => {
                setApproveModalPayout(null);
                approveForm.reset();
            },
        });
    };

    const submitRejection = (e) => {
        e.preventDefault();
        rejectForm.post(route('admin.payouts.reject', rejectModalPayout.id), {
            onSuccess: () => {
                setRejectModalPayout(null);
                rejectForm.reset();
            },
        });
    };

    const submitRevenueShare = (e) => {
        e.preventDefault();
        shareForm.post(route('admin.revenue-shares.save'), {
            onSuccess: () => {
                setShareModalOpen(false);
                shareForm.reset();
            },
        });
    };

    const deleteRevenueShare = (id) => {
        if (confirm('আপনি কি নিশ্চিত যে এই রেভিনিউ শেয়ার নিয়মটি মুছে ফেলতে চান?')) {
            router.delete(route('admin.revenue-shares.destroy', id));
        }
    };

    return (
        <DashboardLayout title="ওয়ালেট ও পেআউট প্রশাসন">
            <Head title="ওয়ালেট ও পেআউট প্রশাসন - Taallum BD" />

            <div className="space-y-8 p-4 md:p-6 lg:p-8">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <span>অ্যাডমিন প্যানেল</span>
                            <span>•</span>
                            <span className="text-emerald-700">টিচার ইকোনমি</span>
                        </div>
                        <h1 className="mt-1 font-serif text-2xl font-bold tracking-tight text-brand-dark sm:text-3xl">
                            শিক্ষক ওয়ালেট ও পেআউট প্রশাসন
                        </h1>
                        <p className="mt-1 text-sm text-slate-500">
                            শিক্ষকদের উত্তোলনের আবেদন যাচাই, কমিশন অনুমোদন এবং রেভিনিউ শেয়ার নীতি নিয়ন্ত্রণ করুন।
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => {
                                shareForm.reset();
                                setShareModalOpen(true);
                            }}
                            className="flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-brand-deep"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                            <span>নতুন রেভিনিউ শেয়ার নিয়ম</span>
                        </button>
                    </div>
                </div>

                {/* Platform KPI Metrics Row */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Platform Available Liabilities */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                            <span>শিক্ষক ব্যালেন্স দেনা</span>
                            <span className="rounded-lg bg-blue-50 p-1.5 text-blue-600">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 font-serif text-2xl font-extrabold text-slate-900">
                            ৳ {metrics.total_available_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </div>
                        <p className="mt-1 text-xs text-slate-400">সকল শিক্ষকের মোট উত্তোলনযোগ্য ব্যালেন্স</p>
                    </div>

                    {/* Pending Escrow */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                            <span>স্থগিত এসক্রো ব্যালেন্স</span>
                            <span className="rounded-lg bg-amber-50 p-1.5 text-amber-600">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 font-serif text-2xl font-extrabold text-amber-900">
                            ৳ {metrics.total_pending_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </div>
                        <p className="mt-1 text-xs text-slate-400">৭ দিনের রিফান্ড উইন্ডোতে রক্ষিত</p>
                    </div>

                    {/* Pending Payout Queue */}
                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-emerald-800">
                            <span>অপেক্ষমাণ পেআউট আবেদন</span>
                            <span className="rounded-full bg-emerald-600 px-2 py-0.5 text-[11px] font-bold text-white">
                                {metrics.pending_requests_count} টি
                            </span>
                        </div>
                        <div className="mt-3 font-serif text-2xl font-extrabold text-emerald-950">
                            ৳ {metrics.pending_requests_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </div>
                        <p className="mt-1 text-xs text-emerald-700">অনুমোদনের জন্য অপেক্ষমাণ মোট দাবি</p>
                    </div>

                    {/* Total Paid Out */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                            <span>মোট পরিশোধিত পেআউট</span>
                            <span className="rounded-lg bg-purple-50 p-1.5 text-purple-600">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 font-serif text-2xl font-extrabold text-slate-900">
                            ৳ {metrics.total_paid_out_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </div>
                        <p className="mt-1 text-xs text-slate-400">ডিফল্ট শেয়ার: {metrics.default_instructor_percentage}% শিক্ষক / {metrics.default_platform_percentage}% প্ল্যাটফর্ম</p>
                    </div>
                </div>

                {/* Tab Switcher */}
                <div className="flex items-center gap-2 border-b border-slate-200">
                    <button
                        type="button"
                        onClick={() => setActiveTab('queue')}
                        className={`flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold transition-all ${
                            activeTab === 'queue'
                                ? 'border-brand-dark text-brand-dark'
                                : 'border-transparent text-slate-400 hover:text-slate-700'
                        }`}
                    >
                        <span>পেআউট উত্তোলন কিউ (Payout Requests)</span>
                        {metrics.pending_requests_count > 0 && (
                            <span className="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">
                                {metrics.pending_requests_count}
                            </span>
                        )}
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveTab('shares')}
                        className={`border-b-2 px-4 py-3 text-sm font-bold transition-all ${
                            activeTab === 'shares'
                                ? 'border-brand-dark text-brand-dark'
                                : 'border-transparent text-slate-400 hover:text-slate-700'
                        }`}
                    >
                        <span>রেভিনিউ শেয়ার নীতিমালা ({revenue_shares.length} টি বিশেষ নিয়ম)</span>
                    </button>
                </div>

                {/* Tab 1: Payout Queue */}
                {activeTab === 'queue' && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {/* Status Filter Header */}
                        <div className="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-center gap-2">
                                {[
                                    { id: 'pending', label: 'অপেক্ষমাণ (Pending)' },
                                    { id: 'paid', label: 'পরিশোধিত (Paid)' },
                                    { id: 'rejected', label: 'বাতিলকৃত (Rejected)' },
                                    { id: 'all', label: 'সকল অনুরোধ' },
                                ].map((tab) => (
                                    <button
                                        key={tab.id}
                                        type="button"
                                        onClick={() => handleStatusFilter(tab.id)}
                                        className={`rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                            current_status === tab.id
                                                ? 'bg-brand-dark text-white'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                        }`}
                                    >
                                        {tab.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Payouts Table */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th className="px-5 py-3.5">শিক্ষক বিবরণ</th>
                                        <th className="px-5 py-3.5">আবেদনের সময়</th>
                                        <th className="px-5 py-3.5">উত্তোলন মাধ্যম</th>
                                        <th className="px-5 py-3.5">অ্যাকাউন্ট তথ্য</th>
                                        <th className="px-5 py-3.5 text-right">পরিমাণ (BDT)</th>
                                        <th className="px-5 py-3.5 text-center">স্ট্যাটাস</th>
                                        <th className="px-5 py-3.5 text-right">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {payouts.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="py-12 text-center text-sm text-slate-400">
                                                এই ক্যাটাগরিতে কোনো পেআউট অনুরোধ পাওয়া যায়নি।
                                            </td>
                                        </tr>
                                    ) : (
                                        payouts.data.map((p) => (
                                            <tr key={p.id} className="hover:bg-slate-50/50">
                                                <td className="px-5 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <img
                                                            src={p.teacher.avatar_url}
                                                            alt={p.teacher.name}
                                                            className="h-9 w-9 rounded-full object-cover border border-slate-200"
                                                        />
                                                        <div>
                                                            <div className="font-bold text-slate-900">{p.teacher.name}</div>
                                                            <div className="text-xs text-slate-500">{p.teacher.email}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-5 py-4 text-xs text-slate-600">
                                                    {p.requested_at}
                                                </td>
                                                <td className="px-5 py-4">
                                                    <span className="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-800">
                                                        {p.method}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-4 text-xs text-slate-600">
                                                    <div className="font-mono font-semibold">{p.account_info?.account_number}</div>
                                                    {p.account_info?.bank_name && (
                                                        <div className="text-[11px] text-slate-400">
                                                            {p.account_info.bank_name} ({p.account_info.branch_name || 'Main'})
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-5 py-4 text-right font-serif text-base font-bold text-slate-900">
                                                    ৳ {p.amount_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="px-5 py-4 text-center">
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-1 text-xs font-bold ${
                                                            p.status === 'paid'
                                                                ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                                                                : p.status === 'pending'
                                                                ? 'bg-amber-50 text-amber-800 border border-amber-200'
                                                                : 'bg-red-50 text-red-800 border border-red-200'
                                                        }`}
                                                    >
                                                        {p.status === 'paid'
                                                            ? 'পরিশোধিত'
                                                            : p.status === 'pending'
                                                            ? 'অপেক্ষমাণ'
                                                            : 'বাতিলকৃত'}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {p.status === 'pending' && (
                                                            <>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => setApproveModalPayout(p)}
                                                                    className="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700"
                                                                >
                                                                    পরিশোধ মার্ক করুন
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => setRejectModalPayout(p)}
                                                                    className="rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100"
                                                                >
                                                                    বাতিল
                                                                </button>
                                                            </>
                                                        )}
                                                        <a
                                                            href={route('admin.payouts.statement', {
                                                                teacher: p.teacher.id,
                                                                year: new Date().getFullYear(),
                                                                month: new Date().getMonth() + 1,
                                                            })}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200"
                                                            title="স্টেটমেন্ট দেখুন"
                                                        >
                                                            স্টেটমেন্ট
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {payouts.links && payouts.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3">
                                <span className="text-xs text-slate-500">
                                    মোট {payouts.total} টির মধ্যে {payouts.from} থেকে {payouts.to} দেখানো হচ্ছে
                                </span>
                                <div className="flex gap-1">
                                    {payouts.links.map((link, idx) => (
                                        <Link
                                            key={idx}
                                            href={link.url || '#'}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                            className={`rounded-lg px-2.5 py-1 text-xs font-bold ${
                                                link.active
                                                    ? 'bg-brand-dark text-white'
                                                    : link.url
                                                    ? 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                                                    : 'text-slate-300 pointer-events-none'
                                            }`}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Tab 2: Revenue Shares Overrides */}
                {activeTab === 'shares' && (
                    <div className="space-y-6">
                        {/* Default Info Box */}
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-base font-bold text-slate-800">প্ল্যাটফর্মের ডিফল্ট রেভিনিউ শেয়ার নীতিমালা</h3>
                            <p className="mt-1 text-xs text-slate-500">
                                যেসকল কোর্স বা শিক্ষকের জন্য আলাদা চুক্তি নেই, তাদের জন্য এই ডিফল্ট শতকরা হার প্রযোজ্য:
                            </p>

                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <div className="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                                    <span className="text-xs font-bold uppercase text-emerald-800">শিক্ষক পাবেন (Default Instructor Share)</span>
                                    <div className="mt-1 text-2xl font-extrabold text-emerald-950">
                                        {metrics.default_instructor_percentage}%
                                    </div>
                                </div>
                                <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <span className="text-xs font-bold uppercase text-slate-600">প্ল্যাটফর্ম কমিশন (Platform Fee)</span>
                                    <div className="mt-1 text-2xl font-extrabold text-slate-800">
                                        {metrics.default_platform_percentage}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Overrides Table */}
                        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-100 p-5">
                                <h3 className="text-base font-bold text-slate-800">কাস্টম রেভিনিউ শেয়ার নিয়মাবলী</h3>
                                <p className="text-xs text-slate-500">নির্দিষ্ট কোনো কোর্স বা শিক্ষকের জন্য বিশেষ কমিশন হার</p>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th className="px-5 py-3.5">কোর্স / শিক্ষক</th>
                                            <th className="px-5 py-3.5">শিক্ষক পাবেন (%)</th>
                                            <th className="px-5 py-3.5">প্ল্যাটফর্ম কমিশন (%)</th>
                                            <th className="px-5 py-3.5">স্ট্যাটাস</th>
                                            <th className="px-5 py-3.5">নোট</th>
                                            <th className="px-5 py-3.5 text-right">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {revenue_shares.length === 0 ? (
                                            <tr>
                                                <td colSpan={6} className="py-8 text-center text-sm text-slate-400">
                                                    কোনো বিশেষ নিয়ম যুক্ত করা নেই। ডিফল্ট ৭০% / ৩০% কার্যকর রয়েছে।
                                                </td>
                                            </tr>
                                        ) : (
                                            revenue_shares.map((rule) => (
                                                <tr key={rule.id} className="hover:bg-slate-50/50">
                                                    <td className="px-5 py-4 font-semibold text-slate-800">
                                                        {rule.course_title ? (
                                                            <span>📘 কোর্স: {rule.course_title}</span>
                                                        ) : (
                                                            <span>👤 শিক্ষক: {rule.teacher_name}</span>
                                                        )}
                                                    </td>
                                                    <td className="px-5 py-4 font-bold text-emerald-700">
                                                        {rule.instructor_share_percentage}%
                                                    </td>
                                                    <td className="px-5 py-4 font-bold text-slate-700">
                                                        {rule.platform_share_percentage}%
                                                    </td>
                                                    <td className="px-5 py-4">
                                                        <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                                            {rule.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়'}
                                                        </span>
                                                    </td>
                                                    <td className="px-5 py-4 text-xs text-slate-500">
                                                        {rule.notes || '—'}
                                                    </td>
                                                    <td className="px-5 py-4 text-right">
                                                        <button
                                                            type="button"
                                                            onClick={() => deleteRevenueShare(rule.id)}
                                                            className="text-xs font-bold text-red-600 hover:underline"
                                                        >
                                                            মুছে ফেলুন
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Approve Modal */}
            {approveModalPayout && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="text-lg font-bold text-slate-900">পেআউট পরিশোধ নিশ্চিতকরণ</h3>
                        <p className="mt-1 text-xs text-slate-500">
                            শিক্ষক <span className="font-bold text-slate-700">{approveModalPayout.teacher.name}</span>-কে ৳{approveModalPayout.amount_bdt} টাকা প্রদান করা হয়েছে বলে চিহ্নিত করুন।
                        </p>

                        <div className="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                            <div>মাধ্যম: <span className="font-bold">{approveModalPayout.method}</span></div>
                            <div>নম্বর: <span className="font-mono font-bold">{approveModalPayout.account_info?.account_number}</span></div>
                        </div>

                        <form onSubmit={submitApproval} className="mt-4 space-y-3">
                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">ট্রানজ্যাকশন আইডি (TrxID) / ব্যাংক রেফারেন্স</label>
                                <input
                                    type="text"
                                    value={approveForm.data.transaction_reference}
                                    onChange={(e) => approveForm.setData('transaction_reference', e.target.value)}
                                    placeholder="উদা: 9A8B7C6D5E"
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">নোট (ঐচ্ছিক)</label>
                                <textarea
                                    value={approveForm.data.notes}
                                    onChange={(e) => approveForm.setData('notes', e.target.value)}
                                    rows={2}
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                />
                            </div>

                            <div className="mt-6 flex justify-end gap-3 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setApproveModalPayout(null)}
                                    className="rounded-xl border px-4 py-2 text-xs font-bold text-slate-600"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={approveForm.processing}
                                    className="rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow hover:bg-emerald-700"
                                >
                                    পরিশোধিত হিসেবে সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Reject Modal */}
            {rejectModalPayout && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="text-lg font-bold text-red-600">পেআউট বাতিল করুন</h3>
                        <p className="mt-1 text-xs text-slate-500">
                            বাতিল করলে ৳{rejectModalPayout.amount_bdt} টাকা স্বয়ংক্রিয়ভাবে শিক্ষকের ওয়ালেট ব্যালেন্সে ফেরত দেওয়া হবে।
                        </p>

                        <form onSubmit={submitRejection} className="mt-4 space-y-3">
                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">বাতিলের কারণ (শিক্ষক দেখতে পাবেন)</label>
                                <textarea
                                    value={rejectForm.data.reason}
                                    onChange={(e) => rejectForm.setData('reason', e.target.value)}
                                    rows={3}
                                    placeholder="উদা: ভুল অ্যাকাউন্ট নম্বর বা লেনদেন সীমা অতিক্রান্ত..."
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                    required
                                />
                                {rejectForm.errors.reason && (
                                    <p className="mt-1 text-xs text-red-600">{rejectForm.errors.reason}</p>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end gap-3 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setRejectModalPayout(null)}
                                    className="rounded-xl border px-4 py-2 text-xs font-bold text-slate-600"
                                >
                                    বন্ধ করুন
                                </button>
                                <button
                                    type="submit"
                                    disabled={rejectForm.processing}
                                    className="rounded-xl bg-red-600 px-5 py-2 text-xs font-bold text-white shadow hover:bg-red-700"
                                >
                                    বাতিল নিশ্চিত করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Revenue Share Modal */}
            {shareModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                        <h3 className="text-lg font-bold text-slate-900">নতুন রেভিনিউ শেয়ার নিয়ম</h3>
                        <p className="mt-1 text-xs text-slate-500">কোর্স অথবা শিক্ষক বাছাই করে কাস্টম কমিশন হার নির্ধারণ করুন।</p>

                        <form onSubmit={submitRevenueShare} className="mt-4 space-y-3">
                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">কোর্সের জন্য (ঐচ্ছিক)</label>
                                <select
                                    value={shareForm.data.course_id}
                                    onChange={(e) => shareForm.setData('course_id', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                >
                                    <option value="">— কোনো কোর্স নির্দিষ্ট নয় —</option>
                                    {courses.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.title}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">অথবা শিক্ষকের জন্য (ঐচ্ছিক)</label>
                                <select
                                    value={shareForm.data.teacher_id}
                                    onChange={(e) => shareForm.setData('teacher_id', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                >
                                    <option value="">— কোনো শিক্ষক নির্দিষ্ট নয় —</option>
                                    {teachers.map((t) => (
                                        <option key={t.id} value={t.id}>
                                            {t.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-bold uppercase text-slate-600">শিক্ষক পাবেন (%)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.5"
                                        value={shareForm.data.instructor_share_percentage}
                                        onChange={(e) => {
                                            const val = parseFloat(e.target.value) || 0;
                                            shareForm.setData({
                                                ...shareForm.data,
                                                instructor_share_percentage: val,
                                                platform_share_percentage: Math.max(0, 100 - val),
                                            });
                                        }}
                                        className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm font-bold text-emerald-700"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-bold uppercase text-slate-600">প্ল্যাটফর্ম কমিশন (%)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.5"
                                        value={shareForm.data.platform_share_percentage}
                                        readOnly
                                        className="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 text-sm font-bold text-slate-600"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">বিশেষ নোট</label>
                                <input
                                    type="text"
                                    value={shareForm.data.notes}
                                    onChange={(e) => shareForm.setData('notes', e.target.value)}
                                    placeholder="উদা: পার্টনারশিপ স্পেশাল ৮০/২০ হার..."
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                />
                            </div>

                            <div className="mt-6 flex justify-end gap-3 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setShareModalOpen(false)}
                                    className="rounded-xl border px-4 py-2 text-xs font-bold text-slate-600"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={shareForm.processing}
                                    className="rounded-xl bg-brand-dark px-5 py-2 text-xs font-bold text-white shadow hover:bg-brand-deep"
                                >
                                    সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </DashboardLayout>
    );
}
