import { useState } from 'react';
import { Head } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import StatCard from '../../Components/StatCard';
import axios from 'axios';

export default function StudentAnalytics({ metrics, weekly_time = [], strengths = [], weaknesses = [], recent_events = [] }) {
    const [deleting, setDeleting] = useState(false);
    const [deleteSuccess, setDeleteSuccess] = useState(null);

    const maxWeeklyMinutes = Math.max(...weekly_time.map(w => w.minutes), 60);

    const handleDeleteHistory = async () => {
        if (!confirm('আপনি কি নিশ্চিত যে আপনার অতীতের লার্নিং ইভেন্ট ও ট্র্যাক ডেটা মুছে/অ্যানোনিমাইজ করতে চান? এটি আর ফিরিয়ে আনা যাবে না।')) {
            return;
        }

        setDeleting(true);
        try {
            const res = await axios.delete('/profile/privacy/delete-data');
            setDeleteSuccess(res.data.message || 'লার্নিং ডেটা সফলভাবে মোছা হয়েছে।');
        } catch (e) {
            alert('ডেটা মুছতে সমস্যা হয়েছে: ' + (e.response?.data?.message || e.message));
        } finally {
            setDeleting(false);
        }
    };

    return (
        <DashboardLayout title="লার্নিং অ্যানালিটিক্স">
            <Head title="লার্নিং অ্যানালিটিক্স ও অগ্রগতি - Taallum BD" />

            {/* Header / Intro */}
            <div className="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                        আপনার ব্যক্তিগত লার্নিং অ্যানালিটিক্স
                    </h1>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        আপনার দৈনিক পড়াশোনার স্ট্রিক, সময় এবং কুইজের শক্তি ও উন্নতির জায়গা পর্যবেক্ষণ করুন।
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <a
                        href="/profile/privacy/export-data"
                        className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-xl transition-all shadow-sm"
                        title="আপনার সমস্ত লার্নিং ডেটা JSON ফরম্যাটে ডাউনলোড করুন"
                    >
                        <svg className="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        ডেটা এক্সপোর্ট (JSON)
                    </a>
                </div>
            </div>

            {deleteSuccess && (
                <div className="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                    <span>{deleteSuccess}</span>
                    <button onClick={() => setDeleteSuccess(null)} className="text-emerald-600 hover:underline text-xs">বন্ধ করুন</button>
                </div>
            )}

            {/* Top Stat Cards */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div className="p-6 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-slate-800 dark:to-orange-950/30 border border-orange-200/60 dark:border-orange-900/40 shadow-sm relative overflow-hidden">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wider text-orange-600 dark:text-orange-400">বর্তমান স্ট্রিক</p>
                            <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                                {metrics.current_streak} <span className="text-lg font-normal text-slate-500">দিন</span>
                            </h3>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                সর্বোচ্চ রেকর্ড: <strong className="text-orange-600">{metrics.longest_streak} দিন</strong>
                            </p>
                        </div>
                        <div className="h-14 w-14 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 flex items-center justify-center text-3xl animate-bounce">
                            🔥
                        </div>
                    </div>
                </div>

                <div className="p-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">সাপ্তাহিক পড়ার সময়</p>
                            <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                                {Math.floor(metrics.total_study_minutes / 60)} <span className="text-lg font-normal text-slate-500">ঘণ্টা</span> {metrics.total_study_minutes % 60} <span className="text-sm font-normal text-slate-500">মি.</span>
                            </h3>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-2">গত ৭ দিনের সক্রিয় সময়</p>
                        </div>
                        <div className="h-14 w-14 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600">
                            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-indigo-950/30 border border-blue-200/60 dark:border-blue-900/40 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">গড় কোর্স অগ্রগতি</p>
                            <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                                {metrics.avg_progress}%
                            </h3>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-2">{metrics.completed_courses}/{metrics.total_courses}টি কোর্স সম্পন্ন</p>
                        </div>
                        <div className="h-14 w-14 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-600">
                            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="p-6 rounded-2xl bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-800 dark:to-purple-950/30 border border-purple-200/60 dark:border-purple-900/40 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">কুইজ পারফরম্যান্স</p>
                            <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                                {strengths.length} <span className="text-lg font-normal text-slate-500">দক্ষতা</span>
                            </h3>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-2">{weaknesses.length}টি ক্ষেত্রে উন্নতির সুযোগ</p>
                        </div>
                        <div className="h-14 w-14 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-600">
                            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {/* Weekly Study Time Chart */}
            <div className="mb-8 p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900 dark:text-white">সাপ্তাহিক পড়াশোনার রুটিন</h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400">গত ৭ দিনের দৈনিক ব্যয়িত সময় (মিনিটে)</p>
                    </div>
                    <span className="text-xs font-medium px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full">
                        রিয়েল-টাইম ট্র্যাকিং
                    </span>
                </div>

                <div className="h-48 flex items-end justify-between gap-2 sm:gap-6 pt-6 px-2 border-b border-slate-100 dark:border-slate-700/60">
                    {weekly_time.map((day, idx) => {
                        const heightPercent = Math.max(8, Math.min(100, Math.round((day.minutes / maxWeeklyMinutes) * 100)));
                        const isToday = idx === weekly_time.length - 1;

                        return (
                            <div key={day.date} className="flex-1 flex flex-col items-center h-full justify-end group">
                                <div className="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {day.minutes}মি.
                                </div>
                                <div className="w-full max-w-[42px] bg-slate-100 dark:bg-slate-700/60 rounded-t-xl overflow-hidden h-full flex items-end">
                                    <div
                                        style={{ height: `${heightPercent}%` }}
                                        className={`w-full rounded-t-xl transition-all duration-500 ${
                                            isToday
                                                ? 'bg-gradient-to-t from-emerald-600 to-teal-400 shadow-md shadow-emerald-500/20'
                                                : day.minutes > 0
                                                ? 'bg-gradient-to-t from-blue-600 to-indigo-400'
                                                : 'bg-slate-200 dark:bg-slate-700'
                                        }`}
                                    />
                                </div>
                                <span className={`mt-2 text-xs font-medium ${isToday ? 'text-emerald-600 font-bold' : 'text-slate-500'}`}>
                                    {day.day}
                                </span>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Strengths & Weaknesses Grid */}
            <div className="grid gap-6 lg:grid-cols-2 mb-8">
                {/* Strengths */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div className="flex items-center gap-3 mb-4">
                        <div className="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600">
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 className="font-bold text-slate-900 dark:text-white">আপনার প্রধান শক্তিমত্তা (Strengths)</h3>
                            <p className="text-xs text-slate-500">যেসব বিষয়ে কুইজের গড় নম্বর ৭৫% বা তার বেশি</p>
                        </div>
                    </div>

                    {strengths.length === 0 ? (
                        <p className="text-sm text-slate-400 italic py-4">এখনও পর্যন্ত পর্যাপ্ত কুইজের তথ্য নেই। নিয়মিত কুইজে অংশ নিন!</p>
                    ) : (
                        <div className="space-y-4">
                            {strengths.slice(0, 5).map((item, i) => (
                                <div key={i} className="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40">
                                    <div className="flex justify-between items-center mb-1 text-sm font-medium">
                                        <span className="text-slate-800 dark:text-slate-200 truncate">{item.topic}</span>
                                        <span className="text-emerald-600 font-bold ml-2">{item.avg_score}%</span>
                                    </div>
                                    <div className="w-full h-2 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                                        <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${item.avg_score}%` }} />
                                    </div>
                                    <div className="flex justify-between text-[11px] text-slate-500 mt-1">
                                        <span>অংশগ্রহণ: {item.attempts} বার</span>
                                        <span>পাস রেট: {item.pass_rate}%</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Weaknesses / Improvement Areas */}
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div className="flex items-center gap-3 mb-4">
                        <div className="p-2 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600">
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 className="font-bold text-slate-900 dark:text-white">উন্নতির সুযোগ (Areas for Improvement)</h3>
                            <p className="text-xs text-slate-500">যেসব বিষয়ে আরও পুনরাবৃত্তি ও মনোযোগ প্রয়োজন</p>
                        </div>
                    </div>

                    {weaknesses.length === 0 ? (
                        <div className="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 text-sm">
                            মাশাআল্লাহ! আপনার কোনো দুর্বল বিষয় চিহ্নিত হয়নি। সব কুইজেই চমৎকার ফল ধরে রেখেছেন।
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {weaknesses.slice(0, 5).map((item, i) => (
                                <div key={i} className="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40">
                                    <div className="flex justify-between items-center mb-1 text-sm font-medium">
                                        <span className="text-slate-800 dark:text-slate-200 truncate">{item.topic}</span>
                                        <span className="text-amber-600 font-bold ml-2">{item.avg_score}%</span>
                                    </div>
                                    <div className="w-full h-2 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                                        <div className="h-full bg-amber-500 rounded-full" style={{ width: `${item.avg_score}%` }} />
                                    </div>
                                    <div className="flex justify-between text-[11px] text-slate-500 mt-1">
                                        <span>অংশগ্রহণ: {item.attempts} বার</span>
                                        <span className="text-amber-600 font-medium">রিভিউ প্রয়োজন</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Privacy and Control Footer */}
            <div className="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h4 className="font-bold text-slate-900 dark:text-white">আপনার ডেটা ও গোপনীয়তার অধিকার</h4>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Taallum BD-তে আপনার সমস্ত লার্নিং মেট্রিক্স ও ডেটা সুরক্ষিত এবং PII এনক্রিপ্টেড। আপনি যেকোনো সময় সম্পূর্ণ ডেটা ডাউনলোড বা মুছে ফেলতে পারেন।
                    </p>
                </div>
                <button
                    onClick={handleDeleteHistory}
                    disabled={deleting}
                    className="px-3 py-1.5 text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors border border-rose-200 dark:border-rose-900/60 shrink-0"
                >
                    {deleting ? 'প্রক্রিয়াধীন...' : 'লার্নিং হিস্টোরি মুছুন'}
                </button>
            </div>
        </DashboardLayout>
    );
}
