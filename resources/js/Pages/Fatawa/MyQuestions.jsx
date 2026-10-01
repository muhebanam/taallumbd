import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

export default function MyQuestions({ questions }) {
    const getStatusBadge = (status) => {
        switch (status) {
            case 'published':
                return <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">প্রকাশিত</span>;
            case 'answered':
                return <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">উত্তর প্রদানকৃত</span>;
            case 'rejected':
                return <span className="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">অননুমোদিত</span>;
            default:
                return <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">পর্যালোচনাধীন (Pending)</span>;
        }
    };

    return (
        <AppLayout>
            <Head title="আমার প্রশ্নসমূহ — আত-তাআল্লুম" />

            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
                    <div>
                        <h1 className="text-2xl font-bold text-[#102526]">আমার দ্বীনি প্রশ্নসমূহ</h1>
                        <p className="mt-1 text-xs text-gray-500">
                            মুফতী সাহেবের কাছে পাঠানো আপনার সকল প্রশ্ন ও উত্তরের বর্তমান অবস্থা
                        </p>
                    </div>

                    <Link
                        href="/fatawa/ask"
                        className="mt-4 sm:mt-0 inline-flex items-center gap-1.5 rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                    >
                        + নতুন প্রশ্ন করুন
                    </Link>
                </div>

                {questions.data && questions.data.length > 0 ? (
                    <div className="mt-6 space-y-4">
                        {questions.data.map((q) => (
                            <div
                                key={q.id}
                                className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-[#102526]/30"
                            >
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex items-center gap-2">
                                        {getStatusBadge(q.status)}
                                        {q.category && (
                                            <span className="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">
                                                {q.category.name}
                                            </span>
                                        )}
                                        {q.is_private && (
                                            <span className="rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700">
                                                ব্যক্তিগত
                                            </span>
                                        )}
                                    </div>
                                    <span className="text-xs text-gray-400">
                                        জমা দেওয়া হয়েছে: {new Date(q.created_at).toLocaleDateString('bn-BD')}
                                    </span>
                                </div>

                                <h3 className="mt-3 text-base font-bold text-[#102526]">
                                    {q.question_title}
                                </h3>

                                <p className="mt-1.5 text-xs text-gray-600 line-clamp-2">
                                    {q.question_body}
                                </p>

                                {q.answer_body ? (
                                    <div className="mt-4 rounded-xl bg-gray-50 border-l-4 border-emerald-700 p-4">
                                        <div className="flex items-center justify-between text-xs font-bold text-emerald-900 mb-1">
                                            <span>উত্তর প্রদান করা হয়েছে:</span>
                                            {q.answered_at && (
                                                <span className="font-normal text-gray-400">
                                                    {new Date(q.answered_at).toLocaleDateString('bn-BD')}
                                                </span>
                                            )}
                                        </div>
                                        <p className="text-xs text-gray-800 leading-relaxed whitespace-pre-line">
                                            {q.answer_body}
                                        </p>
                                    </div>
                                ) : (
                                    <div className="mt-4 text-xs text-amber-700 bg-amber-50 rounded-xl p-3">
                                        বিজ্ঞ মুফতী সাহেব প্রশ্নটি পর্যালোচনা করছেন। উত্তর প্রস্তুত হলে আপনাকে অবগত করা হবে।
                                    </div>
                                )}

                                {q.status === 'published' && !q.is_private && (
                                    <div className="mt-3 flex justify-end">
                                        <Link
                                            href={`/fatawa/${q.id}`}
                                            className="text-xs font-bold text-emerald-800 hover:underline"
                                        >
                                            উন্মুক্ত পাতায় দেখুন &rarr;
                                        </Link>
                                    </div>
                                )}
                            </div>
                        ))}

                        <Pagination links={questions.links} />
                    </div>
                ) : (
                    <div className="mt-12 rounded-3xl border border-dashed border-gray-300 p-12 text-center">
                        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 className="mt-4 text-base font-bold text-gray-900">আপনি এখনো কোনো প্রশ্ন করেননি</h3>
                        <p className="mt-1 text-xs text-gray-500">
                            দ্বীনি জীবনের যেকোনো জটিল বিষয়ে মুফতী সাহেবের সরাসরি সমাধান পেতে প্রশ্ন জমা দিন।
                        </p>
                        <div className="mt-6">
                            <Link
                                href="/fatawa/ask"
                                className="rounded-xl bg-[#102526] px-5 py-2.5 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                            >
                                প্রশ্ন পাঠান
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
