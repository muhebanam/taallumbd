import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function FatwaShow({ fatwa, relatedFatawa = [] }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = () => {
        navigator.clipboard.writeText(window.location.href);
        setCopied(true);
        setTimeout(() => setCopied(false), 2500);
    };

    const handlePrint = () => {
        window.print();
    };

    const answeringScholar = fatwa.assigned_scholar || fatwa.teacher;
    const scholarUser = answeringScholar?.user || fatwa.mufti;

    return (
        <MainLayout>
            <Head title={`${fatwa.question_title} — ফাতাওয়া ও শরয়ী সমাধান | আত-তাআল্লুম`} />

            {/* Breadcrumb Header */}
            <div className="border-b border-gray-200 bg-gray-50/80 py-4 print:hidden">
                <div className="mx-auto flex max-w-5xl items-center gap-2 px-4 text-xs text-gray-500 sm:px-6">
                    <Link href="/" className="hover:text-gray-800">হোম</Link>
                    <span>/</span>
                    <Link href="/fatawa" className="hover:text-gray-800">ফাতাওয়া সম্ভার</Link>
                    {fatwa.category && (
                        <>
                            <span>/</span>
                            <Link href={`/fatawa/category/${fatwa.category.slug}`} className="font-medium text-[#102526] hover:underline">
                                {fatwa.category.name}
                            </Link>
                        </>
                    )}
                </div>
            </div>

            <article className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Fatwa Header Card */}
                <header className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
                        <div className="flex items-center gap-2">
                            {fatwa.category && (
                                <Link
                                    href={`/fatawa/category/${fatwa.category.slug}`}
                                    className="rounded-full bg-emerald-50 px-3.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition"
                                >
                                    {fatwa.category.name}
                                </Link>
                            )}
                            <span className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                                ফাতাওয়া নং: #{fatwa.id}
                            </span>
                        </div>

                        {/* Actions (Share / Print) */}
                        <div className="flex items-center gap-2 print:hidden">
                            <button
                                onClick={handleCopy}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                title="লিংক কপি করুন"
                            >
                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                {copied ? 'কপি হয়েছে!' : 'শেয়ার করুন'}
                            </button>
                            <button
                                onClick={handlePrint}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                                title="প্রিন্ট করুন"
                            >
                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                প্রিন্ট
                            </button>
                        </div>
                    </div>

                    {/* Question Title */}
                    <h1 className="mt-4 text-2xl font-bold leading-snug text-[#102526] sm:text-3xl">
                        {fatwa.question_title}
                    </h1>

                    <div className="mt-4 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                        <span>
                            প্রকাশের তারিখ: {fatwa.published_at ? new Date(fatwa.published_at).toLocaleDateString('bn-BD', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            }) : 'অপেক্ষমান'}
                        </span>
                        {fatwa.views_count > 0 && (
                            <>
                                <span>•</span>
                                <span>পঠিত হয়েছে {fatwa.views_count} বার</span>
                            </>
                        )}
                        {fatwa.questioner_name && !fatwa.is_private && (
                            <>
                                <span>•</span>
                                <span>প্রশ্নকর্তা: {fatwa.questioner_name}</span>
                            </>
                        )}
                    </div>
                </header>

                {/* Question Details Section */}
                <section className="mt-6 rounded-3xl border border-amber-200 bg-amber-50/50 p-6 sm:p-8">
                    <div className="flex items-center gap-2 text-amber-900 font-bold text-sm">
                        <span className="flex h-6 w-6 items-center justify-center rounded-full bg-amber-200 text-amber-900 text-xs font-extrabold">
                            প্র
                        </span>
                        <span>প্রশ্ন:</span>
                    </div>
                    <div className="mt-3 text-base leading-relaxed text-gray-800 whitespace-pre-line font-medium">
                        {fatwa.question_body}
                    </div>
                </section>

                {/* Answer Section */}
                <section className="mt-6 rounded-3xl border-2 border-[#102526] bg-white p-6 sm:p-10 shadow-md">
                    {/* Bismillah Header */}
                    <div className="border-b border-gray-100 pb-6 text-center">
                        <p className="font-amiri text-2xl text-[#102526]" dir="rtl">
                            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                        </p>
                        <p className="mt-1 text-xs text-gray-400">
                            الْجَوَابُ بِعَوْنِ الْمَلِكِ الْوَهَّابِ (আল্লাহর তাওফীকে সমাধান)
                        </p>
                    </div>

                    <div className="mt-6 flex items-center gap-2 text-[#102526] font-bold text-base">
                        <span className="flex h-6 w-6 items-center justify-center rounded-full bg-[#102526] text-[#FFF99A] text-xs font-extrabold">
                            উ
                        </span>
                        <span>শরয়ী সমাধান ও উত্তর:</span>
                    </div>

                    {/* Answer Body */}
                    <div className="mt-4 text-base leading-relaxed text-gray-900 whitespace-pre-line space-y-4">
                        {fatwa.answer_body ? (
                            fatwa.answer_body
                        ) : (
                            <p className="text-gray-500 italic">এই প্রশ্নটির উত্তর এখনো প্রস্তুত হচ্ছে। ইনশাআল্লাহ শীঘ্রই বিজ্ঞ উলামায়ে কেরামের ফতোয়া এখানে যুক্ত হবে।</p>
                        )}
                    </div>

                    {/* Daleel & References Box */}
                    {fatwa.references && (
                        <div className="mt-8 rounded-2xl border border-emerald-900/15 bg-emerald-50/60 p-5">
                            <h4 className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-900">
                                <svg className="h-4 w-4 text-emerald-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                দলীল ও কিতাবের হাওয়ালা (হিদায়াহ, ফাতাওয়ায়ে আলমগীরী, বুখারী ও সুনান):
                            </h4>
                            <div className="mt-2.5 font-amiri text-sm leading-relaxed text-gray-800 whitespace-pre-line" dir="auto">
                                {fatwa.references}
                            </div>
                        </div>
                    )}

                    {/* Answering Mufti / Scholar Sign-off */}
                    <div className="mt-8 border-t border-gray-100 pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#102526] text-[#FFF99A] font-bold text-lg shadow-sm">
                                {scholarUser?.name ? scholarUser.name.charAt(0) : 'ম'}
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">ফাতাওয়া অনুমোদন ও সত্যায়নে:</p>
                                <p className="font-bold text-base text-[#102526]">
                                    {scholarUser?.name || 'মুফতী পরিষদ'}
                                </p>
                                {answeringScholar?.designation && (
                                    <p className="text-xs text-gray-600">{answeringScholar.designation}</p>
                                )}
                            </div>
                        </div>

                        {answeringScholar?.slug && (
                            <Link
                                href={`/teachers/${answeringScholar.slug}`}
                                className="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 hover:text-emerald-950 transition print:hidden"
                            >
                                শিক্ষকের প্রোফাইল দেখুন
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        )}
                    </div>
                </section>

                {/* Related Course Recommendation Card */}
                {fatwa.related_course && (
                    <section className="mt-8 overflow-hidden rounded-3xl border border-[#335558] bg-gradient-to-r from-[#102526] to-[#1A2E2F] p-6 text-white shadow-xl print:hidden">
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div className="space-y-2">
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-[#FFF99A]/20 px-3 py-1 text-xs font-bold text-[#FFF99A]">
                                    সম্পর্কিত একাডেমিক কোর্স
                                </span>
                                <h3 className="text-xl font-bold text-white">
                                    {fatwa.related_course.title}
                                </h3>
                                <p className="text-xs text-white/80 max-w-xl leading-relaxed">
                                    এই বিষয়ে মৌলিক ভিত্তি তৈরি এবং তাহকীকপূর্ণ ইলম অর্জনের জন্য আমাদের এই কোর্সটিতে অংশগ্রহণ করুন।
                                    {fatwa.related_course.instructor?.name && (
                                        <span className="block mt-1 text-[#FFF99A]">
                                            উস্তায: {fatwa.related_course.instructor.name}
                                        </span>
                                    )}
                                </p>
                            </div>

                            <div className="flex shrink-0 items-center gap-4">
                                <div className="text-right">
                                    <p className="text-xs text-white/60">কোর্স ফি</p>
                                    <p className="text-lg font-bold text-[#FFF99A]">
                                        {fatwa.related_course.price > 0 ? `৳${fatwa.related_course.price}` : 'ফ্রি'}
                                    </p>
                                </div>
                                <Link
                                    href={`/courses/${fatwa.related_course.slug}`}
                                    className="rounded-xl bg-[#FFF99A] px-5 py-3 text-sm font-bold text-[#102526] shadow transition hover:bg-[#fff780]"
                                >
                                    কোর্সটি দেখুন
                                </Link>
                            </div>
                        </div>
                    </section>
                )}

                {/* Related Fatawa Section */}
                {relatedFatawa.length > 0 && (
                    <section className="mt-12 print:hidden">
                        <h3 className="text-xl font-bold text-[#102526]">
                            এই বিষয়ের আরও কিছু ফাতাওয়া
                        </h3>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            {relatedFatawa.map((rf) => (
                                <Link
                                    key={rf.id}
                                    href={`/fatawa/${rf.id}`}
                                    className="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-[#102526]/30 hover:shadow-md"
                                >
                                    <div className="flex items-center justify-between text-xs text-gray-400">
                                        <span className="rounded-full bg-gray-100 px-2.5 py-0.5 font-medium text-gray-600">
                                            #{rf.id}
                                        </span>
                                        {rf.views_count > 0 && <span>{rf.views_count} ভিউ</span>}
                                    </div>
                                    <h4 className="mt-2 font-bold text-gray-900 group-hover:text-emerald-800 transition line-clamp-2">
                                        {rf.question_title}
                                    </h4>
                                    <span className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-emerald-800">
                                        সমাধান পড়ুন &rarr;
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                {/* Ask Another Question CTA */}
                <div className="mt-12 rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center print:hidden">
                    <h3 className="text-lg font-bold text-gray-900">আপনার কি এ বিষয়ে অন্য কোনো প্রশ্ন রয়েছে?</h3>
                    <p className="mt-1 text-sm text-gray-600">
                        আমাদের দারুল ইফতা ও অভিজ্ঞ উলামায়ে কেরামের কাছে যেকোনো মাসআলা সরাসরি লিখে পাঠাতে পারেন।
                    </p>
                    <Link
                        href="/fatawa/ask"
                        className="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#102526] px-6 py-3 text-sm font-bold text-[#FFF99A] shadow transition hover:bg-[#1A2E2F]"
                    >
                        নতুন প্রশ্ন পাঠান
                    </Link>
                </div>
            </article>
        </MainLayout>
    );
}
