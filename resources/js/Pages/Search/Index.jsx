import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function SearchIndex({ searchQuery = '', activeType = 'all', searchResults = {} }) {
    const [query, setQuery] = useState(searchQuery);

    const filterTabs = [
        { key: 'all', label: 'সকল ফলাফল' },
        { key: 'courses', label: 'কোর্সসমূহ' },
        { key: 'teachers', label: 'শিক্ষকমণ্ডলী' },
        { key: 'lessons', label: 'কোর্স পাঠ' },
        { key: 'hadiths', label: 'হাদিস' },
        { key: 'quran', label: 'আল-কুরআন' },
        { key: 'fatawa', label: 'ফতোয়া' },
        { key: 'articles', label: 'প্রবন্ধ' },
        { key: 'publications', label: 'প্রকাশনা' },
    ];

    const handleSearch = (e) => {
        e.preventDefault();
        if (query.trim()) {
            router.get('/search', { q: query.trim(), type: activeType === 'all' ? undefined : activeType });
        }
    };

    const handleTabChange = (typeKey) => {
        router.get('/search', { q: query.trim() || undefined, type: typeKey === 'all' ? undefined : typeKey });
    };

    const getTypeBadgeColor = (type) => {
        switch (type) {
            case 'course': return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
            case 'teacher': return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
            case 'lesson': return 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30';
            case 'quran': return 'bg-teal-500/20 text-teal-300 border-teal-500/30';
            case 'hadith': return 'bg-blue-500/20 text-blue-300 border-blue-500/30';
            case 'fatwa': return 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30';
            case 'article': return 'bg-purple-500/20 text-purple-300 border-purple-500/30';
            case 'publication': return 'bg-orange-500/20 text-orange-300 border-orange-500/30';
            default: return 'bg-gray-500/20 text-gray-300 border-gray-500/30';
        }
    };

    const renderCard = (item) => (
        <Link
            key={`${item.type}-${item.id}`}
            href={item.url}
            className="group flex flex-col justify-between p-4 rounded-2xl bg-[#142C2E] border border-[#254244] hover:border-[#FFF99A]/40 transition hover:shadow-lg hover:shadow-black/40"
        >
            <div className="flex gap-3.5">
                {item.thumbnail ? (
                    <img
                        src={item.thumbnail}
                        alt=""
                        className="h-16 w-16 rounded-xl object-cover bg-white/5 shrink-0"
                        onError={(e) => { e.target.style.display = 'none'; }}
                    />
                ) : (
                    <div className="h-16 w-16 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-2xl shrink-0">
                        {item.type === 'quran' ? '📖' : item.type === 'hadith' ? '📜' : item.type === 'teacher' ? '👤' : item.type === 'course' ? '🎓' : '📚'}
                    </div>
                )}

                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                        <span className={`px-2 py-0.5 text-[10px] font-bold rounded-full border ${getTypeBadgeColor(item.type)}`}>
                            {item.type_label}
                        </span>
                        {item.subtitle && (
                            <span className="text-xs text-gray-400 truncate">
                                {item.subtitle}
                            </span>
                        )}
                    </div>

                    <h3 className="text-base font-bold text-white group-hover:text-[#FFF99A] transition truncate font-bangla">
                        {item.title}
                    </h3>

                    {item.description && (
                        <p className="text-xs text-gray-300 line-clamp-2 mt-1 leading-relaxed">
                            {item.description}
                        </p>
                    )}
                </div>
            </div>

            <div className="mt-3 pt-3 border-t border-white/5 flex items-center justify-between text-xs text-gray-400">
                <span className="group-hover:text-white transition">বিস্তারিত দেখুন &rarr;</span>
                {item.meta?.price !== undefined && (
                    <span className="font-bold text-[#FFF99A]">
                        {item.meta.is_free ? 'ফ্রি' : `৳ ${item.meta.price}`}
                    </span>
                )}
            </div>
        </Link>
    );

    const totalResults = searchResults?.total ?? 0;
    const groups = searchResults?.groups ?? {};
    const specificResults = searchResults?.results ?? [];

    return (
        <MainLayout>
            <Head title={searchQuery ? `"${searchQuery}" এর অনুসন্ধান ফলাফল — আত-তাআল্লুম` : 'অনুসন্ধান — আত-তাআল্লুম'} />

            <div className="bg-[#102526] min-h-screen py-10">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header Search Box */}
                    <div className="max-w-3xl mx-auto text-center mb-8">
                        <h1 className="text-3xl font-extrabold text-white mb-3 font-bangla">
                            আত-তাআল্লুম সমন্বিত অনুসন্ধান
                        </h1>
                        <p className="text-sm text-gray-300 mb-6">
                            কোর্স, লেকচার, নির্ভরযোগ্য হাদিস, তাফসির ও ফাতাওয়া এক ক্লিকে খুঁজুন
                        </p>

                        <form onSubmit={handleSearch} className="relative flex items-center shadow-xl rounded-2xl bg-[#1A383B] border border-[#254244] p-1.5 focus-within:border-[#FFF99A]/50 transition">
                            <svg className="h-5 w-5 text-gray-400 ml-3 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="কী খুঁজতে চান? যেমন: সালাত, নামায, ওজু, সাওম, বুখারী..."
                                className="w-full bg-transparent text-white placeholder-gray-400 text-base focus:outline-none px-2 font-bangla"
                            />
                            <button
                                type="submit"
                                className="rounded-xl bg-[#FFF99A] px-6 py-2.5 text-xs font-bold text-[#102526] hover:bg-[#fff780] transition shrink-0"
                            >
                                অনুসন্ধান
                            </button>
                        </form>
                    </div>

                    {/* Filter Tabs */}
                    <div className="flex items-center gap-2 overflow-x-auto pb-3 mb-8 border-b border-[#254244] no-scrollbar">
                        {filterTabs.map((tab) => (
                            <button
                                key={tab.key}
                                onClick={() => handleTabChange(tab.key)}
                                className={`px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition ${
                                    activeType === tab.key
                                        ? 'bg-[#FFF99A] text-[#102526] shadow-sm'
                                        : 'bg-[#142C2E] text-gray-300 hover:bg-white/10 hover:text-white border border-[#254244]'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {/* Results Overview Notice */}
                    {searchQuery && (
                        <div className="mb-6 flex items-center justify-between text-sm text-gray-300">
                            <p>
                                <span className="text-white font-bold">"{searchQuery}"</span> এর জন্য মোট{' '}
                                <span className="text-[#FFF99A] font-bold">{totalResults}</span> টি ফলাফল পাওয়া গেছে
                            </p>
                        </div>
                    )}

                    {/* Empty Query State */}
                    {!searchQuery && (
                        <div className="py-16 text-center rounded-3xl bg-[#142C2E]/60 border border-[#254244] p-8 max-w-xl mx-auto">
                            <div className="text-4xl mb-3">🔍</div>
                            <h3 className="text-lg font-bold text-white mb-2 font-bangla">অনুসন্ধান শুরু করুন</h3>
                            <p className="text-xs text-gray-400 mb-6 leading-relaxed">
                                আরবি হরকতসহ বা হরকত ছাড়া এবং বাংলা প্রতিশব্দ (যেমন নামায / সালাত) দিয়ে সহজেই প্ল্যাটফর্মের সকল তথ্য খুঁজে নিন।
                            </p>
                            <div className="flex flex-wrap justify-center gap-2">
                                {['সালাত', 'নামায', 'যাকাত', 'রোজা', 'সহীহ বুখারী', 'তাওহীদ', 'হজ্ব'].map((suggest) => (
                                    <button
                                        key={suggest}
                                        onClick={() => {
                                            setQuery(suggest);
                                            router.get('/search', { q: suggest });
                                        }}
                                        className="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-gray-300 hover:text-[#FFF99A] hover:border-[#FFF99A]/40 transition"
                                    >
                                        #{suggest}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Empty Results State */}
                    {searchQuery && totalResults === 0 && (
                        <div className="py-16 text-center rounded-3xl bg-[#142C2E]/60 border border-[#254244] p-8 max-w-lg mx-auto">
                            <div className="text-4xl mb-3">🏜️</div>
                            <h3 className="text-lg font-bold text-white mb-2 font-bangla">কোনো ফলাফল পাওয়া যায়নি</h3>
                            <p className="text-xs text-gray-400 leading-relaxed">
                                "{searchQuery}" এর সাথে সামঞ্জস্যপূর্ণ কোনো কনটেন্ট পাওয়া যায়নি। অনুগ্রহ করে ভিন্ন শব্দ বা বানান ব্যবহার করে চেষ্টা করুন।
                            </p>
                        </div>
                    )}

                    {/* Grouped View (activeType === 'all') */}
                    {activeType === 'all' && totalResults > 0 && (
                        <div className="space-y-10">
                            {Object.entries(groups).map(([groupKey, items]) => {
                                if (!items || items.length === 0) return null;
                                const groupName = filterTabs.find((t) => t.key === groupKey)?.label ?? groupKey;

                                return (
                                    <section key={groupKey} className="space-y-4">
                                        <div className="flex items-center justify-between border-b border-[#254244] pb-2">
                                            <h2 className="text-xl font-bold text-white flex items-center gap-2 font-bangla">
                                                <span>{groupName}</span>
                                                <span className="text-xs font-normal text-gray-400">({items.length})</span>
                                            </h2>
                                            <button
                                                onClick={() => handleTabChange(groupKey)}
                                                className="text-xs text-[#FFF99A] hover:underline"
                                            >
                                                আরও দেখুন &rarr;
                                            </button>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            {items.map(renderCard)}
                                        </div>
                                    </section>
                                );
                            })}
                        </div>
                    )}

                    {/* Specific Filter View */}
                    {activeType !== 'all' && specificResults.length > 0 && (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {specificResults.map(renderCard)}
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}
