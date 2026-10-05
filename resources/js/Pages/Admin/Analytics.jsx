import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function AdminAnalytics({
    overview,
    revenue_trend = [],
    dau_trend = [],
    funnel = [],
    cohort_matrix = [],
    top_courses = [],
    top_scholars = []
}) {
    const [daysRange, setDaysRange] = useState(overview.days_range || 30);

    const handleRangeChange = (newDays) => {
        setDaysRange(newDays);
        router.get('/admin/analytics', { days: newDays }, { preserveState: true, preserveScroll: true });
    };

    const maxRevenue = Math.max(...revenue_trend.map(r => r.revenue), 1000);
    const maxDau = Math.max(...dau_trend.map(d => d.dau), 10);

    const funnelBaseCount = funnel.length > 0 ? Math.max(funnel[0].count, 1) : 1;

    return (
        <DashboardLayout title="বিজনেস অ্যানালিটিক্স">
            <Head title="বিজনেস ও গ্রোথ অ্যানালিটিক্স - Taallum BD" />

            {/* Header & Controls */}
            <div className="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>বিজনেস ও গ্রোথ অ্যানালিটিক্স</span>
                        <span className="px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-full">
                            রিয়েল-টাইম
                        </span>
                    </h1>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        DAU/WAU/MAU গ্রোথ, রেভিনিউ ধারা, রিটেনশন কোহর্ট এবং কনভার্শন ফানেল পর্যবেক্ষণ করুন।
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                    {/* Time Range Selector */}
                    <div className="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        {[7, 30, 90].map((d) => (
                            <button
                                key={d}
                                onClick={() => handleRangeChange(d)}
                                className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                                    daysRange === d
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                }`}
                            >
                                {d} দিন
                            </button>
                        ))}
                    </div>

                    {/* CSV Export Button */}
                    <a
                        href="/admin/analytics/export"
                        className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
                    >
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        CSV এক্সপোর্ট
                    </a>
                </div>
            </div>

            {/* KPI Cards */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-6 mb-8">
                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-slate-500">DAU (দৈনিক সক্রিয়)</p>
                    <h3 className="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                        {overview.dau}
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">আজকের সক্রিয় ব্যবহারকারী</p>
                </div>

                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-slate-500">WAU (সাপ্তাহিক সক্রিয়)</p>
                    <h3 className="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                        {overview.wau}
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">বিগত ৭ দিনের সক্রিয়</p>
                </div>

                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-slate-500">MAU (মাসিক সক্রিয়)</p>
                    <h3 className="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                        {overview.mau}
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">বিগত ৩০ দিনের সক্রিয়</p>
                </div>

                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-emerald-600">স্টিকিনেস (DAU/MAU)</p>
                    <h3 className="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                        {overview.stickiness}%
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">এনগেজমেন্ট গভীরতা</p>
                </div>

                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-blue-600">রেভিনিউ ({daysRange} দিন)</p>
                    <h3 className="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                        ৳{overview.revenue_period?.toLocaleString()}
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">অনুমোদিত মোট অর্ডার</p>
                </div>

                <div className="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase text-rose-600">রিফান্ড ({daysRange} দিন)</p>
                    <h3 className="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">
                        ৳{overview.refunds_period?.toLocaleString()}
                    </h3>
                    <p className="text-[11px] text-slate-400 mt-1">ফেরতকৃত মোট টাকা</p>
                </div>
            </div>

            {/* Trends Grid: Revenue & DAU */}
            <div className="grid gap-6 lg:grid-cols-2 mb-8">
                {/* Revenue Trend */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-base font-bold text-slate-900 dark:text-white">দৈনিক রেভিনিউ ট্রেন্ড (BDT)</h2>
                        <span className="text-xs text-slate-400">বিগত {daysRange} দিন</span>
                    </div>
                    <div className="h-44 flex items-end justify-between gap-1 sm:gap-2 pt-4 border-b border-slate-100 dark:border-slate-700">
                        {revenue_trend.slice(-14).map((r) => {
                            const height = Math.max(6, Math.min(100, Math.round((r.revenue / maxRevenue) * 100)));
                            return (
                                <div key={r.date} className="flex-1 flex flex-col items-center h-full justify-end group">
                                    <span className="text-[10px] text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        ৳{Math.round(r.revenue)}
                                    </span>
                                    <div className="w-full max-w-[28px] bg-slate-100 dark:bg-slate-700 rounded-t h-full flex items-end">
                                        <div
                                            style={{ height: `${height}%` }}
                                            className="w-full rounded-t bg-gradient-to-t from-emerald-600 to-teal-400 transition-all"
                                        />
                                    </div>
                                    <span className="mt-1 text-[10px] text-slate-400 truncate max-w-[32px]">
                                        {r.date.slice(5)}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* DAU Trend */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-base font-bold text-slate-900 dark:text-white">দৈনিক সক্রিয় শিক্ষার্থী (DAU)</h2>
                        <span className="text-xs text-slate-400">বিগত {daysRange} দিন</span>
                    </div>
                    <div className="h-44 flex items-end justify-between gap-1 sm:gap-2 pt-4 border-b border-slate-100 dark:border-slate-700">
                        {dau_trend.slice(-14).map((d) => {
                            const height = Math.max(6, Math.min(100, Math.round((d.dau / maxDau) * 100)));
                            return (
                                <div key={d.date} className="flex-1 flex flex-col items-center h-full justify-end group">
                                    <span className="text-[10px] text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        {d.dau}
                                    </span>
                                    <div className="w-full max-w-[28px] bg-slate-100 dark:bg-slate-700 rounded-t h-full flex items-end">
                                        <div
                                            style={{ height: `${height}%` }}
                                            className="w-full rounded-t bg-gradient-to-t from-blue-600 to-indigo-400 transition-all"
                                        />
                                    </div>
                                    <span className="mt-1 text-[10px] text-slate-400 truncate max-w-[32px]">
                                        {d.date.slice(5)}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>

            {/* Conversion Funnel */}
            <div className="mb-8 p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <h2 className="text-lg font-bold text-slate-900 dark:text-white mb-2">
                    কনভার্শন ফানেল (Conversion Funnel)
                </h2>
                <p className="text-xs text-slate-500 mb-6">ব্রাউজিং থেকে শুরু করে চেকআউট ও লেসন সম্পন্ন হওয়ার স্তরভিত্তিক ড্রপ-অফ</p>

                <div className="space-y-4">
                    {funnel.map((step, idx) => {
                        const widthPercent = Math.max(12, Math.min(100, Math.round((step.count / funnelBaseCount) * 100)));
                        const prevCount = idx > 0 ? funnel[idx - 1].count : step.count;
                        const dropRate = prevCount > 0 ? round1((step.count / prevCount) * 100) : 100;

                        return (
                            <div key={idx} className="space-y-1">
                                <div className="flex justify-between text-xs font-semibold">
                                    <span className="text-slate-800 dark:text-slate-200">{step.stage}</span>
                                    <div className="space-x-2">
                                        <span className="text-slate-900 dark:text-white font-bold">{step.count.toLocaleString()}</span>
                                        {idx > 0 && (
                                            <span className="text-[11px] text-slate-400 font-normal">
                                                (পূর্ববর্তী ধাপের {dropRate}%)
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div className="w-full h-7 bg-slate-100 dark:bg-slate-700/60 rounded-xl overflow-hidden p-0.5">
                                    <div
                                        style={{ width: `${widthPercent}%`, backgroundColor: step.color }}
                                        className="h-full rounded-lg transition-all duration-700 flex items-center justify-end px-3 text-white text-[11px] font-bold shadow-sm"
                                    >
                                        {widthPercent}%
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Retention Cohort Matrix */}
            <div className="mb-8 p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div className="flex items-center justify-between mb-4">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900 dark:text-white">৪ সপ্তাহের রিটেনশন কোহর্ট ম্যাট্রিক্স</h2>
                        <p className="text-xs text-slate-500">নিবন্ধনকারী ইউজাররা পরবর্তী সপ্তাহগুলোতে কতটা সক্রিয় থাকেন</p>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 font-semibold">
                            <tr>
                                <th className="py-3 px-4 rounded-l-lg">কোহর্ট সপ্তাহ</th>
                                <th className="py-3 px-4 text-center">নিবন্ধন সংখ্যা</th>
                                <th className="py-3 px-4 text-center">Week 0</th>
                                <th className="py-3 px-4 text-center">Week 1</th>
                                <th className="py-3 px-4 text-center">Week 2</th>
                                <th className="py-3 px-4 text-center">Week 3</th>
                                <th className="py-3 px-4 text-center rounded-r-lg">Week 4</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                            {cohort_matrix.map((c, idx) => (
                                <tr key={idx} className="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td className="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                        {c.cohort}
                                    </td>
                                    <td className="py-3 px-4 text-center font-bold text-slate-600 dark:text-slate-300">
                                        {c.size}
                                    </td>
                                    {[0, 1, 2, 3, 4].map((w) => {
                                        const val = c.weeks[w];
                                        if (val === undefined) {
                                            return <td key={w} className="py-3 px-4 text-center text-slate-300 dark:text-slate-600">-</td>;
                                        }

                                        let bgColor = 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300';
                                        if (val < 25) bgColor = 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300';
                                        else if (val < 50) bgColor = 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300';
                                        else if (val < 75) bgColor = 'bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300';

                                        return (
                                            <td key={w} className="py-3 px-4 text-center">
                                                <span className={`inline-block w-14 py-1 rounded-md text-xs font-bold ${bgColor}`}>
                                                    {val}%
                                                </span>
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Top Courses and Scholars Grid */}
            <div className="grid gap-6 lg:grid-cols-2">
                {/* Top Courses */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h3 className="font-bold text-slate-900 dark:text-white mb-4">শীর্ষ কোর্সসমূহ (এনরোলমেন্ট ও রেভিনিউ)</h3>
                    <div className="space-y-3">
                        {top_courses.map((course, i) => (
                            <div key={course.id} className="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <span className="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-600 text-xs font-bold flex items-center justify-center text-slate-700 dark:text-slate-200">
                                        {i + 1}
                                    </span>
                                    <div>
                                        <h4 className="text-sm font-semibold text-slate-900 dark:text-white">{course.title}</h4>
                                        <p className="text-xs text-slate-500">{course.enrollments} জন শিক্ষার্থী</p>
                                    </div>
                                </div>
                                <span className="font-bold text-emerald-600 text-sm">৳{course.revenue.toLocaleString()}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Top Scholars */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h3 className="font-bold text-slate-900 dark:text-white mb-4">শীর্ষ শিক্ষক ও স্কলারবৃন্দ</h3>
                    <div className="space-y-3">
                        {top_scholars.map((scholar, i) => (
                            <div key={scholar.id} className="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <span className="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 text-xs font-bold flex items-center justify-center">
                                        {i + 1}
                                    </span>
                                    <div>
                                        <h4 className="text-sm font-semibold text-slate-900 dark:text-white">{scholar.name}</h4>
                                        <p className="text-xs text-slate-500">{scholar.designation || 'ইসলামিক স্কলার'}</p>
                                    </div>
                                </div>
                                <span className="text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700">
                                    {scholar.courses_count}টি কোর্স
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}

function round1(val) {
    return Math.round(val * 10) / 10;
}
