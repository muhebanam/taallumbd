import React from 'react';
import { Link } from '@inertiajs/react';

const DEFAULT_CATEGORIES = [
    {
        name: 'আল-কুরআন ও তাজবীদ',
        slug: 'quran',
        desc: 'বিশুদ্ধ তিলাওয়াত, মাখরাজ, তাজবীদের নিয়ম এবং নির্বাচিত সূরাসমূহের মর্মবাণী।',
        icon: '📖',
        color: 'from-emerald-500/10 to-emerald-500/5 text-emerald-800 border-emerald-200/60',
    },
    {
        name: 'হাদীস ও সুন্নাহ',
        slug: 'hadith',
        desc: 'রাসূলুল্লাহ ﷺ-এর পবিত্র বাণী, সহীহ হাদীসের ব্যাখ্যা এবং দৈনন্দিন জীবনে সুন্নাহর প্রয়োগ।',
        icon: '📜',
        color: 'from-amber-500/10 to-amber-500/5 text-amber-800 border-amber-200/60',
    },
    {
        name: 'ফিকহ ও মাসায়েল',
        slug: 'fiqh',
        desc: 'ইবাদত, মুয়ামালাত, পারিবারিক ও সমসাময়িক আধুনিক সমস্যার শরয়ী সমাধান।',
        icon: '⚖️',
        color: 'from-blue-500/10 to-blue-500/5 text-blue-800 border-blue-200/60',
    },
    {
        name: 'আকীদাহ ও বিশ্বাস',
        slug: 'aqeedah',
        desc: 'আহলুস সুন্নাহ ওয়াল জামা‘আতের বিশুদ্ধ বিশ্বাস, তাওহীদ এবং সংশয় নিরসন।',
        icon: '🛡️',
        color: 'from-purple-500/10 to-purple-500/5 text-purple-800 border-purple-200/60',
    },
    {
        name: 'আরবি ভাষা ও ব্যাকরণ',
        slug: 'arabic',
        desc: 'কুরআন ও হাদীসের ভাষা সহজে বোঝার জন্য ব্যাকরণ (নাহু, সরফ) ও কথ্য আরবি।',
        icon: '✍️',
        color: 'from-teal-500/10 to-teal-500/5 text-teal-800 border-teal-200/60',
    },
    {
        name: 'সীরাত ও ইসলামী ইতিহাস',
        slug: 'seerah',
        desc: 'নবীজি ﷺ-এর পবিত্র জীবনচরিত, সাহাবায়ে কেরাম ও সোনালী যুগের ইতিহাস।',
        icon: '🕌',
        color: 'from-rose-500/10 to-rose-500/5 text-rose-800 border-rose-200/60',
    },
    {
        name: 'ইসলামী অর্থনীতি ও ফাইন্যান্স',
        slug: 'finance',
        desc: 'হালাল বিনিয়োগ, সুদবিহীন ব্যাংকিং, যাকাত হিসাব ও ব্যবসায়িক লেনদেন।',
        icon: '🪙',
        color: 'from-indigo-500/10 to-indigo-500/5 text-indigo-800 border-indigo-200/60',
    },
    {
        name: 'পারিবারিক ও আত্মশুদ্ধি',
        slug: 'tazkiyah',
        desc: 'তাজকিয়ায়ে নফস, চারিত্রিক উন্নয়ন, সুন্দর দাম্পত্য জীবন ও সন্তান লালন-পালন।',
        icon: '🌱',
        color: 'from-green-500/10 to-green-500/5 text-green-800 border-green-200/60',
    },
];

export default function CategoriesSection({ categories = [] }) {
    const catList = Array.isArray(categories) ? categories : (categories && typeof categories === 'object' ? Object.values(categories) : []);
    // Merge dynamic categories if available
    const displayList = catList.length > 0 ? catList.map((cat, idx) => {
        const fallback = DEFAULT_CATEGORIES[idx % DEFAULT_CATEGORIES.length];
        return {
            name: cat.name,
            slug: cat.slug,
            desc: fallback.desc,
            icon: fallback.icon,
            color: fallback.color,
            courses_count: cat.courses_count,
        };
    }) : DEFAULT_CATEGORIES;

    return (
        <section className="py-16 sm:py-24 bg-[#F8FAF8]">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="text-center max-w-2xl mx-auto">
                    <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                        বিষয়ভিত্তিক বিভাগ
                    </span>
                    <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                        ইসলামী জ্ঞানের প্রধান শাখাসমূহ
                    </h2>
                    <p className="mt-3 text-base text-slate-600 font-bangla">
                        আপনার আগ্রহ ও প্রয়োজন অনুযায়ী বিভাগ বেছে নিয়ে জ্ঞানার্জনের যাত্রা শুরু করুন।
                    </p>
                </div>

                {/* Grid */}
                <div className="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {displayList.map((item, index) => (
                        <Link
                            key={index}
                            href={`/courses/category/${item.slug}`}
                            className={`group relative flex flex-col justify-between rounded-2xl border bg-gradient-to-b ${item.color} p-6 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg bg-white`}
                        >
                            <div>
                                <div className="flex items-center justify-between">
                                    <span className="text-3xl p-2 rounded-xl bg-white shadow-sm border border-slate-100 group-hover:scale-110 transition-transform">
                                        {item.icon}
                                    </span>
                                    {item.courses_count !== undefined && (
                                        <span className="rounded-full bg-white/80 px-2.5 py-0.5 text-xs font-bold text-slate-700 shadow-2xs">
                                            {item.courses_count} টি কোর্স
                                        </span>
                                    )}
                                </div>

                                <h3 className="mt-5 text-lg font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors font-bangla">
                                    {item.name}
                                </h3>

                                <p className="mt-2 text-xs text-slate-600 leading-relaxed line-clamp-2">
                                    {item.desc}
                                </p>
                            </div>

                            <div className="mt-5 flex items-center text-xs font-bold text-[#1A2E2F] group-hover:text-emerald-700">
                                <span>কোর্সগুলো দেখুন</span>
                                <svg className="ml-1 h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}
