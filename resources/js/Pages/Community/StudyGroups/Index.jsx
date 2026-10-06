import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import Pagination from '../../../Components/Pagination';

export default function StudyGroupsIndex({ groups, tab = 'explore' }) {
    const { auth } = usePage().props;

    const handleTabChange = (selectedTab) => {
        router.get('/community/groups', { tab: selectedTab }, { preserveState: true });
    };

    const handleJoin = (groupSlug, e) => {
        e.preventDefault();
        if (!auth.user) {
            router.get('/login');
            return;
        }
        router.post(`/community/groups/${groupSlug}/join`, {}, { preserveScroll: true });
    };

    return (
        <MainLayout>
            <Head title="ইসলামিক স্টাডি গ্রুপ ও দ্বীনি হালাকা — আত-তাআল্লুম" />

            {/* Header */}
            <div className="relative overflow-hidden bg-gradient-to-b from-[#102526] via-[#1A2E2F] to-[#102526] py-14 text-white">
                <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px]"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/30 bg-[#FFF99A]/10 px-4 py-1.5 text-xs font-semibold text-[#FFF99A]">
                        <span className="h-2 w-2 rounded-full bg-[#FFF99A] animate-pulse"></span>
                        ইলমী হালাকা ও দলগত পাঠচর্চা
                    </span>
                    <h1 className="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl text-white">
                        ইসলামিক স্টাডি গ্রুপসমূহ
                    </h1>
                    <p className="mx-auto mt-3 max-w-2xl text-base text-[#F8FAF8]/80 sm:text-lg">
                        সহপাঠী ও গবেষকদের সাথে নির্দিষ্ট দ্বীনি বিষয়ে নিয়মিত মুযাকারা, সাপ্তাহিক লক্ষ্য অর্জন ও যৌথ অধ্যয়ন
                    </p>

                    <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <Link
                            href="/community/groups/create"
                            className="inline-flex items-center gap-2 rounded-xl bg-[#FFF99A] px-6 py-3 text-sm font-bold text-[#102526] shadow-lg transition hover:bg-[#fff780]"
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            নতুন গ্রুপ তৈরি করুন
                        </Link>
                        <Link
                            href="/scholar-sessions"
                            className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20"
                        >
                            লাইভ স্কলার সেশনসমূহ দেখুন →
                        </Link>
                    </div>
                </div>
            </div>

            {/* Navigation Tabs */}
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="flex border-b border-gray-200 gap-4 mb-8">
                    <button
                        onClick={() => handleTabChange('explore')}
                        className={`pb-3 text-sm font-medium border-b-2 transition ${
                            tab === 'explore'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        উন্মুক্ত হালাকা (Explore)
                    </button>
                    <button
                        onClick={() => handleTabChange('my_groups')}
                        className={`pb-3 text-sm font-medium border-b-2 transition ${
                            tab === 'my_groups'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        আমার গ্রুপসমূহ (My Groups)
                    </button>
                    <button
                        onClick={() => handleTabChange('course_linked')}
                        className={`pb-3 text-sm font-medium border-b-2 transition ${
                            tab === 'course_linked'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        কোর্স-সংযুক্ত গ্রুপ (Course Linked)
                    </button>
                </div>

                {/* Groups Grid */}
                {groups.data && groups.data.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {groups.data.map((group) => (
                            <div
                                key={group.id}
                                className="flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition"
                            >
                                <div>
                                    <div className="flex items-center justify-between mb-3">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            group.type === 'public'
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : group.type === 'private'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-indigo-100 text-indigo-800'
                                        }`}>
                                            {group.type === 'public' ? 'উন্মুক্ত গ্রুপ' : group.type === 'private' ? 'প্রাইভেট হালাকা' : 'কোর্স লিংকড'}
                                        </span>
                                        <span className="text-xs text-gray-500">
                                            {group.members_count} / {group.max_members} সদস্য
                                        </span>
                                    </div>

                                    <h3 className="text-lg font-bold text-gray-900 line-clamp-1 hover:text-[#102526]">
                                        <Link href={`/community/groups/${group.slug}`}>
                                            {group.name}
                                        </Link>
                                    </h3>
                                    <p className="mt-2 text-sm text-gray-600 line-clamp-2">
                                        {group.description || 'সহপাঠীদের সাথে ইলমী মোযাকারা ও নিয়মিত দ্বীনি আলোচনা।'}
                                    </p>

                                    {/* Weekly Goal Snippet */}
                                    {group.weekly_goal && (
                                        <div className="mt-4 rounded-xl bg-gray-50 p-3 border border-gray-100">
                                            <div className="flex items-center gap-1.5 text-xs font-semibold text-emerald-800">
                                                <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                সাপ্তাহিক লক্ষ্য:
                                            </div>
                                            <p className="mt-1 text-xs text-gray-700 line-clamp-1">
                                                {group.weekly_goal}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                <div className="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div className="flex items-center gap-2 text-xs text-gray-500">
                                        <span>পরিচালক: {group.creator?.name || 'অজ্ঞাত'}</span>
                                    </div>
                                    <Link
                                        href={`/community/groups/${group.slug}`}
                                        className="inline-flex items-center gap-1 text-xs font-bold text-[#102526] hover:underline"
                                    >
                                        প্রবেশ করুন →
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-gray-300 p-12 text-center bg-gray-50">
                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 className="mt-4 text-base font-semibold text-gray-900">কোনো স্টাডি গ্রুপ পাওয়া যায়নি</h3>
                        <p className="mt-1 text-sm text-gray-500">প্রথম স্টাডি গ্রুপ তৈরি করে সহপাঠীদের সাথে দ্বীনি পাঠ শুরু করুন।</p>
                        <div className="mt-6">
                            <Link
                                href="/community/groups/create"
                                className="inline-flex items-center rounded-xl bg-[#102526] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#1A2E2F]"
                            >
                                নতুন গ্রুপ তৈরি করুন
                            </Link>
                        </div>
                    </div>
                )}

                {/* Pagination */}
                {groups.links && (
                    <div className="mt-8">
                        <Pagination links={groups.links} />
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
