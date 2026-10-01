import React, { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function QuranShow({ surah, prevSurah, nextSurah }) {
    const { auth } = usePage().props;
    const [fontSize, setFontSize] = useState('text-2xl sm:text-3xl'); // text-xl | text-2xl sm:text-3xl | text-4xl
    const [showTranslation, setShowTranslation] = useState(true);
    const [copiedIndex, setCopiedIndex] = useState(null);

    const handleCopy = (text, idx) => {
        navigator.clipboard.writeText(text);
        setCopiedIndex(idx);
        setTimeout(() => setCopiedIndex(null), 2500);
    };

    const handleHifzStatus = (status) => {
        router.post(`/quran/${surah.number}/memorize`, { status }, { preserveScroll: true });
    };

    return (
        <MainLayout>
            <Head>
                <title>{`${surah.name_bangla} (${surah.name_arabic}) — আল-কুরআন | আত-তাআল্লুম`}</title>
                <meta name="description" content={`${surah.name_bangla} এর বিশুদ্ধ আরবী তিলাওয়াত, বাংলা অর্থ ও তাফসীর।`} />
            </Head>

            {/* Surah Header Banner */}
            <div className="bg-[#102526] text-white py-12 sm:py-16 relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px] opacity-10"></div>
                <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                    {/* Breadcrumbs */}
                    <div className="flex items-center justify-center gap-2 text-xs text-slate-400 mb-4">
                        <Link href="/quran" className="hover:text-[#FFF99A]">কুরআন সূচিপত্র</Link>
                        <span>/</span>
                        <span className="text-white">সূরা {surah.name_bangla}</span>
                    </div>

                    <h1 className="font-arabic text-4xl sm:text-5xl font-bold text-[#FFF99A]">
                        {surah.name_arabic}
                    </h1>

                    <h2 className="mt-3 text-2xl sm:text-3xl font-bold text-white font-bangla">
                        সূরা {surah.name_bangla}
                    </h2>

                    <div className="mt-4 flex items-center justify-center gap-4 text-xs font-semibold text-slate-300">
                        <span className="rounded-full bg-white/10 px-3 py-1">সূরা নং {surah.number}</span>
                        <span className="rounded-full bg-white/10 px-3 py-1">মোট আয়াত: {surah.ayah_count} টি</span>
                        <span className="rounded-full bg-white/10 px-3 py-1">
                            {surah.revelation_type === 'Meccan' ? 'মাক্কী সূরা' : 'মাদানী সূরা'}
                        </span>
                    </div>

                    {/* Hifz Tracker Button if user logged in */}
                    {auth?.user && (
                        <div className="mt-6 flex justify-center gap-2">
                            <button
                                onClick={() => handleHifzStatus('memorizing')}
                                className="rounded-xl border border-amber-400/40 bg-amber-400/10 px-3.5 py-1.5 text-xs font-bold text-amber-300 hover:bg-amber-400/20 transition"
                            >
                                ⏳ মুখস্থ চলছে
                            </button>
                            <button
                                onClick={() => handleHifzStatus('memorized')}
                                className="rounded-xl border border-emerald-400/40 bg-emerald-400/10 px-3.5 py-1.5 text-xs font-bold text-emerald-300 hover:bg-emerald-400/20 transition"
                            >
                                ✓ হিফজ সম্পন্ন
                            </button>
                        </div>
                    )}
                </div>
            </div>

            {/* Reading Toolbar */}
            <div className="sticky top-[61px] z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                    {/* Prev / Next buttons */}
                    <div className="flex items-center gap-2">
                        {prevSurah && (
                            <Link
                                href={`/quran/${prevSurah.number}`}
                                className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-100"
                            >
                                ← {prevSurah.name_bangla}
                            </Link>
                        )}
                        {nextSurah && (
                            <Link
                                href={`/quran/${nextSurah.number}`}
                                className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-100"
                            >
                                {nextSurah.name_bangla} →
                            </Link>
                        )}
                    </div>

                    {/* Controls */}
                    <div className="flex items-center gap-3">
                        <label className="flex items-center gap-1.5 cursor-pointer font-medium text-slate-700">
                            <input
                                type="checkbox"
                                checked={showTranslation}
                                onChange={(e) => setShowTranslation(e.target.checked)}
                                className="rounded border-slate-300 text-[#1A2E2F] focus:ring-0"
                            />
                            <span>বাংলা অর্থ</span>
                        </label>

                        {/* Font size toggles */}
                        <div className="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                            <button
                                onClick={() => setFontSize('text-xl sm:text-2xl')}
                                className={`px-2 py-0.5 rounded font-bold ${fontSize.includes('text-xl') ? 'bg-[#1A2E2F] text-white' : 'text-slate-600'}`}
                            >
                                ছোট
                            </button>
                            <button
                                onClick={() => setFontSize('text-2xl sm:text-3xl')}
                                className={`px-2 py-0.5 rounded font-bold ${fontSize.includes('text-2xl') ? 'bg-[#1A2E2F] text-white' : 'text-slate-600'}`}
                            >
                                স্বাভাবিক
                            </button>
                            <button
                                onClick={() => setFontSize('text-3xl sm:text-4xl')}
                                className={`px-2 py-0.5 rounded font-bold ${fontSize.includes('text-3xl') ? 'bg-[#1A2E2F] text-white' : 'text-slate-600'}`}
                            >
                                বড়
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Ayah Content Area */}
            <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-10 space-y-6">
                {/* Bismillah Header (except for Surah At-Tawbah) */}
                {surah.number !== 9 && (
                    <div className="rounded-2xl border border-slate-200/60 bg-white p-6 text-center shadow-xs">
                        <span className="font-arabic text-2xl sm:text-3xl font-bold text-[#102526]">
                            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                        </span>
                        {showTranslation && (
                            <p className="mt-2 text-xs text-slate-500 font-bangla">
                                পরম করুণাময় ও অসীম দয়ালু আল্লাহর নামে শুরু করছি
                            </p>
                        )}
                    </div>
                )}

                {/* Ayah List */}
                {surah.ayahs && surah.ayahs.length > 0 ? (
                    surah.ayahs.map((ayah, idx) => (
                        <div
                            key={ayah.number || idx}
                            className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-all hover:border-[#1A2E2F]/30"
                        >
                            {/* Ayah Meta & Actions */}
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 text-xs">
                                <span className="flex h-7 w-7 items-center justify-center rounded-full bg-[#1A2E2F] font-bold text-[#FFF99A]">
                                    {ayah.number}
                                </span>

                                <button
                                    onClick={() => handleCopy(`${ayah.text_uthmani}\n${ayah.bangla_translation?.text || ''}`, idx)}
                                    className="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-100"
                                >
                                    {copiedIndex === idx ? 'কপি হয়েছে!' : 'কপি করুন'}
                                </button>
                            </div>

                            {/* Uthmani Arabic Text */}
                            <div className="text-right" dir="rtl">
                                <p className={`font-arabic ${fontSize} font-bold leading-loose text-slate-900 tracking-wide`}>
                                    {ayah.text_uthmani}
                                </p>
                            </div>

                            {/* Bengali Translation */}
                            {showTranslation && ayah.bangla_translation && (
                                <div className="mt-5 border-t border-slate-100 pt-4">
                                    <p className="text-sm sm:text-base text-slate-700 font-bangla leading-relaxed">
                                        {ayah.bangla_translation.text}
                                    </p>
                                </div>
                            )}
                        </div>
                    ))
                ) : (
                    <div className="rounded-2xl border border-dashed border-slate-300 p-12 text-center bg-white">
                        <span className="text-4xl">📖</span>
                        <h3 className="mt-3 text-lg font-bold text-slate-800">আয়াত লোড হচ্ছে</h3>
                        <p className="text-xs text-slate-500 mt-1">পবিত্র কুরআনের আয়াত ডাটাবেজ থেকে লোড করা হচ্ছে...</p>
                    </div>
                )}

                {/* Bottom Navigation */}
                <div className="flex items-center justify-between pt-6 border-t border-slate-200">
                    <div>
                        {prevSurah && (
                            <Link
                                href={`/quran/${prevSurah.number}`}
                                className="btn-secondary text-xs !py-2.5"
                            >
                                ← পূর্ববর্তী সূরা ({prevSurah.name_bangla})
                            </Link>
                        )}
                    </div>
                    <div>
                        <Link href="/quran" className="text-xs font-bold text-slate-600 hover:text-[#1A2E2F]">
                            সূচিপত্র
                        </Link>
                    </div>
                    <div>
                        {nextSurah && (
                            <Link
                                href={`/quran/${nextSurah.number}`}
                                className="btn-primary text-xs !py-2.5"
                            >
                                পরবর্তী সূরা ({nextSurah.name_bangla}) →
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
