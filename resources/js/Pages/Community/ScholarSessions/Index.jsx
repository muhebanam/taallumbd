import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import Pagination from '../../../Components/Pagination';

export default function ScholarSessionsIndex({
    sessions,
    registeredSessionIds = [],
    filters = {},
}) {
    const handleFilterChange = (key, value) => {
        router.get('/scholar-sessions', {
            ...filters,
            [key]: value || undefined,
        }, { preserveState: true });
    };

    return (
        <MainLayout>
            <Head title="বিজ্ঞ স্কলার সেশন ও লাইভ হালাকা — আত-তাআল্লুম" />

            {/* Hero */}
            <div className="relative overflow-hidden bg-gradient-to-b from-[#102526] via-[#1A2E2F] to-[#102526] py-14 text-white">
                <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px]"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-[#FFF99A]/30 bg-[#FFF99A]/10 px-4 py-1.5 text-xs font-semibold text-[#FFF99A]">
                        <span className="h-2 w-2 rounded-full bg-[#FFF99A] animate-pulse"></span>
                        সরাসরি বিজ্ঞ আলেমদের তত্ত্বাবধানে
                    </span>
                    <h1 className="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl text-white">
                        লাইভ স্কলার সেশন ও কনসালটেশন
                    </h1>
                    <p className="mx-auto mt-3 max-w-2xl text-base text-[#F8FAF8]/80 sm:text-lg">
                        উস্তায ও মুফতিদের সাথে সরাসরি ইন্টার‍্যাক্টিভ লাইভ ক্লাস, দ্বীনি ওয়েবিনার ও ব্যক্তিগত দিকনির্দেশনামূলক সেশন
                    </p>

                    <div className="mt-6 flex justify-center gap-4">
                        <Link
                            href="/community/groups"
                            className="rounded-xl border border-white/20 bg-white/10 px-5 py-2.5 text-xs font-semibold text-white backdrop-blur hover:bg-white/20"
                        >
                            ← স্টাডি গ্রুপসমূহে ফিরে যান
                        </Link>
                    </div>
                </div>
            </div>

            {/* Filter Tabs */}
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="flex flex-col sm:flex-row items-center justify-between border-b border-gray-200 pb-4 gap-4 mb-8">
                    <div className="flex gap-4">
                        <button
                            onClick={() => handleFilterChange('status', 'scheduled')}
                            className={`pb-2 text-sm font-semibold border-b-2 transition ${
                                filters.status !== 'completed'
                                    ? 'border-[#102526] text-[#102526]'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            }`}
                        >
                            আসন্ন লাইভ সেশনসমূহ
                        </button>
                        <button
                            onClick={() => handleFilterChange('status', 'completed')}
                            className={`pb-2 text-sm font-semibold border-b-2 transition ${
                                filters.status === 'completed'
                                    ? 'border-[#102526] text-[#102526]'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            }`}
                        >
                            রেকর্ডিং আর্কাইভ
                        </button>
                    </div>

                    <div className="flex items-center gap-2">
                        <select
                            value={filters.type || ''}
                            onChange={(e) => handleFilterChange('type', e.target.value)}
                            className="rounded-xl border-gray-200 text-xs py-1.5 focus:border-[#102526] focus:ring-[#102526]"
                        >
                            <option value="">সকল ধরন</option>
                            <option value="webinar">উন্মুক্ত ওয়েবিনার</option>
                            <option value="live_class">কোর্স লাইভ ক্লাস</option>
                            <option value="consultation">ব্যক্তিগত কনসালটেশন</option>
                        </select>
                    </div>
                </div>

                {/* Sessions Grid */}
                {sessions.data && sessions.data.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {sessions.data.map((session) => {
                            const isRegistered = registeredSessionIds.includes(session.id);
                            return (
                                <div
                                    key={session.id}
                                    className="flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition"
                                >
                                    <div>
                                        <div className="flex items-center justify-between mb-3">
                                            <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                                session.session_type === 'consultation'
                                                    ? 'bg-purple-100 text-purple-800'
                                                    : session.session_type === 'webinar'
                                                    ? 'bg-amber-100 text-amber-800'
                                                    : 'bg-blue-100 text-blue-800'
                                            }`}>
                                                {session.session_type === 'consultation' ? 'কনসালটেশন' : session.session_type === 'webinar' ? 'ওয়েবিনার' : 'লাইভ ক্লাস'}
                                            </span>
                                            <span className="text-xs font-bold text-emerald-800">
                                                {session.fee > 0 ? `৳ ${session.fee}` : 'ফ্রি সেশন'}
                                            </span>
                                        </div>

                                        <h3 className="text-lg font-bold text-gray-900 line-clamp-2 hover:text-[#102526]">
                                            <Link href={`/scholar-sessions/${session.id}`}>
                                                {session.title}
                                            </Link>
                                        </h3>

                                        <p className="mt-2 text-xs text-gray-600 line-clamp-2">
                                            {session.description || 'বিজ্ঞ আলেমের সান্নিধ্যে সরাসরি ইন্টার‍্যাক্টিভ আলোচনা।'}
                                        </p>

                                        {/* Date and Platform */}
                                        <div className="mt-4 rounded-xl bg-gray-50 p-3 text-xs space-y-1.5 border border-gray-100">
                                            <div className="flex items-center gap-1.5 text-gray-700">
                                                <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>সময়: {new Date(session.start_time).toLocaleString('bn-BD')}</span>
                                            </div>
                                            <div className="flex items-center gap-1.5 text-gray-700">
                                                <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>দৈর্ঘ্য: {session.duration || 60} মিনিট ({session.platform?.toUpperCase()})</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                                        <div className="text-xs text-gray-500">
                                            উস্তায: <strong className="text-gray-900">{session.instructor?.name || 'বিজ্ঞ স্কলার'}</strong>
                                        </div>
                                        <Link
                                            href={`/scholar-sessions/${session.id}`}
                                            className={`rounded-xl px-4 py-2 text-xs font-bold transition ${
                                                isRegistered
                                                    ? 'bg-emerald-100 text-emerald-800'
                                                    : 'bg-[#102526] text-white hover:bg-[#1A2E2F]'
                                            }`}
                                        >
                                            {isRegistered ? 'নিবন্ধিত ✓' : 'বিস্তারিত ও বুকিং →'}
                                        </Link>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-gray-300 p-12 text-center bg-gray-50">
                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <h3 className="mt-4 text-base font-semibold text-gray-900">এই মুহূর্তে কোনো সেশন পাওয়া যায়নি</h3>
                        <p className="mt-1 text-sm text-gray-500">নতুন স্কলার সেশন শিডিউল হওয়া মাত্রই এখানে দৃশ্যমান হবে।</p>
                    </div>
                )}

                {sessions.links && (
                    <div className="mt-8">
                        <Pagination links={sessions.links} />
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
