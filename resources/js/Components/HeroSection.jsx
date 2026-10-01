import React from 'react';
import { Link } from '@inertiajs/react';

export default function HeroSection({ stats = [] }) {
    return (
        <section className="relative overflow-hidden bg-[#102526] text-white">
            {/* Ambient Background Glow and Islamic Decorative Radial Gradients */}
            <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:24px_24px] opacity-5"></div>
            <div className="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-[#1A2E2F] blur-3xl opacity-60"></div>
            <div className="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-emerald-950 blur-3xl opacity-60"></div>

            <div className="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
                <div className="mx-auto max-w-4xl text-center">
                    {/* Arabic Quranic Ayah Calligraphy Banner */}
                    <div className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/20 bg-white/5 px-4 py-1.5 backdrop-blur-md">
                        <span className="text-sm font-arabic tracking-wide text-[#FFF99A]">
                            ﷽ • وَقُل رَّبِّ زِدْنِي عِلْمًا
                        </span>
                        <span className="hidden sm:inline text-xs text-white/60">
                            (সূরা ত্ব-হা: ১১৪)
                        </span>
                    </div>

                    {/* Main Headline */}
                    <h1 className="mt-8 text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl font-bangla leading-tight sm:leading-tight">
                        শুদ্ধ ইসলামী শিক্ষার এক <br className="hidden sm:block" />
                        <span className="bg-gradient-to-r from-[#FFF99A] via-amber-200 to-emerald-300 bg-clip-text text-transparent">
                            আধুনিক ডিজিটাল একাডেমি
                        </span>
                    </h1>

                    {/* Subtitle */}
                    <p className="mt-6 text-lg sm:text-xl text-slate-300 leading-relaxed font-bangla max-w-3xl mx-auto">
                        বিজ্ঞ উলামায়ে কেরামের প্রত্যক্ষ দিকনির্দেশনায় সুসংগঠিত সিলেবাসে কুরআন, হাদীস, ফিকহ ও আরবী ভাষার উচ্চতর জ্ঞান অর্জন করুন ঘরে বসেই।
                    </p>

                    {/* Action CTAs */}
                    <div className="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <Link
                            href="/courses"
                            className="inline-flex items-center gap-2 rounded-xl bg-[#FFF99A] px-7 py-3.5 text-base font-bold text-[#102526] shadow-lg transition-all duration-300 hover:bg-[#fff780] hover:scale-105 hover:shadow-xl"
                        >
                            <span>কোর্সসমূহ অন্বেষণ করুন</span>
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>

                        <Link
                            href="/about/teachers"
                            className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-7 py-3.5 text-base font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/10 hover:border-[#FFF99A]/40 hover:text-[#FFF99A]"
                        >
                            <span>শিক্ষকমণ্ডলী জানুন</span>
                        </Link>

                        <Link
                            href="/fatawa/ask"
                            className="inline-flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-950/40 px-6 py-3.5 text-base font-semibold text-emerald-300 transition-all duration-300 hover:bg-emerald-900/60"
                        >
                            <span>প্রশ্ন করুন (ফাতাওয়া)</span>
                        </Link>
                    </div>

                    {/* Feature Highlights Pills */}
                    <div className="mt-12 flex flex-wrap items-center justify-center gap-4 text-xs font-medium text-slate-300">
                        <div className="flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-1.5 border border-white/10">
                            <span className="text-[#FFF99A]">✓</span> বিশুদ্ধ তাহকীকভিত্তিক পাঠ
                        </div>
                        <div className="flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-1.5 border border-white/10">
                            <span className="text-[#FFF99A]">✓</span> স্বীকৃত সনদপত্র (Certificate)
                        </div>
                        <div className="flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-1.5 border border-white/10">
                            <span className="text-[#FFF99A]">✓</span> যেকোনো ডিভাইসে আজীবন অ্যাক্সেস
                        </div>
                    </div>
                </div>

                {/* Key Statistics Strip */}
                {stats.length > 0 && (
                    <div className="mt-16 rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-md sm:p-8">
                        <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5 text-center">
                            {stats.map((s, index) => (
                                <div key={index} className="flex flex-col items-center justify-center">
                                    <span className="text-2xl sm:text-3xl font-extrabold text-[#FFF99A] tracking-tight">
                                        {s.value}
                                    </span>
                                    <span className="mt-1 text-xs sm:text-sm text-slate-300 font-medium">
                                        {s.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Bottom Curve Divider */}
            <div className="h-6 w-full bg-[#F8FAF8] rounded-t-[2.5rem] mt-2"></div>
        </section>
    );
}
