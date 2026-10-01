import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import Pagination from '../../Components/Pagination';

export default function CommunityIndex({ posts, topics = [], likedPostIds = [], filters = {}, stats = {} }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.q || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/community', {
            q: search || undefined,
            topic: filters.topic !== 'all' ? filters.topic : undefined,
            sort: filters.sort,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleTopicChange = (topicId) => {
        router.get('/community', {
            q: filters.q || undefined,
            topic: topicId !== 'all' ? topicId : undefined,
            sort: filters.sort,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSortChange = (sortType) => {
        router.get('/community', {
            q: filters.q || undefined,
            topic: filters.topic !== 'all' ? filters.topic : undefined,
            sort: sortType,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleLike = (postId, e) => {
        e.preventDefault();
        if (!auth.user) {
            router.get('/login');
            return;
        }
        router.post(`/community/${postId}/like`, {}, {
            preserveScroll: true,
        });
    };

    const getTopicName = (topicId) => {
        const found = topics.find((t) => t.id === topicId);
        return found ? found.label : 'সাধারণ আলোচনা';
    };

    return (
        <MainLayout>
            <Head title="জ্ঞানভিত্তিক উম্মাহ ফোরাম ও ইসলামিক কমিউনিটি — আত-তাআল্লুম" />

            {/* Hero Section */}
            <div className="relative overflow-hidden bg-gradient-to-b from-[#102526] via-[#1A2E2F] to-[#102526] py-14 text-white">
                <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px]"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/30 bg-[#FFF99A]/10 px-4 py-1.5 text-xs font-semibold text-[#FFF99A]">
                        <span className="h-2 w-2 rounded-full bg-[#FFF99A] animate-pulse"></span>
                        ইলমী মোযাকারা ও উন্মুক্ত ফোরাম
                    </span>
                    <h1 className="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl text-white">
                        আত-তাআল্লুম ইসলামিক কমিউনিটি
                    </h1>
                    <p className="mx-auto mt-3 max-w-2xl text-base text-[#F8FAF8]/80 sm:text-lg">
                        উস্তায, গবেষক ও শিক্ষার্থীদের জ্ঞান বিনিময়, পাঠ্য আলোচনা ও ইলমী সহযোগিতার উন্মুক্ত প্ল্যাটফর্ম
                    </p>

                    {/* Quran Callout */}
                    <div className="mx-auto mt-6 max-w-lg rounded-2xl border border-[#254244] bg-[#102526]/80 p-4 backdrop-blur shadow-inner">
                        <p className="font-amiri text-lg text-[#FFF99A]" dir="rtl">
                            « وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ »
                        </p>
                        <p className="mt-1 text-xs text-white/70">
                            "তোমরা সৎকর্ম ও আল্লাহভীতিতে পরস্পরে সহযোগিতা করো।" — [সূরা আল-মায়িদাহ: ২]
                        </p>
                    </div>

                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="mx-auto mt-8 max-w-2xl">
                        <div className="flex gap-2 rounded-2xl bg-[#1A2E2F]/90 p-2 border border-[#335558] shadow-2xl backdrop-blur">
                            <div className="relative flex-1">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="যেকোনো বিষয়, প্রশ্ন বা আলোচনা অনুসন্ধান করুন..."
                                    className="w-full rounded-xl bg-white/10 px-4 py-3 pl-11 text-sm text-white placeholder-white/50 focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[#FFF99A]"
                                />
                                <svg className="absolute left-3.5 top-3.5 h-5 w-5 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <button
                                type="submit"
                                className="rounded-xl bg-[#FFF99A] px-6 py-3 text-sm font-bold text-[#102526] shadow transition hover:bg-[#fff780]"
                            >
                                খুঁজুন
                            </button>
                        </div>
                    </form>

                    {/* Stats Strip */}
                    <div className="mx-auto mt-6 flex max-w-md items-center justify-center gap-6 text-xs text-white/70">
                        <span><strong className="text-[#FFF99A]">{stats.total_discussions || 0}</strong> টি আলোচনা</span>
                        <span>•</span>
                        <span><strong className="text-[#FFF99A]">{stats.solved_discussions || 0}</strong> টি সমাধানকৃত</span>
                        <span>•</span>
                        <span><strong className="text-[#FFF99A]">{stats.total_comments || 0}</strong> টি ইলমী মন্তব্য</span>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Topic Pills Carousel / Tabs */}
                <div className="no-scrollbar flex items-center gap-2 overflow-x-auto border-b border-gray-200 pb-4">
                    {topics.map((t) => (
                        <button
                            key={t.id}
                            onClick={() => handleTopicChange(t.id)}
                            className={`whitespace-nowrap rounded-xl px-4 py-2 text-xs font-bold transition ${
                                (filters.topic || 'all') === t.id
                                    ? 'bg-[#102526] text-[#FFF99A] shadow'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
                            }`}
                        >
                            {t.label}
                        </button>
                    ))}
                </div>

                <div className="mt-8 gap-8 lg:flex">
                    {/* Posts Feed (Main) */}
                    <div className="min-w-0 flex-1">
                        {/* Feed Controls: Sort Filters & Create Button */}
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-4">
                            <div className="flex items-center gap-2 text-xs">
                                <span className="font-semibold text-gray-500">ক্রমানুসারে:</span>
                                <button
                                    onClick={() => handleSortChange('latest')}
                                    className={`rounded-lg px-2.5 py-1 font-semibold ${filters.sort === 'latest' ? 'bg-gray-200 text-[#102526]' : 'text-gray-600 hover:text-gray-900'}`}
                                >
                                    সর্বশেষ
                                </button>
                                <button
                                    onClick={() => handleSortChange('popular')}
                                    className={`rounded-lg px-2.5 py-1 font-semibold ${filters.sort === 'popular' ? 'bg-gray-200 text-[#102526]' : 'text-gray-600 hover:text-gray-900'}`}
                                >
                                    জনপ্রিয়
                                </button>
                                <button
                                    onClick={() => handleSortChange('solved')}
                                    className={`rounded-lg px-2.5 py-1 font-semibold ${filters.sort === 'solved' ? 'bg-gray-200 text-[#102526]' : 'text-gray-600 hover:text-gray-900'}`}
                                >
                                    সমাধানকৃত
                                </button>
                                <button
                                    onClick={() => handleSortChange('unanswered')}
                                    className={`rounded-lg px-2.5 py-1 font-semibold ${filters.sort === 'unanswered' ? 'bg-gray-200 text-[#102526]' : 'text-gray-600 hover:text-gray-900'}`}
                                >
                                    অনুত্তরপ্রাপ্ত
                                </button>
                            </div>

                            <Link
                                href="/community/create"
                                className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#102526] px-4 py-2.5 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                            >
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                নতুন আলোচনা শুরু করুন
                            </Link>
                        </div>

                        {/* Post Cards List */}
                        {posts.data && posts.data.length > 0 ? (
                            <div className="mt-6 space-y-4">
                                {posts.data.map((p) => {
                                    const isLiked = likedPostIds.includes(p.id);
                                    return (
                                        <article
                                            key={p.id}
                                            className="group relative rounded-2xl border border-gray-200/90 bg-white p-5 sm:p-6 shadow-sm transition hover:border-[#102526]/30 hover:shadow-md"
                                        >
                                            <div className="flex items-start gap-4">
                                                {/* Upvote Button / Column */}
                                                <div className="flex flex-col items-center">
                                                    <button
                                                        onClick={(e) => handleLike(p.id, e)}
                                                        className={`flex flex-col items-center justify-center rounded-xl p-2 transition ${
                                                            isLiked
                                                                ? 'bg-emerald-100 text-emerald-800'
                                                                : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                                        }`}
                                                        title="ভোট দিন"
                                                    >
                                                        <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
                                                        </svg>
                                                        <span className="text-xs font-bold mt-0.5">{p.upvotes_count || 0}</span>
                                                    </button>
                                                </div>

                                                {/* Post Content */}
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        {p.is_pinned && (
                                                            <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 flex items-center gap-1">
                                                                📌 পিনকৃত
                                                            </span>
                                                        )}
                                                        {p.is_solved && (
                                                            <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 flex items-center gap-1">
                                                                ✓ সমাধানকৃত
                                                            </span>
                                                        )}
                                                        <span className="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-semibold text-gray-700">
                                                            {getTopicName(p.topic)}
                                                        </span>
                                                        {p.course && (
                                                            <span className="rounded-full bg-blue-50 px-2.5 py-0.5 text-[10px] font-medium text-blue-700">
                                                                কোর্স: {p.course.title}
                                                            </span>
                                                        )}
                                                    </div>

                                                    <h3 className="mt-2 text-base sm:text-lg font-bold text-[#102526] group-hover:text-emerald-900 transition">
                                                        <Link href={`/community/${p.id}`} className="hover:underline">
                                                            {p.title}
                                                        </Link>
                                                    </h3>

                                                    <p className="mt-1 line-clamp-2 text-xs sm:text-sm text-gray-600 leading-relaxed">
                                                        {p.body}
                                                    </p>

                                                    {/* Meta Info */}
                                                    <div className="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500">
                                                        <div className="flex items-center gap-2">
                                                            <div className="flex h-6 w-6 items-center justify-center rounded-full bg-[#102526] text-[#FFF99A] text-[10px] font-bold">
                                                                {p.user?.name ? p.user.name.charAt(0) : 'স'}
                                                            </div>
                                                            <span className="font-semibold text-gray-800">{p.user?.name}</span>
                                                            {p.user?.role === 'instructor' && (
                                                                <span className="rounded-full bg-emerald-100 px-2 py-0.2 text-[10px] font-bold text-emerald-800">উস্তায</span>
                                                            )}
                                                            {p.user?.role === 'admin' && (
                                                                <span className="rounded-full bg-purple-100 px-2 py-0.2 text-[10px] font-bold text-purple-800">অ্যাডমিন</span>
                                                            )}
                                                            <span>•</span>
                                                            <span>{new Date(p.created_at).toLocaleDateString('bn-BD')}</span>
                                                        </div>

                                                        <div className="flex items-center gap-3">
                                                            <span className="flex items-center gap-1 text-gray-400">
                                                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                </svg>
                                                                {p.comments_count || 0} টি মন্তব্য
                                                            </span>
                                                            {p.views_count > 0 && (
                                                                <span className="flex items-center gap-1 text-gray-400">
                                                                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                    </svg>
                                                                    {p.views_count}
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="mt-12 rounded-3xl border border-dashed border-gray-300 p-12 text-center">
                                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <h3 className="mt-4 text-base font-bold text-gray-900">এই বিভাগে কোনো আলোচনা পাওয়া যায়নি</h3>
                                <p className="mt-1 text-xs text-gray-500">
                                    আপনি প্রথম ব্যক্তি হিসেবে এই বিষয়ে একটি আলোচনা শুরু করতে পারেন।
                                </p>
                                <div className="mt-6">
                                    <Link
                                        href="/community/create"
                                        className="rounded-xl bg-[#102526] px-5 py-2.5 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                                    >
                                        নতুন আলোচনা শুরু করুন
                                    </Link>
                                </div>
                            </div>
                        )}

                        <div className="mt-8">
                            <Pagination links={posts.links} />
                        </div>
                    </div>

                    {/* Right Sidebar */}
                    <aside className="mt-10 lg:mt-0 w-full shrink-0 lg:w-80 space-y-6">
                        {/* Create Discussion Card */}
                        <div className="rounded-3xl border border-[#254244]/20 bg-gradient-to-br from-[#102526] to-[#1A2E2F] p-6 text-white shadow-lg">
                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#FFF99A]/20 text-[#FFF99A] mb-4">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-bold text-white">জ্ঞানের আলোচনায় অংশ নিন</h3>
                            <p className="mt-1 text-xs text-white/75 leading-relaxed">
                                কুরআন, হাদিস, আরবি ব্যাকরণ কিংবা দ্বীনি কোনো বিষয় নিয়ে সহপাঠী ও শিক্ষকগণের সাথে ভাববিনিময় করুন।
                            </p>
                            <Link
                                href="/community/create"
                                className="mt-5 block w-full rounded-xl bg-[#FFF99A] py-3 text-center text-xs font-bold text-[#102526] shadow transition hover:bg-[#fff780]"
                            >
                                প্রশ্ন বা পোস্ট লিখুন
                            </Link>
                        </div>

                        {/* Community Guidelines */}
                        <div className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h4 className="font-bold text-sm text-[#102526] flex items-center gap-2">
                                <svg className="h-4 w-4 text-emerald-800" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                </svg>
                                ফোরাম ব্যবহারের নীতিমালা
                            </h4>
                            <ul className="mt-3 space-y-2 text-xs text-gray-600 leading-relaxed">
                                <li>• পারস্পরিক শ্রদ্ধা ও শালীন ভাষা বজায় রাখুন।</li>
                                <li>• বিতর্ক বা অহংকার পরিহার করে কেবল ইলম অর্জনের নিয়তে লিখুন।</li>
                                <li>• দলীলবিহীন ব্যক্তিগত মতামতকে শরীয়তের হুকুম হিসেবে প্রচার করবেন না।</li>
                                <li>• কোনো বিষয়ে চূড়ান্ত ফতোয়ার জন্য আমাদের <Link href="/fatawa" className="font-bold text-emerald-800 underline">ফাতাওয়া বিভাগে</Link> যান।</li>
                            </ul>
                        </div>
                    </aside>
                </div>
            </div>
        </MainLayout>
    );
}
