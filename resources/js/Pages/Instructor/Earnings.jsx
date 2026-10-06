import React, { useState } from 'react';
import { Head, useForm, router, Link } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';

export default function Earnings({
    metrics,
    chart_data = [],
    transactions,
    payout_requests = [],
    filters = {},
    teacher = {},
}) {
    const [payoutModalOpen, setPayoutModalOpen] = useState(false);
    const [selectedStatementYear, setSelectedStatementYear] = useState(new Date().getFullYear());
    const [selectedStatementMonth, setSelectedStatementMonth] = useState(new Date().getMonth() + 1);

    const { data, setData, post, processing, errors, reset } = useForm({
        amount_bdt: '',
        method: 'bkash',
        account_number: '',
        account_holder_name: '',
        bank_name: '',
        branch_name: '',
        routing_number: '',
        notes: '',
    });

    const handleFilterChange = (key, value) => {
        router.get(
            route('instructor.earnings'),
            { ...filters, [key]: value },
            { preserveState: true, replace: true }
        );
    };

    const handlePayoutSubmit = (e) => {
        e.preventDefault();
        post(route('instructor.earnings.payout'), {
            onSuccess: () => {
                setPayoutModalOpen(false);
                reset();
            },
        });
    };

    const maxChartAmount = Math.max(...chart_data.map((d) => d.amount_bdt), 100);

    return (
        <DashboardLayout title="শিক্ষক আয় ও ওয়ালেট">
            <Head title="শিক্ষক আয় ও ওয়ালেট - Taallum BD" />

            <div className="space-y-8 p-4 md:p-6 lg:p-8">
                {/* Header section */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <span>শিক্ষক পোর্টাল</span>
                            <span>•</span>
                            <span className="text-emerald-700">অর্থ ও ওয়ালেট</span>
                        </div>
                        <h1 className="mt-1 font-serif text-2xl font-bold tracking-tight text-brand-dark sm:text-3xl">
                            আয় ও আর্থিক ওয়ালেট
                        </h1>
                        <p className="mt-1 text-sm text-slate-500">
                            আপনার কোর্স বিক্রয় কমিশন, স্থগিত ব্যালেন্স এবং উত্তোলন হিস্টোরি এক নজরে দেখুন।
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        {/* Statement Export Form */}
                        <div className="flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
                            <select
                                value={selectedStatementMonth}
                                onChange={(e) => setSelectedStatementMonth(e.target.value)}
                                className="rounded-lg border-0 bg-transparent py-1 text-xs font-bold text-slate-700 focus:ring-0"
                            >
                                {[
                                    { m: 1, name: 'জানুয়ারি' },
                                    { m: 2, name: 'ফেব্রুয়ারি' },
                                    { m: 3, name: 'মার্চ' },
                                    { m: 4, name: 'এপ্রিল' },
                                    { m: 5, name: 'মে' },
                                    { m: 6, name: 'জুন' },
                                    { m: 7, name: 'জুলাই' },
                                    { m: 8, name: 'আগস্ট' },
                                    { m: 9, name: 'সেপ্টেম্বর' },
                                    { m: 10, name: 'অক্টোবর' },
                                    { m: 11, name: 'নভেম্বর' },
                                    { m: 12, name: 'ডিসেম্বর' },
                                ].map((item) => (
                                    <option key={item.m} value={item.m}>
                                        {item.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={selectedStatementYear}
                                onChange={(e) => setSelectedStatementYear(e.target.value)}
                                className="rounded-lg border-0 bg-transparent py-1 text-xs font-bold text-slate-700 focus:ring-0"
                            >
                                {[2026, 2025, 2024].map((y) => (
                                    <option key={y} value={y}>
                                        {y}
                                    </option>
                                ))}
                            </select>
                            <a
                                href={route('instructor.earnings.statement', {
                                    year: selectedStatementYear,
                                    month: selectedStatementMonth,
                                })}
                                target="_blank"
                                rel="noreferrer"
                                className="flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200"
                            >
                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>স্টেটমেন্ট</span>
                            </a>
                        </div>

                        {/* Request Payout Button */}
                        <button
                            type="button"
                            onClick={() => setPayoutModalOpen(true)}
                            className="flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-deep hover:shadow-lg focus:ring-2 focus:ring-brand-dark/20"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>অর্থ উত্তোলন (Payout)</span>
                        </button>
                    </div>
                </div>

                {/* KPI Metrics Row */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Available Balance */}
                    <div className="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 to-white p-5 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-emerald-800">উত্তোলনযোগ্য ব্যালেন্স</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-700">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-1">
                            <span className="text-sm font-bold text-emerald-800">৳</span>
                            <span className="font-serif text-3xl font-extrabold text-emerald-950">
                                {metrics.available_balance_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                            </span>
                        </div>
                        <p className="mt-2 text-xs text-emerald-700/80">
                            যে-কোনো সময় উত্তোলন করা যাবে (ন্যূনতম ৳{metrics.min_payout_amount_bdt})
                        </p>
                    </div>

                    {/* Pending Balance (Escrow) */}
                    <div className="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/70 to-white p-5 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-amber-800">স্থগিত ব্যালেন্স (Escrow)</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-700">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-1">
                            <span className="text-sm font-bold text-amber-800">৳</span>
                            <span className="font-serif text-3xl font-extrabold text-amber-950">
                                {metrics.pending_balance_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                            </span>
                        </div>
                        <p className="mt-2 text-xs text-amber-700/80">
                            রিফান্ড নীতি অনুযায়ী বিক্রয়ের ৭ দিন পর মূল ব্যালেন্সে যোগ হবে
                        </p>
                    </div>

                    {/* Lifetime Total Earnings */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-500">সর্বমোট কমিশন আয়</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-1">
                            <span className="text-sm font-bold text-slate-400">৳</span>
                            <span className="font-serif text-3xl font-extrabold text-slate-800">
                                {metrics.lifetime_earnings_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                            </span>
                        </div>
                        <div className="mt-2 flex items-center justify-between text-xs text-slate-500">
                            <span>কমিশন হার: {metrics.instructor_share_percentage}%</span>
                            <span className="font-bold text-slate-700">মোট ব্যালেন্স: ৳{metrics.total_balance_bdt.toFixed(0)}</span>
                        </div>
                    </div>

                    {/* Total Paid Out */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-500">সফলভাবে উত্তোলন</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-1">
                            <span className="text-sm font-bold text-slate-400">৳</span>
                            <span className="font-serif text-3xl font-extrabold text-slate-800">
                                {metrics.total_paid_out_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                            </span>
                        </div>
                        <p className="mt-2 text-xs text-slate-500">
                            {metrics.pending_payouts_bdt > 0 ? (
                                <span className="font-bold text-amber-600">
                                    ৳{metrics.pending_payouts_bdt.toFixed(2)} বর্তমানে প্রক্রিয়ায় রয়েছে
                                </span>
                            ) : (
                                'কোনো পেন্ডিং উত্তোলন অনুরোধ নেই'
                            )}
                        </p>
                    </div>
                </div>

                {/* Revenue Trend Chart & Payout Summary */}
                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Monthly Trend Chart */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <div className="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h2 className="text-base font-bold text-slate-800">মাসিক আয়ের ট্রেন্ড (বিগত ৬ মাস)</h2>
                                <p className="text-xs text-slate-500">প্রতি মাসে আপনার কোর্স বিক্রয় হতে প্রাপ্ত মোট কমিশন</p>
                            </div>
                        </div>

                        {/* Custom Pure-CSS / SVG Bar Chart */}
                        <div className="mt-6 flex h-52 items-end justify-between gap-2 pt-6 sm:gap-6">
                            {chart_data.map((item) => {
                                const heightPercent = Math.min(Math.round((item.amount_bdt / maxChartAmount) * 100), 100);
                                return (
                                    <div key={item.month_key} className="flex flex-1 flex-col items-center gap-2">
                                        <div className="text-[11px] font-bold text-slate-600">
                                            {item.amount_bdt > 0 ? `৳${Math.round(item.amount_bdt)}` : '—'}
                                        </div>
                                        <div className="relative flex h-36 w-full max-w-[42px] items-end justify-center rounded-xl bg-slate-100 p-1">
                                            <div
                                                style={{ height: `${Math.max(heightPercent, 4)}%` }}
                                                className={`w-full rounded-lg transition-all duration-500 ${
                                                    item.amount_bdt > 0
                                                        ? 'bg-gradient-to-t from-emerald-600 to-emerald-400 shadow-sm'
                                                        : 'bg-slate-200'
                                                }`}
                                            ></div>
                                        </div>
                                        <div className="text-center text-[11px] font-medium text-slate-500">
                                            {item.month_label.split(' ')[0]}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Quick Consultation & Withdrawal Rules */}
                    <div className="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div>
                            <h2 className="text-base font-bold text-slate-800">আর্থিক নীতি ও পরামর্শ ফি</h2>
                            <p className="mt-1 text-xs text-slate-500">তা'ল্লুম বিডি শিক্ষক ওয়ালেট সম্পর্কিত নিয়ামাবলি</p>

                            <div className="mt-5 space-y-4">
                                <div className="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5">
                                    <div className="text-xs font-bold text-slate-700">কমিশন বণ্টন হার</div>
                                    <div className="mt-1 text-xs text-slate-600">
                                        আপনি কোর্সের প্রতিটি বিক্রিত মূল্যের <span className="font-bold text-emerald-700">{metrics.instructor_share_percentage}%</span> পেয়ে থাকেন।
                                    </div>
                                </div>

                                <div className="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5">
                                    <div className="text-xs font-bold text-slate-700">রিফান্ড সুরক্ষা সময়সীমা</div>
                                    <div className="mt-1 text-xs text-slate-600">
                                        শিক্ষার্থীদের ৭ দিনের সন্তুষ্টি গ্যারান্টি থাকায় কমিশন বিক্রয়ের ৭ দিন পর্যন্ত স্থগিত (Pending) থাকে।
                                    </div>
                                </div>

                                <div className="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5">
                                    <div className="text-xs font-bold text-slate-700">পরামর্শ/কনসাল্টেশন ফি সেটিংস</div>
                                    <div className="mt-1 text-xs text-slate-600">
                                        বর্তমান ফি: <span className="font-bold text-brand-dark">{teacher.consultation_fee_bdt ? `৳${teacher.consultation_fee_bdt}` : 'বিনামূল্যে'}</span> (
                                        <Link href={route('instructor.profile.teacher.edit')} className="text-emerald-600 hover:underline">
                                            প্রোফাইল থেকে পরিবর্তন করুন
                                        </Link>
                                        )
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-6 border-t border-slate-100 pt-4 text-center">
                            <span className="text-[11px] text-slate-400">সহায়তার জন্য যোগাযোগ করুন: finance@taallumbd.com</span>
                        </div>
                    </div>
                </div>

                {/* Payout History Section */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-2 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-base font-bold text-slate-800">উত্তোলন অনুরোধের বিবরণ (Payout Requests)</h2>
                            <p className="text-xs text-slate-500">আপনার করা সাম্প্রতিক পেআউট আবেদন ও স্ট্যাটাস</p>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th className="px-5 py-3.5">আবেদনের তারিখ</th>
                                    <th className="px-5 py-3.5">উত্তোলন মাধ্যম</th>
                                    <th className="px-5 py-3.5">পরিমাণ (BDT)</th>
                                    <th className="px-5 py-3.5">অ্যাকাউন্ট বিবরণ</th>
                                    <th className="px-5 py-3.5">স্ট্যাটাস</th>
                                    <th className="px-5 py-3.5">ট্রানজ্যাকশন আইডি / নোট</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {payout_requests.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="py-8 text-center text-sm text-slate-400">
                                            কোনো উত্তোলন অনুরোধের তথ্য পাওয়া যায়নি।
                                        </td>
                                    </tr>
                                ) : (
                                    payout_requests.map((p) => (
                                        <tr key={p.id} className="hover:bg-slate-50/50">
                                            <td className="px-5 py-4 text-xs text-slate-600">{p.requested_at}</td>
                                            <td className="px-5 py-4">
                                                <span className="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-800">
                                                    {p.method}
                                                </span>
                                            </td>
                                            <td className="px-5 py-4 font-serif font-bold text-slate-900">
                                                ৳ {p.amount_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-5 py-4 text-xs text-slate-600">
                                                <div>{p.account_info?.account_number || '—'}</div>
                                                {p.account_info?.bank_name && (
                                                    <div className="text-[11px] text-slate-400">{p.account_info.bank_name}</div>
                                                )}
                                            </td>
                                            <td className="px-5 py-4">
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
                                            <td className="px-5 py-4 text-xs text-slate-600">
                                                {p.transaction_reference && (
                                                    <span className="font-mono text-emerald-700 font-semibold">{p.transaction_reference}</span>
                                                )}
                                                {p.rejection_reason && (
                                                    <span className="text-red-600">{p.rejection_reason}</span>
                                                )}
                                                {!p.transaction_reference && !p.rejection_reason && '—'}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Ledger Transactions Section */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-base font-bold text-slate-800">ওয়ালেট লেজার (Wallet Transactions)</h2>
                            <p className="text-xs text-slate-500">দ্বিমুখী হিসাবনিকাশ ও সম্পূর্ণ লেনদেন তালিকা</p>
                        </div>

                        {/* Filter Tabs */}
                        <div className="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                onClick={() => handleFilterChange('type', 'all')}
                                className={`rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    filters.type === 'all'
                                        ? 'bg-brand-dark text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }`}
                            >
                                সব লেনদেন
                            </button>
                            <button
                                type="button"
                                onClick={() => handleFilterChange('type', 'credit')}
                                className={`rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    filters.type === 'credit'
                                        ? 'bg-emerald-700 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }`}
                            >
                                জমা (Credit)
                            </button>
                            <button
                                type="button"
                                onClick={() => handleFilterChange('type', 'debit')}
                                className={`rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    filters.type === 'debit'
                                        ? 'bg-red-700 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }`}
                            >
                                কর্তন (Debit)
                            </button>
                            <button
                                type="button"
                                onClick={() => handleFilterChange('balance_type', filters.balance_type === 'pending' ? 'all' : 'pending')}
                                className={`rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    filters.balance_type === 'pending'
                                        ? 'bg-amber-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }`}
                            >
                                শুধু স্থগিত (Pending)
                            </button>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th className="px-5 py-3.5">তারিখ</th>
                                    <th className="px-5 py-3.5">বিবরণ</th>
                                    <th className="px-5 py-3.5">ধরণ</th>
                                    <th className="px-5 py-3.5">ব্যালেন্স প্রকার</th>
                                    <th className="px-5 py-3.5 text-right">পরিমাণ (BDT)</th>
                                    <th className="px-5 py-3.5 text-right">ব্যালেন্স পরবর্তী (BDT)</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {transactions.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="py-8 text-center text-sm text-slate-400">
                                            কোনো লেনদেন রেকর্ড পাওয়া যায়নি।
                                        </td>
                                    </tr>
                                ) : (
                                    transactions.data.map((t) => (
                                        <tr key={t.id} className="hover:bg-slate-50/50">
                                            <td className="px-5 py-3.5 text-xs text-slate-600">
                                                <div>{new Date(t.created_at).toLocaleDateString('bn-BD')}</div>
                                                <div className="text-[10px] text-slate-400">{t.created_at_human}</div>
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-slate-800 font-medium">
                                                {t.description}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span
                                                    className={`inline-flex rounded-md px-2 py-0.5 text-[11px] font-bold ${
                                                        t.type === 'credit'
                                                            ? 'bg-emerald-50 text-emerald-800'
                                                            : 'bg-red-50 text-red-800'
                                                    }`}
                                                >
                                                    {t.type === 'credit' ? '+ জমা' : '- কর্তন'}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-xs">
                                                <span
                                                    className={`rounded px-1.5 py-0.5 text-[10px] font-bold ${
                                                        t.balance_type === 'pending'
                                                            ? 'bg-amber-100 text-amber-800'
                                                            : 'bg-slate-100 text-slate-700'
                                                    }`}
                                                >
                                                    {t.balance_type === 'pending' ? 'স্থগিত (Escrow)' : 'মূল ব্যালেন্স'}
                                                </span>
                                            </td>
                                            <td
                                                className={`px-5 py-3.5 text-right font-serif font-bold ${
                                                    t.type === 'credit' ? 'text-emerald-700' : 'text-red-600'
                                                }`}
                                            >
                                                {t.type === 'credit' ? '+' : '-'} ৳ {t.amount_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-5 py-3.5 text-right font-serif font-semibold text-slate-700">
                                                ৳ {t.balance_after_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {transactions.links && transactions.links.length > 3 && (
                        <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3">
                            <span className="text-xs text-slate-500">
                                মোট {transactions.total} টি লেনদেনের মধ্যে {transactions.from} থেকে {transactions.to} দেখানো হচ্ছে
                            </span>
                            <div className="flex gap-1">
                                {transactions.links.map((link, idx) => (
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
            </div>

            {/* Payout Request Modal */}
            {payoutModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition-all">
                        <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 className="text-lg font-bold text-slate-900">অর্থ উত্তোলন অনুরোধ (Payout Request)</h3>
                            <button
                                type="button"
                                onClick={() => setPayoutModalOpen(false)}
                                className="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            >
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handlePayoutSubmit} className="mt-4 space-y-4">
                            {/* Current Available Alert */}
                            <div className="flex items-center justify-between rounded-xl bg-emerald-50 p-3 text-xs font-semibold text-emerald-900">
                                <span>বর্তমান উত্তোলনযোগ্য ব্যালেন্স:</span>
                                <span className="font-serif text-sm font-bold text-emerald-700">
                                    ৳ {metrics.available_balance_bdt.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                </span>
                            </div>

                            {/* Amount Input */}
                            <div>
                                <div className="flex items-center justify-between">
                                    <label className="text-xs font-bold uppercase text-slate-600">উত্তোলনের পরিমাণ (টাকা)</label>
                                    <button
                                        type="button"
                                        onClick={() => setData('amount_bdt', metrics.available_balance_bdt)}
                                        className="text-xs font-bold text-emerald-700 hover:underline"
                                    >
                                        সর্বোচ্চ ব্যালেন্স দিন
                                    </button>
                                </div>
                                <div className="relative mt-1">
                                    <span className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">
                                        ৳
                                    </span>
                                    <input
                                        type="number"
                                        step="1"
                                        min={metrics.min_payout_amount_bdt}
                                        max={metrics.available_balance_bdt}
                                        value={data.amount_bdt}
                                        onChange={(e) => setData('amount_bdt', e.target.value)}
                                        placeholder={`সর্বনিম্ন ${metrics.min_payout_amount_bdt}`}
                                        className="w-full rounded-xl border border-slate-200 py-2.5 pl-8 pr-3 text-sm focus:border-brand-dark focus:ring-brand-dark"
                                        required
                                    />
                                </div>
                                {errors.amount_bdt && (
                                    <p className="mt-1 text-xs text-red-600 font-semibold">{errors.amount_bdt}</p>
                                )}
                            </div>

                            {/* Withdrawal Method */}
                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">উত্তোলন মাধ্যম নির্বাচন করুন</label>
                                <div className="mt-2 grid grid-cols-4 gap-2">
                                    {[
                                        { id: 'bkash', label: 'বিকাশ (bKash)' },
                                        { id: 'nagad', label: 'নগদ (Nagad)' },
                                        { id: 'rocket', label: 'রকেট (Rocket)' },
                                        { id: 'bank', label: 'ব্যাংক ট্রান্সফার' },
                                    ].map((m) => (
                                        <button
                                            key={m.id}
                                            type="button"
                                            onClick={() => setData('method', m.id)}
                                            className={`rounded-xl border p-2 text-center text-xs font-bold transition-all ${
                                                data.method === m.id
                                                    ? 'border-brand-dark bg-brand-dark text-white'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                            }`}
                                        >
                                            {m.label}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Method-specific inputs */}
                            {data.method !== 'bank' ? (
                                <div>
                                    <label className="text-xs font-bold uppercase text-slate-600">
                                        {data.method.toUpperCase()} পার্সোনাল নম্বর
                                    </label>
                                    <input
                                        type="text"
                                        value={data.account_number}
                                        onChange={(e) => setData('account_number', e.target.value)}
                                        placeholder="01XXXXXXXXX"
                                        className="mt-1 w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-brand-dark focus:ring-brand-dark"
                                        required
                                    />
                                    {errors.account_number && (
                                        <p className="mt-1 text-xs text-red-600 font-semibold">{errors.account_number}</p>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    <div>
                                        <label className="text-xs font-bold uppercase text-slate-600">অ্যাকাউন্ট হোল্ডারের নাম</label>
                                        <input
                                            type="text"
                                            value={data.account_holder_name}
                                            onChange={(e) => setData('account_holder_name', e.target.value)}
                                            className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                            required
                                        />
                                    </div>
                                    <div className="grid grid-cols-2 gap-2">
                                        <div>
                                            <label className="text-xs font-bold uppercase text-slate-600">ব্যাংকের নাম</label>
                                            <input
                                                type="text"
                                                value={data.bank_name}
                                                onChange={(e) => setData('bank_name', e.target.value)}
                                                className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="text-xs font-bold uppercase text-slate-600">শাখার নাম (Branch)</label>
                                            <input
                                                type="text"
                                                value={data.branch_name}
                                                onChange={(e) => setData('branch_name', e.target.value)}
                                                className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2">
                                        <div>
                                            <label className="text-xs font-bold uppercase text-slate-600">অ্যাকাউন্ট নম্বর</label>
                                            <input
                                                type="text"
                                                value={data.account_number}
                                                onChange={(e) => setData('account_number', e.target.value)}
                                                className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="text-xs font-bold uppercase text-slate-600">রাউটিং নম্বর (ঐচ্ছিক)</label>
                                            <input
                                                type="text"
                                                value={data.routing_number}
                                                onChange={(e) => setData('routing_number', e.target.value)}
                                                className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Optional Notes */}
                            <div>
                                <label className="text-xs font-bold uppercase text-slate-600">বিশেষ কোনো নির্দেশনা (ঐচ্ছিক)</label>
                                <textarea
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows={2}
                                    className="mt-1 w-full rounded-xl border border-slate-200 py-2 px-3 text-sm"
                                    placeholder="প্রয়োজনীয় কোনো নোট থাকলে লিখুন..."
                                />
                            </div>

                            {/* Submit & Cancel Buttons */}
                            <div className="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => setPayoutModalOpen(false)}
                                    className="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing || metrics.available_balance_bdt < metrics.min_payout_amount_bdt}
                                    className="rounded-xl bg-brand-dark px-5 py-2 text-xs font-bold text-white shadow hover:bg-brand-deep disabled:opacity-50"
                                >
                                    {processing ? 'আবেদন পাঠানো হচ্ছে...' : 'আবেদন নিশ্চিত করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </DashboardLayout>
    );
}
