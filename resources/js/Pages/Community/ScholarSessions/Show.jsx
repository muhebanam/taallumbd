import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function ScholarSessionShow({
    session,
    isRegistered = false,
    canAccessMeeting = false,
    registrationsCount = 0,
}) {
    const { auth } = usePage().props;
    const [notes, setNotes] = useState('');
    const [registering, setRegistering] = useState(false);

    const handleRegister = (e) => {
        e.preventDefault();
        if (!auth.user) {
            router.get('/login');
            return;
        }

        setRegistering(true);
        router.post(`/scholar-sessions/${session.id}/register`, { notes }, {
            onFinish: () => setRegistering(false),
        });
    };

    const handleCheckIn = () => {
        router.post(`/scholar-sessions/${session.id}/check-in`, {}, {
            preserveScroll: true,
        });
    };

    return (
        <MainLayout>
            <Head title={`${session.title} — স্কলার সেশন`} />

            {/* Header */}
            <div className="bg-[#102526] py-12 text-white">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <Link
                        href="/scholar-sessions"
                        className="inline-flex items-center gap-1.5 text-xs text-[#FFF99A] hover:underline"
                    >
                        ← সকল স্কলার সেশন
                    </Link>

                    <div className="mt-4 flex flex-wrap items-center gap-2">
                        <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                            {session.session_type === 'consultation' ? 'ব্যক্তিগত কনসালটেশন' : session.session_type === 'webinar' ? 'উন্মুক্ত ওয়েবিনার' : 'লাইভ ক্লাস'}
                        </span>
                        <span className="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-[#FFF99A]">
                            {session.fee > 0 ? `ফি: ৳ ${session.fee}` : 'ফ্রি সেশন'}
                        </span>
                        {session.status === 'live' && (
                            <span className="animate-pulse rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                • লাইভ চলছে
                            </span>
                        )}
                    </div>

                    <h1 className="mt-4 text-2xl font-extrabold sm:text-4xl text-white">
                        {session.title}
                    </h1>

                    <div className="mt-4 flex items-center gap-3">
                        <div className="h-10 w-10 rounded-full bg-[#1A2E2F] text-[#FFF99A] border border-[#335558] flex items-center justify-center font-bold">
                            {session.instructor?.name ? session.instructor.name.charAt(0) : 'S'}
                        </div>
                        <div>
                            <div className="font-bold text-white">{session.instructor?.name || 'বিজ্ঞ স্কলার'}</div>
                            <div className="text-xs text-white/60">আত-তাআল্লুম স্বীকৃত উস্তায</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Content Body */}
            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left: Details (2 cols) */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 className="text-base font-bold text-gray-900 mb-3">সেশনের বিষয়বস্তু ও বিবরণ</h2>
                            <p className="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                {session.description || 'এই সেশনে নির্দিষ্ট দ্বীনি বিষয়ে সরাসরি পাঠদান ও প্রশ্নোত্তর পর্ব অনুষ্ঠিত হবে।'}
                            </p>

                            {/* Meeting Access Section */}
                            {canAccessMeeting ? (
                                <div className="mt-6 rounded-xl bg-emerald-50 p-5 border border-emerald-200">
                                    <div className="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                                        <svg className="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        লাইভ মিটিং লিংক প্রস্তুত
                                    </div>
                                    <p className="mt-1 text-xs text-emerald-800">
                                        আপনি সফলভাবে নিবন্ধিত আছেন। সেশনে যুক্ত হতে নিচের লিংকে ক্লিক করুন:
                                    </p>
                                    <div className="mt-4 flex flex-wrap gap-3">
                                        {session.meeting_url && (
                                            <a
                                                href={session.meeting_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-emerald-800 transition"
                                            >
                                                সেশনে প্রবেশ করুন ({session.platform?.toUpperCase()}) ↗
                                            </a>
                                        )}
                                        <button
                                            onClick={handleCheckIn}
                                            className="rounded-xl border border-emerald-700 bg-white px-4 py-2.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-50"
                                        >
                                            উপস্থিতি নিশ্চিত করুন
                                        </button>
                                    </div>
                                </div>
                            ) : null}

                            {/* Recording Archive if Completed */}
                            {session.recording_url && (
                                <div className="mt-6 rounded-xl bg-blue-50 p-5 border border-blue-200">
                                    <div className="font-bold text-sm text-blue-900">
                                        📹 সেশনের রেকর্ডিং আর্কাইভ
                                    </div>
                                    <p className="mt-1 text-xs text-blue-700">
                                        সেশনটি সম্পন্ন হয়েছে। নিচের ভিডিও আর্কাইভে ক্লিক করে পূর্ণ আলোচনা দেখুন:
                                    </p>
                                    <div className="mt-3">
                                        <a
                                            href={session.recording_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-blue-700 px-4 py-2 text-xs font-bold text-white hover:bg-blue-800"
                                        >
                                            রেকর্ডিং দেখুন ↗
                                        </a>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: Booking / Action Sidebar (1 col) */}
                    <div className="space-y-6">
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="text-sm font-bold text-gray-900 mb-4">সময়সূচী ও তথ্য</h3>

                            <div className="space-y-3 text-xs text-gray-600">
                                <div className="flex justify-between py-1 border-b border-gray-100">
                                    <span>তারিখ ও সময়:</span>
                                    <span className="font-semibold text-gray-900">{new Date(session.start_time).toLocaleString('bn-BD')}</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-gray-100">
                                    <span>সেশনের দৈর্ঘ্য:</span>
                                    <span className="font-semibold text-gray-900">{session.duration} মিনিট</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-gray-100">
                                    <span>প্ল্যাটফর্ম:</span>
                                    <span className="font-semibold text-gray-900">{session.platform?.toUpperCase()}</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-gray-100">
                                    <span>নিবন্ধিত শিক্ষার্থী:</span>
                                    <span className="font-semibold text-gray-900">{registrationsCount} জন</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-gray-100">
                                    <span>ফি:</span>
                                    <span className="font-bold text-emerald-800">
                                        {session.fee > 0 ? `৳ ${session.fee}` : 'সম্পূর্ণ ফ্রি'}
                                    </span>
                                </div>
                            </div>

                            {/* Registration Button */}
                            <div className="mt-6 pt-4 border-t border-gray-100">
                                {isRegistered ? (
                                    <div className="rounded-xl bg-emerald-100 p-3 text-center text-xs font-bold text-emerald-900">
                                        ✓ আপনি এই সেশনে নিবন্ধিত আছেন
                                    </div>
                                ) : (
                                    <form onSubmit={handleRegister} className="space-y-3">
                                        <textarea
                                            rows={2}
                                            value={notes}
                                            onChange={(e) => setNotes(e.target.value)}
                                            placeholder="উস্তাযের উদ্দেশ্যে কোনো বিশেষ প্রশ্ন বা নোট (ঐচ্ছিক)..."
                                            className="w-full rounded-xl border-gray-200 text-xs focus:border-[#102526] focus:ring-[#102526]"
                                        />
                                        <button
                                            type="submit"
                                            disabled={registering}
                                            className="w-full rounded-xl bg-[#102526] py-3 text-sm font-bold text-white shadow hover:bg-[#1A2E2F] transition disabled:opacity-50"
                                        >
                                            {registering
                                                ? 'প্রক্রিয়াকরণ হচ্ছে...'
                                                : session.fee > 0
                                                ? `পেমেন্ট ও বুকিং সম্পন্ন করুন (৳ ${session.fee})`
                                                : 'এখনই ফ্রি নিবন্ধন করুন'}
                                        </button>
                                    </form>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
