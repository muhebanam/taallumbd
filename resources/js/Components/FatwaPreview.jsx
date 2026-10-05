import React from 'react';
import { Link } from '@inertiajs/react';

export default function FatwaPreview({ fatawa = [] }) {
    const fatwaList = Array.isArray(fatawa) ? fatawa : (fatawa && typeof fatawa === 'object' ? Object.values(fatawa) : []);
    return (
        <section className="py-16 sm:py-24 bg-white border-y border-slate-200/60">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <span className="inline-block rounded-full bg-emerald-100 px-4 py-1 text-xs font-bold text-emerald-800 uppercase tracking-wider">
                            শরয়ী সমাধান
                        </span>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                            সাম্প্রতিক ফাতাওয়া ও বিশেষজ্ঞ মতামত
                        </h2>
                        <p className="mt-2 text-base text-slate-600 font-bangla">
                            দৈনন্দিন জীবনের বিভিন্ন জটিল মাসআলায় বিশ্বস্ত উলামায়ে কেরামের শরয়ী দিকনির্দেশনা।
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/fatawa/ask"
                            className="inline-flex items-center gap-1.5 rounded-xl bg-[#1A2E2F] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#102526]"
                        >
                            <span>প্রশ্ন জমা দিন</span>
                            <span>✍️</span>
                        </Link>
                        <Link
                            href="/fatawa"
                            className="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-[#1A2E2F] transition py-2"
                        >
                            <span>আর্কাইভ দেখুন</span>
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                {/* Fatawa Cards Grid */}
                <div className="mt-12 grid grid-cols-1 gap-6 md:grid-cols-2">
                    {fatwaList.length > 0 ? (
                        fatwaList.map((f) => (
                            <div
                                key={f.id}
                                className="group flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-[#F8FAF8] p-6 transition-all duration-300 hover:border-[#1A2E2F]/40 hover:bg-white hover:shadow-lg"
                            >
                                <div>
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="rounded-lg bg-emerald-50 border border-emerald-200/60 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">
                                            {f.category?.name || 'সাধারণ ফাতাওয়া'}
                                        </span>
                                        {f.published_at && (
                                            <span className="text-[11px] text-slate-500 font-mono">
                                                {new Date(f.published_at).toLocaleDateString('bn-BD')}
                                            </span>
                                        )}
                                    </div>

                                    <h3 className="mt-4 text-base font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors font-bangla line-clamp-2">
                                        <Link href={`/fatawa/${f.id}`}>
                                            {f.question_title}
                                        </Link>
                                    </h3>

                                    <p className="mt-2 text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                        {f.answer_body ? f.answer_body.replace(/<[^>]*>?/gm, '') : f.question_body}
                                    </p>
                                </div>

                                <div className="mt-6 flex items-center justify-between border-t border-slate-200/60 pt-4">
                                    <div className="flex items-center gap-2">
                                        <div className="h-7 w-7 rounded-full bg-[#1A2E2F] text-[10px] text-white font-bold flex items-center justify-center">
                                            {f.teacher?.name ? f.teacher.name.charAt(0) : 'মু'}
                                        </div>
                                        <div className="text-left">
                                            <span className="block text-xs font-bold text-slate-800 line-clamp-1">
                                                {f.teacher?.name || 'আত-তাআল্লুম দারুল ইফতা'}
                                            </span>
                                            <span className="block text-[10px] text-slate-500">অনুমোদিত উত্তরদাতা</span>
                                        </div>
                                    </div>

                                    <Link
                                        href={`/fatawa/${f.id}`}
                                        className="text-xs font-bold text-emerald-700 hover:text-emerald-900 transition flex items-center gap-1"
                                    >
                                        <span>উত্তর পড়ুন</span>
                                        <span>→</span>
                                    </Link>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-2 rounded-2xl border border-dashed border-slate-300 p-8 text-center bg-slate-50">
                            <span className="text-3xl">⚖️</span>
                            <h4 className="mt-2 text-sm font-bold text-slate-800">কোনো ফাতাওয়া এখনও তালিকাভুক্ত হয়নি</h4>
                            <p className="text-xs text-slate-500 mt-1">আপনার যেকোনো দ্বীনি প্রশ্ন পাঠাতে পারেন আমাদের স্কলার প্যানেলে।</p>
                            <Link href="/fatawa/ask" className="btn-primary mt-4 text-xs !py-2">প্রশ্ন জমা দিন</Link>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
