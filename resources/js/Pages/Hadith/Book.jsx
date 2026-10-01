import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import Pagination from '../../Components/Pagination';

export default function HadithBook({ book, hadiths, chapters = [], filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [copiedIndex, setCopiedIndex] = useState(null);

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(`/hadith/${book.slug}`, { search, chapter: filters.chapter }, { preserveState: true });
    };

    const handleChapterFilter = (chapId) => {
        router.get(`/hadith/${book.slug}`, { chapter: chapId || undefined, search: filters.search }, { preserveState: true });
    };

    const handleCopy = (text, idx) => {
        navigator.clipboard.writeText(text);
        setCopiedIndex(idx);
        setTimeout(() => setCopiedIndex(null), 2500);
    };

    const hadithList = hadiths.data || hadiths;

    return (
        <MainLayout>
            <Head>
                <title>{`${book.name_bangla} — হাদীস পাঠাগার | আত-তাআল্লুম`}</title>
                <meta name="description" content={`${book.name_bangla} (${book.name_arabic}) এর প্রামাণ্য হাদীসসমূহ, আরবী পাঠ ও বাংলা অনুবাদ।`} />
            </Head>

            {/* Book Header Banner */}
            <div className="bg-[#102526] text-white py-12 sm:py-16 relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px] opacity-10"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-2 text-xs text-slate-400 mb-4">
                        <Link href="/hadith" className="hover:text-[#FFF99A]">হাদীস গ্রন্থসমূহ</Link>
                        <span>/</span>
                        <span className="text-white">{book.name_bangla}</span>
                    </div>

                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <span className="font-arabic text-3xl sm:text-4xl font-bold text-[#FFF99A]">
                                {book.name_arabic}
                            </span>
                            <h1 className="mt-2 text-2xl sm:text-3xl font-extrabold text-white font-bangla">
                                {book.name_bangla}
                            </h1>
                            <p className="mt-1 text-xs sm:text-sm text-emerald-300 font-semibold">
                                সংকলক: {book.author}
                            </p>
                        </div>

                        {/* Search in Book */}
                        <form onSubmit={handleSearch} className="flex gap-2 max-w-md w-full">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="হাদীস বা বর্ণনাকারী অনুসন্ধান..."
                                className="flex-1 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:border-[#FFF99A] focus:outline-none focus:ring-1 focus:ring-[#FFF99A]"
                            />
                            <button type="submit" className="btn-accent text-xs !px-4 !py-2.5">
                                খুঁজুন
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {/* Chapter Bar (if chapters exist) */}
            {chapters.length > 0 && (
                <div className="bg-white border-b border-slate-200/80 sticky top-[61px] z-30 shadow-2xs">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3 overflow-x-auto">
                        <button
                            onClick={() => handleChapterFilter('')}
                            className={`rounded-lg px-3 py-1.5 text-xs font-bold shrink-0 transition ${
                                !filters.chapter
                                    ? 'bg-[#1A2E2F] text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            সকল অধ্যায়
                        </button>
                        {chapters.map((chap) => (
                            <button
                                key={chap.id}
                                onClick={() => handleChapterFilter(chap.id)}
                                className={`rounded-lg px-3 py-1.5 text-xs font-bold shrink-0 transition ${
                                    filters.chapter == chap.id
                                        ? 'bg-[#1A2E2F] text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }`}
                            >
                                {chap.number}. {chap.title_bangla}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* Hadiths Listing */}
            <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-10 space-y-8">
                {hadithList && hadithList.length > 0 ? (
                    hadithList.map((h, idx) => (
                        <div
                            key={h.id || idx}
                            className="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-sm transition-all hover:shadow-md hover:border-[#1A2E2F]/30"
                        >
                            {/* Hadith Meta Strip */}
                            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-6">
                                <div className="flex items-center gap-3">
                                    <span className="rounded-xl bg-[#1A2E2F] px-3 py-1 text-xs font-bold text-[#FFF99A]">
                                        হাদীস নং {h.number}
                                    </span>
                                    {h.grade && (
                                        <span className="rounded-lg bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-xs font-semibold">
                                            মান: {h.grade} {h.grade_by ? `(${h.grade_by})` : ''}
                                        </span>
                                    )}
                                </div>

                                <button
                                    onClick={() => handleCopy(`${h.text_arabic}\n\n${h.narrator ? h.narrator + ' থেকে বর্ণিত:\n' : ''}${h.text_bangla}`, idx)}
                                    className="rounded-lg bg-slate-50 border border-slate-200 px-3 py-1 text-xs text-slate-600 hover:bg-slate-100 transition"
                                >
                                    {copiedIndex === idx ? 'কপি হয়েছে!' : 'কপি করুন'}
                                </button>
                            </div>

                            {/* Arabic Matn */}
                            <div className="text-right" dir="rtl">
                                <p className="font-arabic text-xl sm:text-2xl font-bold leading-loose text-slate-900 tracking-wide">
                                    {h.text_arabic}
                                </p>
                            </div>

                            {/* Bangla Translation */}
                            <div className="mt-6 border-t border-slate-100 pt-5">
                                {h.narrator && (
                                    <p className="text-xs font-bold text-emerald-800 mb-2 font-bangla">
                                        {h.narrator} থেকে বর্ণিত:
                                    </p>
                                )}
                                <p className="text-sm sm:text-base text-slate-700 font-bangla leading-relaxed">
                                    {h.text_bangla}
                                </p>
                            </div>

                            {/* Explanation / Notes if available */}
                            {h.explanation && (
                                <div className="mt-5 rounded-2xl bg-slate-50 border border-slate-200/80 p-4 text-xs text-slate-600 leading-relaxed font-bangla">
                                    <span className="font-bold text-slate-800 block mb-1">ব্যাখ্যা ও ফায়দা:</span>
                                    {h.explanation}
                                </div>
                            )}
                        </div>
                    ))
                ) : (
                    <div className="rounded-3xl border border-dashed border-slate-300 p-12 text-center bg-white">
                        <span className="text-4xl">📜</span>
                        <h3 className="mt-3 text-lg font-bold text-slate-800">কোনো হাদীস পাওয়া যায়নি</h3>
                        <p className="text-xs text-slate-500 mt-1">অনুসন্ধানের শব্দ পরিবর্তন করে পুনরায় চেষ্টা করুন।</p>
                    </div>
                )}

                {/* Pagination */}
                {hadiths.links && hadiths.links.length > 3 && (
                    <div className="pt-6 flex justify-center">
                        <Pagination links={hadiths.links} />
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
