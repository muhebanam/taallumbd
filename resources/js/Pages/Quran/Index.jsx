import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function QuranIndex({ surahs = [], filters = {}, userProgress = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [filterType, setFilterType] = useState('all'); // all | Meccan | Medinan

    const filteredSurahs = surahs.filter(s => {
        const matchesType = filterType === 'all' || s.revelation_type === filterType;
        const matchesSearch = !search || 
            s.name_bangla.toLowerCase().includes(search.toLowerCase()) ||
            s.name_arabic.includes(search) ||
            s.number.toString() === search;
        return matchesType && matchesSearch;
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/quran', { search }, { preserveState: true });
    };

    return (
        <MainLayout>
            <Head>
                <title>আল-কুরআনুল কারীম — আত-তাআল্লুম</title>
                <meta name="description" content="পবিত্র আল-কুরআনের ১১৪টি সূরা, আরবি তিলাওয়াত, সহজ সরল বাংলা অনুবাদ এবং হিফজ ট্র্যাকার।" />
            </Head>

            {/* Quran Banner */}
            <div className="relative overflow-hidden bg-[#102526] text-white py-16 sm:py-20">
                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px] opacity-10"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-block rounded-full border border-[#FFF99A]/30 bg-white/5 px-4 py-1 text-xs font-semibold text-[#FFF99A]">
                        كِتَابٌ أَنزَلْنَاهُ إِلَيْكَ مُبَارَكٌ
                    </span>
                    <h1 className="mt-4 text-3xl sm:text-5xl font-extrabold font-bangla tracking-tight">
                        আল-কুরআনুল কারীম
                    </h1>
                    <p className="mt-3 text-base text-slate-300 font-bangla max-w-2xl mx-auto">
                        উসমানি লিপি, বিশুদ্ধ বাংলা অনুবাদ ও তাফসীর সহ ১১৪টি সূরার পূর্ণাঙ্গ পাঠশালা ও হিফজ ট্র্যাকিং ব্যবস্থা।
                    </p>

                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="mt-8 max-w-xl mx-auto flex gap-2">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="সূরার নাম (যেমন: ফাতিহা) বা নম্বর লিখুন..."
                            className="flex-1 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-slate-400 backdrop-blur-md focus:border-[#FFF99A] focus:outline-none focus:ring-1 focus:ring-[#FFF99A]"
                        />
                        <button type="submit" className="btn-accent text-xs !px-5 !py-3">
                            অনুসন্ধান
                        </button>
                    </form>
                </div>
            </div>

            {/* Filter Pills */}
            <div className="bg-white border-b border-slate-200/80 sticky top-[61px] z-30 shadow-2xs">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => setFilterType('all')}
                            className={`rounded-lg px-3 py-1.5 text-xs font-bold transition ${
                                filterType === 'all'
                                    ? 'bg-[#1A2E2F] text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            সকল সূরা ({surahs.length})
                        </button>
                        <button
                            onClick={() => setFilterType('Meccan')}
                            className={`rounded-lg px-3 py-1.5 text-xs font-bold transition ${
                                filterType === 'Meccan'
                                    ? 'bg-[#1A2E2F] text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            মাক্কী সূরা
                        </button>
                        <button
                            onClick={() => setFilterType('Medinan')}
                            className={`rounded-lg px-3 py-1.5 text-xs font-bold transition ${
                                filterType === 'Medinan'
                                    ? 'bg-[#1A2E2F] text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            মাদানী সূরা
                        </button>
                    </div>

                    <span className="text-xs text-slate-500 hidden sm:block">
                        মোট প্রদর্শিত: <strong>{filteredSurahs.length}</strong> টি সূরা
                    </span>
                </div>
            </div>

            {/* Surah Grid */}
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {filteredSurahs.map((surah) => {
                        const status = userProgress[surah.id];
                        return (
                            <Link
                                key={surah.number}
                                href={`/quran/${surah.number}`}
                                className="group relative flex items-center justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#1A2E2F]/40 hover:shadow-lg"
                            >
                                <div className="flex items-center gap-4">
                                    {/* Surah Number Icon */}
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-[#1A2E2F] group-hover:bg-[#1A2E2F] group-hover:text-[#FFF99A] transition-colors">
                                        {surah.number}
                                    </div>

                                    <div>
                                        <div className="flex items-center gap-2">
                                            <h3 className="text-base font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors font-bangla">
                                                {surah.name_bangla}
                                            </h3>
                                            {status && (
                                                <span className={`text-[10px] font-bold px-1.5 py-0.2 rounded ${
                                                    status === 'memorized' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                                }`}>
                                                    {status === 'memorized' ? 'হিফজ সম্পন্ন' : 'মুখস্থ চলছে'}
                                                </span>
                                            )}
                                        </div>
                                        <div className="mt-1 flex items-center gap-2 text-xs text-slate-500 font-medium">
                                            <span>{surah.revelation_type === 'Meccan' ? 'মাক্কী' : 'মাদানী'}</span>
                                            <span>•</span>
                                            <span>{surah.ayah_count} টি আয়াত</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Arabic Calligraphic Name */}
                                <div className="text-right">
                                    <span className="font-arabic text-xl font-bold text-emerald-950 group-hover:text-emerald-700 transition-colors">
                                        {surah.name_arabic}
                                    </span>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </MainLayout>
    );
}
