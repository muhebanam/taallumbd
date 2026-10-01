import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import Pagination from '../../Components/Pagination';

export default function FatawaIndex({ fatawa, categories, scholars = [], activeCategory, filters = {}, stats = {} }) {
    const [search, setSearch] = useState(filters.q || '');
    const [scholarId, setScholarId] = useState(filters.scholar_id || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(activeCategory ? `/fatawa/category/${activeCategory.slug}` : '/fatawa', {
            q: search || undefined,
            scholar_id: scholarId || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleScholarChange = (e) => {
        const val = e.target.value;
        setScholarId(val);
        router.get(activeCategory ? `/fatawa/category/${activeCategory.slug}` : '/fatawa', {
            q: search || undefined,
            scholar_id: val || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <MainLayout>
            <Head title={activeCategory ? `${activeCategory.name} — ফাতাওয়া ও শরয়ী সমাধান` : 'ফাতাওয়া ও শরয়ী সমাধান সম্ভার — আত-তাআল্লুম'} />

            {/* Hero Section */}
            <div className="relative overflow-hidden bg-gradient-to-b from-[#102526] via-[#1A2E2F] to-[#102526] py-14 text-white">
                <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px]"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/30 bg-[#FFF99A]/10 px-4 py-1.5 text-xs font-semibold text-[#FFF99A]">
                        <span className="h-2 w-2 rounded-full bg-[#FFF99A] animate-pulse"></span>
                        দারুল ইফতা ও শরীয়াহ বোর্ড
                    </span>
                    <h1 className="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl text-white">
                        ফাতাওয়া ও দ্বীনি প্রশ্নোত্তর
                    </h1>
                    <p className="mx-auto mt-3 max-w-2xl text-base text-[#F8FAF8]/80 sm:text-lg">
                        কুরআন, সুন্নাহ ও নির্ভরযোগ্য ফিকহের আলোকে বিজ্ঞ মুফতিয়ানে কেরামের তাহকীকপূর্ণ শরয়ী সমাধান
                    </p>

                    {/* Arabic Ayah Callout */}
                    <div className="mx-auto mt-6 max-w-xl rounded-2xl border border-[#254244] bg-[#102526]/80 p-4 backdrop-blur shadow-inner">
                        <p className="font-amiri text-lg text-[#FFF99A]" dir="rtl">
                            « فَاسْأَلُوا أَهْلَ الذِّكْرِ إِن كُنتُمْ لَا تَعْلَمُونَ »
                        </p>
                        <p className="mt-1 text-xs text-white/70">
                            "অতএব তোমরা জ্ঞানীদের জিজ্ঞাসা করো, যদি তোমরা না জানো।" — [সূরা আন-নাহল: ৪৩]
                        </p>
                    </div>

                    {/* Search & Filter Bar */}
                    <form onSubmit={handleSearch} className="mx-auto mt-8 max-w-3xl">
                        <div className="flex flex-col sm:flex-row gap-3 rounded-2xl bg-[#1A2E2F]/90 p-2 border border-[#335558] shadow-2xl backdrop-blur">
                            <div className="relative flex-1">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="বিষয়, মাসআলা বা প্রশ্ন দিয়ে অনুসন্ধান করুন..."
                                    className="w-full rounded-xl bg-white/10 px-4 py-3 pl-11 text-sm text-white placeholder-white/50 focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[#FFF99A]"
                                />
                                <svg className="absolute left-3.5 top-3.5 h-5 w-5 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            {scholars.length > 0 && (
                                <select
                                    value={scholarId}
                                    onChange={handleScholarChange}
                                    aria-label="মুফতী বা আলেম নির্বাচন করুন"
                                    className="rounded-xl bg-white/10 px-3 py-3 text-sm text-white focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[#FFF99A]"
                                >
                                    <option value="" className="bg-[#102526] text-white">সকল মুফতী ও শিক্ষক</option>
                                    {scholars.map((s) => (
                                        <option key={s.id} value={s.id} className="bg-[#102526] text-white">
                                            {s.title_prefix} {s.user?.name}
                                        </option>
                                    ))}
                                </select>
                            )}

                            <button
                                type="submit"
                                className="inline-flex items-center justify-center rounded-xl bg-[#FFF99A] px-6 py-3 text-sm font-bold text-[#102526] shadow-md transition hover:bg-[#fff780] hover:shadow-lg"
                            >
                                অনুসন্ধান
                            </button>
                        </div>
                    </form>

                    {/* Stats Strip */}
                    <div className="mx-auto mt-8 flex max-w-xl items-center justify-center gap-6 text-xs text-white/70">
                        <span className="flex items-center gap-1.5">
                            <span className="font-bold text-[#FFF99A]">{stats.total_fatawa || fatawa.total || 0}+</span> ফাতাওয়া সংকলিত
                        </span>
                        <span className="text-[#335558]">•</span>
                        <span className="flex items-center gap-1.5">
                            <span className="font-bold text-[#FFF99A]">{stats.answered_this_month || 0}+</span> এই মাসে উত্তরপ্রাপ্ত
                        </span>
                        <span className="text-[#335558]">•</span>
                        <span>১০০% তাহকীকপূর্ণ ও পরীক্ষিত</span>
                    </div>
                </div>
            </div>

            {/* Main Content Area */}
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="gap-8 lg:flex">
                    {/* Left Sidebar */}
                    <aside className="mb-8 w-full shrink-0 lg:mb-0 lg:w-72">
                        {/* Ask Button Card */}
                        <div className="rounded-2xl border border-emerald-900/10 bg-gradient-to-br from-[#102526] to-[#1A2E2F] p-6 text-white shadow-lg">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-[#FFF99A]/20 text-[#FFF99A] mb-4">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-bold text-white">আপনার কি কোনো প্রশ্ন আছে?</h3>
                            <p className="mt-1 text-xs text-white/70 leading-relaxed">
                                দৈনন্দিন জীবন, ইবাদত কিংবা ব্যবসা সংক্রান্ত কোনো মাসআলা জানতে সরাসরি প্রশ্ন জমা দিন।
                            </p>
                            <Link
                                href="/fatawa/ask"
                                className="mt-5 block w-full rounded-xl bg-[#FFF99A] py-2.5 text-center text-sm font-bold text-[#102526] shadow transition hover:bg-[#fff780]"
                            >
                                সরাসরি প্রশ্ন করুন
                            </Link>
                        </div>

                        {/* Category List */}
                        <div className="mt-6 rounded-2xl border border-[#254244]/20 bg-white p-5 shadow-sm">
                            <h3 className="font-bold text-[#102526] flex items-center justify-between">
                                <span>বিষয়ভিত্তিক বিভাগসমূহ</span>
                                <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                                    {categories.length}
                                </span>
                            </h3>

                            <div className="mt-4 space-y-1">
                                <Link
                                    href="/fatawa"
                                    className={`flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition ${
                                        !activeCategory
                                            ? 'bg-[#102526] text-[#FFF99A] font-semibold'
                                            : 'text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    <span>সকল ফাতাওয়া</span>
                                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>

                                {categories.map((c) => {
                                    const isActive = activeCategory?.id === c.id;
                                    return (
                                        <div key={c.id} className="pt-1">
                                            <Link
                                                href={`/fatawa/category/${c.slug}`}
                                                className={`flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition ${
                                                    isActive
                                                        ? 'bg-[#102526] text-[#FFF99A] font-semibold'
                                                        : 'text-gray-700 hover:bg-gray-100'
                                                }`}
                                            >
                                                <span>{c.name}</span>
                                                <svg className="h-3.5 w-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>

                                            {/* Subcategories */}
                                            {c.children && c.children.length > 0 && (
                                                <div className="ml-3 mt-1 space-y-1 border-l-2 border-[#102526]/10 pl-2">
                                                    {c.children.map((child) => (
                                                        <Link
                                                            key={child.id}
                                                            href={`/fatawa/category/${child.slug}`}
                                                            className={`block rounded-lg px-2.5 py-1.5 text-xs transition ${
                                                                activeCategory?.id === child.id
                                                                    ? 'bg-[#102526]/10 font-bold text-[#102526]'
                                                                    : 'text-gray-600 hover:bg-gray-100'
                                                            }`}
                                                        >
                                                            {child.name}
                                                        </Link>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Etiquette Box */}
                        <div className="mt-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-amber-900">
                            <h4 className="font-bold text-sm flex items-center gap-1.5">
                                <svg className="h-4 w-4 text-amber-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                </svg>
                                প্রশ্ন করার আদব (আদাবুস্ সুওয়াল)
                            </h4>
                            <ul className="mt-2.5 space-y-1.5 text-xs leading-relaxed text-amber-950/80">
                                <li>• প্রশ্ন পরিষ্কার ও সংক্ষেপে লিখুন।</li>
                                <li>• কোনো তর্কের উদ্দেশ্যে নয়, আমলের নিয়তে জানুন।</li>
                                <li>• পারিবারিক গোপনীয়তা থাকলে গোপন অপশন নির্বাচন করুন।</li>
                            </ul>
                        </div>
                    </aside>

                    {/* Right Content / Fatawa Cards */}
                    <div className="min-w-0 flex-1">
                        {/* Header & Active Filter Bar */}
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4">
                            <div>
                                <h2 className="text-2xl font-bold text-[#102526]">
                                    {activeCategory ? activeCategory.name : 'সকল ফাতাওয়া ও সমাধান'}
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    মোট {fatawa.total || 0} টি ফাতাওয়া পাওয়া গেছে
                                </p>
                            </div>

                            {(filters.q || filters.scholar_id) && (
                                <div className="mt-2 sm:mt-0 flex items-center gap-2">
                                    <span className="text-xs text-gray-500">ফিল্টার সক্রিয়:</span>
                                    {filters.q && (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-[#102526]/10 px-2.5 py-0.5 text-xs font-medium text-[#102526]">
                                            "{filters.q}"
                                        </span>
                                    )}
                                    <Link
                                        href="/fatawa"
                                        className="text-xs font-semibold text-rose-600 hover:underline"
                                    >
                                        মুছে ফেলুন
                                    </Link>
                                </div>
                            )}
                        </div>

                        {/* Fatawa Cards Grid */}
                        {fatawa.data && fatawa.data.length > 0 ? (
                            <div className="mt-6 space-y-4">
                                {fatawa.data.map((f) => {
                                    const answeringScholar = f.assigned_scholar?.user || f.teacher?.user || f.mufti;
                                    return (
                                        <article
                                            key={f.id}
                                            className="group relative rounded-2xl border border-gray-200/90 bg-white p-6 shadow-sm transition-all hover:border-[#1A2E2F]/40 hover:shadow-md"
                                        >
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <div className="flex items-center gap-2">
                                                    {f.category && (
                                                        <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                                                            {f.category.name}
                                                        </span>
                                                    )}
                                                    {f.views_count > 0 && (
                                                        <span className="flex items-center gap-1 text-xs text-gray-400">
                                                            <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            {f.views_count} বার পঠিত
                                                        </span>
                                                    )}
                                                </div>

                                                <span className="text-xs text-gray-400">
                                                    {f.published_at ? new Date(f.published_at).toLocaleDateString('bn-BD', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric'
                                                    }) : ''}
                                                </span>
                                            </div>

                                            {/* Question Title */}
                                            <h3 className="mt-3 text-lg font-bold text-[#102526] group-hover:text-emerald-800 transition">
                                                <Link href={`/fatawa/${f.id}`} className="hover:underline">
                                                    {f.question_title}
                                                </Link>
                                            </h3>

                                            {/* Question Snippet */}
                                            <p className="mt-2 line-clamp-2 text-sm text-gray-600 leading-relaxed">
                                                {f.question_body}
                                            </p>

                                            {/* Answer Preview / Snippet */}
                                            {f.answer_body && (
                                                <div className="mt-3 rounded-xl bg-gray-50 border-l-4 border-[#102526] p-3 text-xs text-gray-700">
                                                    <span className="font-bold text-[#102526]">উত্তর সংক্ষেপ: </span>
                                                    <span className="line-clamp-2">{f.answer_body}</span>
                                                </div>
                                            )}

                                            {/* Card Footer: Scholar & Action */}
                                            <div className="mt-4 flex flex-wrap items-center justify-between border-t border-gray-100 pt-4 text-xs">
                                                <div className="flex items-center gap-2">
                                                    <div className="flex h-7 w-7 items-center justify-center rounded-full bg-[#102526] text-[#FFF99A] font-bold text-xs">
                                                        {answeringScholar?.name ? answeringScholar.name.charAt(0) : 'ম'}
                                                    </div>
                                                    <div>
                                                        <span className="text-gray-500">উত্তর প্রদানকারী: </span>
                                                        <span className="font-semibold text-[#102526]">
                                                            {answeringScholar?.name || 'মুফতী পরিষদ, আত-তাআল্লুম'}
                                                        </span>
                                                    </div>
                                                </div>

                                                <Link
                                                    href={`/fatawa/${f.id}`}
                                                    className="inline-flex items-center gap-1 font-bold text-emerald-800 hover:text-emerald-950 transition"
                                                >
                                                    সম্পূর্ণ উত্তর পড়ুন
                                                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </Link>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="mt-12 rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 className="mt-4 text-base font-bold text-gray-900">কোনো ফাতাওয়া খুঁজে পাওয়া যায়নি</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    আপনার কাঙ্ক্ষিত বিষয়ের কোনো ফাতাওয়া পাওয়া যায়নি। আপনি চাইলে সরাসরি প্রশ্ন পাঠাতে পারেন।
                                </p>
                                <div className="mt-6">
                                    <Link
                                        href="/fatawa/ask"
                                        className="inline-flex items-center gap-2 rounded-xl bg-[#102526] px-5 py-2.5 text-sm font-semibold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                                    >
                                        নতুন প্রশ্ন করুন
                                    </Link>
                                </div>
                            </div>
                        )}

                        {/* Pagination */}
                        {fatawa.links && (
                            <div className="mt-8">
                                <Pagination links={fatawa.links} />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
