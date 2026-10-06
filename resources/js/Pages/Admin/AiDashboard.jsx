import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function AiDashboard({
    aiEnabled,
    globalStats,
    totalInteractions,
    totalTokens,
    totalCost,
    flaggedCount,
    featureStats,
    recentInteractions,
}) {
    const handleToggle = () => {
        if (confirm(`আপনি কি নিশ্চিতভাবে AI ফিচার ${aiEnabled ? 'নিষ্ক্রিয়' : 'সক্রিয়'} করতে চান?`)) {
            router.post(route('admin.ai.toggle'));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="AI গভর্ন্যান্স ও খরচ ড্যাশবোর্ড" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    {/* Header with Master Toggle */}
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 font-bold text-lg">
                                    🤖
                                </span>
                                <h1 className="text-xl font-bold text-slate-900 font-bangla">
                                    AI গভর্ন্যান্স ও খরচ ড্যাশবোর্ড
                                </h1>
                                <span className={`text-xs px-2.5 py-1 rounded-full font-bold ${
                                    aiEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                                }`}>
                                    {aiEnabled ? '● সক্রিয় (Active)' : '○ নিষ্ক্রিয় (Disabled)'}
                                </span>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">
                                মূলনীতি: "AI assists; scholars remain final authority." (Master Plan Part 21, 25)
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Link
                                href={route('admin.ai.reviews')}
                                className="relative inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100 transition"
                            >
                                <span>⚖️ স্কলার রিভিউ কিউ</span>
                                {flaggedCount > 0 && (
                                    <span className="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-[10px] font-bold">
                                        {flaggedCount}
                                    </span>
                                )}
                            </Link>

                            <button
                                type="button"
                                onClick={handleToggle}
                                className={`px-4 py-2 text-xs font-bold rounded-xl text-white transition shadow-sm ${
                                    aiEnabled
                                        ? 'bg-rose-600 hover:bg-rose-700'
                                        : 'bg-emerald-600 hover:bg-emerald-700'
                                }`}
                            >
                                {aiEnabled ? 'AI ফিচার বন্ধ করুন' : 'AI ফিচার চালু করুন'}
                            </button>
                        </div>
                    </div>

                    {/* Stat Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span className="text-xs font-semibold text-slate-500">আজকের রিকোয়েস্ট (বাজেট গার্ড)</span>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-2xl font-black text-slate-900">
                                    {globalStats.requests_today} / {globalStats.daily_limit}
                                </span>
                                <span className="text-xs font-bold text-emerald-600">
                                    {globalStats.percent_used}% ব্যবহৃত
                                </span>
                            </div>
                            <div className="mt-3 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div
                                    className="bg-emerald-500 h-1.5 rounded-full"
                                    style={{ width: `${Math.min(100, globalStats.percent_used)}%` }}
                                />
                            </div>
                        </div>

                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span className="text-xs font-semibold text-slate-500">মোট ব্যবহৃত টোকেন</span>
                            <div className="mt-2 text-2xl font-black text-slate-900">
                                {Number(totalTokens).toLocaleString()}
                            </div>
                            <span className="text-xs text-slate-400 mt-1 block">
                                আজ: {Number(globalStats.tokens_today).toLocaleString()} টোকেন
                            </span>
                        </div>

                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span className="text-xs font-semibold text-slate-500">আনুমানিক খরচ (USD)</span>
                            <div className="mt-2 text-2xl font-black text-slate-900">
                                ${Number(totalCost).toFixed(4)}
                            </div>
                            <span className="text-xs text-emerald-600 mt-1 block">
                                ফ্রি টিয়ার সুরক্ষা সক্রিয়
                            </span>
                        </div>

                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span className="text-xs font-semibold text-slate-500">মোট এআই ইন্টারেকশন</span>
                            <div className="mt-2 text-2xl font-black text-slate-900">
                                {totalInteractions}
                            </div>
                            <span className="text-xs text-amber-600 mt-1 block">
                                {flaggedCount} টি পর্যালোচনার অপেক্ষায়
                            </span>
                        </div>
                    </div>

                    {/* Feature Breakdown Table */}
                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div className="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 className="font-bold text-sm text-slate-800 font-bangla">ফিচারভিত্তিক ব্যবহার বিশ্লেষণ</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-xs text-left">
                                <thead className="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                    <tr>
                                        <th className="px-6 py-3">ফিচার</th>
                                        <th className="px-6 py-3">রিকোয়েস্ট সংখ্যা</th>
                                        <th className="px-6 py-3">মোট টোকেন</th>
                                        <th className="px-6 py-3">আনুমানিক খরচ</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {featureStats.map((stat, i) => (
                                        <tr key={i} className="hover:bg-slate-50/80">
                                            <td className="px-6 py-3.5 font-bold text-slate-800">
                                                {stat.feature === 'course_tutor' && '🎓 কোর্স টিউটর (Assistant)'}
                                                {stat.feature === 'search_summary' && '🔍 এআই সার্চ সারসংক্ষেপ'}
                                                {stat.feature === 'quiz_generator' && '📝 কুইজ জেনারেটর'}
                                                {stat.feature === 'content_assist' && '✍️ কনটেন্ট ভাষা সম্পাদনা'}
                                                {!['course_tutor', 'search_summary', 'quiz_generator', 'content_assist'].includes(stat.feature) && stat.feature}
                                            </td>
                                            <td className="px-6 py-3.5 text-slate-600">{stat.count}</td>
                                            <td className="px-6 py-3.5 text-slate-600">{Number(stat.total_tokens).toLocaleString()}</td>
                                            <td className="px-6 py-3.5 text-slate-600">${Number(stat.total_cost).toFixed(6)}</td>
                                        </tr>
                                    ))}
                                    {featureStats.length === 0 && (
                                        <tr>
                                            <td colSpan="4" className="px-6 py-6 text-center text-slate-400">
                                                এখনো কোনো এআই রিকোয়েস্ট রেকর্ড করা হয়নি।
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Recent Interactions Audit Trail */}
                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div className="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 className="font-bold text-sm text-slate-800 font-bangla">সাম্প্রতিক এআই অডিট লগ (PII মুক্ত)</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-xs text-left">
                                <thead className="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                    <tr>
                                        <th className="px-6 py-3">আইডি / সময়</th>
                                        <th className="px-6 py-3">ইউজার</th>
                                        <th className="px-6 py-3">ফিচার</th>
                                        <th className="px-6 py-3">প্রম্পট (ফিল্টার্ড)</th>
                                        <th className="px-6 py-3">ল্যাটেন্সি</th>
                                        <th className="px-6 py-3">স্ট্যাটাস</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {recentInteractions.map((item) => (
                                        <tr key={item.id} className="hover:bg-slate-50/80">
                                            <td className="px-6 py-3 font-mono text-slate-500">
                                                #{item.id}<br />
                                                <span className="text-[10px] text-slate-400">
                                                    {new Date(item.created_at).toLocaleTimeString('bn-BD')}
                                                </span>
                                            </td>
                                            <td className="px-6 py-3 font-medium text-slate-700">
                                                {item.user ? item.user.name : 'গেস্ট / অনামী'}
                                            </td>
                                            <td className="px-6 py-3 text-slate-600">
                                                <span className="px-2 py-0.5 rounded-full bg-slate-100 text-[10px] font-semibold text-slate-700">
                                                    {item.feature}
                                                </span>
                                            </td>
                                            <td className="px-6 py-3 text-slate-600 max-w-xs truncate" title={item.prompt_redacted}>
                                                {item.prompt_redacted}
                                            </td>
                                            <td className="px-6 py-3 text-slate-500">
                                                {item.latency_ms} ms
                                            </td>
                                            <td className="px-6 py-3">
                                                {item.flagged ? (
                                                    <span className="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">
                                                        ⚠️ ফ্ল্যাগড
                                                    </span>
                                                ) : (
                                                    <span className="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">
                                                        স্বাভাবিক
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                    {recentInteractions.length === 0 && (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-6 text-center text-slate-400">
                                                কোনো সাম্প্রতিক রেকর্ড নেই।
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
