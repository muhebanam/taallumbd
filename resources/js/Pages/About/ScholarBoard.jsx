import React from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import SeoHead from '../../Components/SeoHead';

export default function ScholarBoard({ boardMembers = [] }) {
    const list = Array.isArray(boardMembers) ? boardMembers : Object.values(boardMembers || {});

    const jsonLd = {
        '@context': 'https://schema.org',
        '@type': 'ItemList',
        itemListElement: list.map((m, idx) => ({
            '@type': 'ListItem',
            position: idx + 1,
            item: {
                '@type': 'Person',
                name: m.name,
                jobTitle: m.designation,
                url: `https://taallum.org/scholars/${m.slug}`,
            },
        })),
    };

    return (
        <MainLayout>
            <SeoHead
                title="বিজ্ঞ উলামা ও ফাতাওয়া বোর্ড"
                description="দেশের প্রখ্যাত ইসলামী চিন্তাবিদ ও গবেষকদের সমন্বয়ে গঠিত আত-তাআল্লুম একাডেমিক ও ফাতাওয়া বোর্ড।"
                canonical="https://taallum.org/scholars/board"
                jsonLd={jsonLd}
            />

            {/* Banner */}
            <section className="relative overflow-hidden bg-[#102526] py-16 sm:py-24 text-white">
                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:24px_24px] opacity-5"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <div className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/30 bg-white/5 px-4 py-1.5 backdrop-blur-md">
                        <span className="text-xs font-bold text-[#FFF99A] tracking-wider uppercase">
                            একাডেমিক গভর্ন্যান্স ও ট্রাস্ট
                        </span>
                    </div>

                    <h1 className="mt-6 text-3xl sm:text-5xl font-extrabold font-bangla text-white">
                        বিজ্ঞ উলামা পরিষদ ও শরীয়াহ বোর্ড
                    </h1>
                    <p className="mt-4 max-w-2xl mx-auto text-sm sm:text-base text-slate-300 font-bangla leading-relaxed">
                        আত-তাআল্লুমের প্রতিটি কোর্স, পাঠ্যক্রম এবং শরয়ী সিদ্ধান্ত আহলুস সুন্নাহ ওয়াল জামা‘আতের বিশুদ্ধ মানহাজ অনুযায়ী এই বিজ্ঞ স্কলার প্যানেলের প্রত্যক্ষ নিরীক্ষা ও দিকনির্দেশনায় পরিচালিত হয়।
                    </p>
                </div>
            </section>

            {/* Board Members Grid */}
            <section className="py-16 sm:py-24 bg-[#F8FAF8]">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {list.map((scholar) => (
                            <div
                                key={scholar.id}
                                className="group relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-brand/30"
                            >
                                <div>
                                    {/* Avatar & Badges */}
                                    <div className="flex items-start gap-4">
                                        <div className="relative shrink-0">
                                            <img
                                                src={scholar.avatar ? (scholar.avatar.startsWith('http') ? scholar.avatar : `/storage/${scholar.avatar}`) : '/images/teachers/avatar-default.jpg'}
                                                alt={scholar.name}
                                                className="h-20 w-20 rounded-2xl object-cover border-2 border-brand/10 shadow-sm"
                                                loading="lazy"
                                                width="80"
                                                height="80"
                                            />
                                            {scholar.is_verified && (
                                                <span
                                                    className="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md"
                                                    title="যাচাইকৃত স্কলার"
                                                >
                                                    <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            )}
                                        </div>

                                        <div>
                                            <span className="inline-block rounded-lg bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200/60 mb-1">
                                                বোর্ড সদস্য
                                            </span>
                                            <h3 className="text-lg font-bold text-[#142425] group-hover:text-brand transition-colors font-bangla">
                                                <Link href={`/scholars/${scholar.slug}`}>
                                                    {scholar.name}
                                                </Link>
                                            </h3>
                                            <p className="text-xs text-slate-500 font-medium line-clamp-1">
                                                {scholar.designation}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Short Bio */}
                                    <p className="mt-4 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                        {scholar.short_bio || scholar.headline || 'আত-তাআল্লুম একাডেমিক ও ফাতাওয়া বোর্ডের সম্মানিত সিনিয়র সদস্য।'}
                                    </p>

                                    {/* Stats Strip */}
                                    <div className="mt-6 grid grid-cols-3 gap-2 rounded-2xl bg-slate-50 border border-slate-100 p-3 text-center">
                                        <div>
                                            <span className="block text-base font-extrabold text-[#1A2E2F]">
                                                {scholar.courses_count || 0}
                                            </span>
                                            <span className="text-[10px] text-slate-500 font-medium">কোর্স</span>
                                        </div>
                                        <div className="border-x border-slate-200">
                                            <span className="block text-base font-extrabold text-[#1A2E2F]">
                                                {scholar.fatawa_count || 0}
                                            </span>
                                            <span className="text-[10px] text-slate-500 font-medium">ফাতাওয়া</span>
                                        </div>
                                        <div>
                                            <span className="block text-base font-extrabold text-[#1A2E2F]">
                                                {scholar.articles_count || 0}
                                            </span>
                                            <span className="text-[10px] text-slate-500 font-medium">প্রবন্ধ</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Footer CTA */}
                                <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <Link
                                        href={`/scholars/${scholar.slug}`}
                                        className="inline-flex items-center gap-1.5 text-xs font-bold text-brand hover:text-emerald-700 transition"
                                    >
                                        <span>পূর্ণ প্রোফাইল দেখুন</span>
                                        <span>→</span>
                                    </Link>

                                    <Link
                                        href="/fatawa/ask"
                                        className="rounded-xl bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-[#1A2E2F] hover:text-white transition"
                                    >
                                        প্রশ্ন পাঠান ✍️
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
