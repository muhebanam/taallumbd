import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function Verify({ certificate, identifier, isValid, isRevoked, revokedReason, revokedAt }) {
    return (
        <MainLayout>
            <Head>
                <title>সনদপত্র যাচাইকরণ — আত-তাআল্লুম</title>
                <meta name="description" content="আত-তাআল্লুম ডিজিটাল একাডেমি কর্তৃক প্রদত্ত সনদপত্র যাচাই ও নিশ্চিতকরণ।" />
            </Head>

            <div className="py-16 sm:py-24 bg-[#F8FAF8]">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    {/* Revoked State */}
                    {isRevoked && certificate ? (
                        <div className="overflow-hidden rounded-3xl border border-rose-300 bg-white shadow-xl">
                            <div className="h-2 w-full bg-rose-600"></div>
                            <div className="bg-rose-950 px-8 py-10 text-center text-white relative">
                                <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/20 border border-rose-400/40 text-3xl text-rose-300">
                                    ⚠️
                                </div>
                                <span className="mt-4 inline-block rounded-full bg-rose-500/20 border border-rose-400/40 px-4 py-1 text-xs font-bold text-rose-300">
                                    বাতিলকৃত / প্রত্যাহারকৃত সনদপত্র (REVOKED)
                                </span>
                                <h1 className="mt-3 text-2xl sm:text-3xl font-extrabold text-white font-bangla">
                                    এই সনদপত্রটি প্রত্যাহার করা হয়েছে
                                </h1>
                                <p className="mt-1 text-xs sm:text-sm text-rose-200">
                                    This certificate has been revoked by Taallum Academic Authority
                                </p>
                            </div>

                            <div className="p-8 sm:p-10 space-y-6">
                                <div className="rounded-2xl bg-rose-50 border border-rose-200 p-5 text-sm text-rose-900">
                                    <h4 className="font-bold text-rose-950 font-bangla text-base mb-1">
                                        প্রত্যাহারের কারণ ও বিবরণ:
                                    </h4>
                                    <p className="text-rose-800 leading-relaxed">
                                        {revokedReason || 'একাডেমিক নীতিমালা বা যাচাই ব্যত্যয়ের কারণে এই সনদটি বাতিল করা হয়েছে।'}
                                    </p>
                                    <div className="mt-3 pt-3 border-t border-rose-200/60 text-xs text-rose-700 flex flex-wrap gap-4">
                                        <span><strong>প্রত্যাহারের তারিখ:</strong> {revokedAt || '—'}</span>
                                        <span><strong>সনদ নম্বর:</strong> {certificate.certificate_no}</span>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-600">
                                    <div>
                                        <span className="text-xs text-slate-400 block uppercase">মূল সনদ প্রাপক</span>
                                        <p className="font-bold text-slate-800 mt-1 font-bangla">{certificate.user?.name}</p>
                                    </div>
                                    <div>
                                        <span className="text-xs text-slate-400 block uppercase">কোর্স</span>
                                        <p className="font-bold text-slate-800 mt-1 font-bangla">{certificate.course?.title}</p>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-slate-100 flex justify-center">
                                    <Link href="/" className="btn-secondary text-xs">
                                        প্রধান পাতায় ফিরে যান
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ) : isValid && certificate ? (
                        /* Valid Certificate State */
                        <div className="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xl">
                            {/* Top Islamic Accent Bar */}
                            <div className="h-2 w-full bg-gradient-to-r from-[#1A2E2F] via-[#FFF99A] to-[#1A2E2F]"></div>

                            {/* Header */}
                            <div className="bg-[#102526] px-8 py-10 text-center text-white relative overflow-hidden">
                                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
                                
                                <div className="relative z-10 flex flex-col items-center">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-3xl">
                                        ✓
                                    </div>

                                    <div className="mt-4 flex flex-wrap justify-center gap-2">
                                        <span className="rounded-full bg-emerald-500/20 border border-emerald-400/30 px-3.5 py-1 text-xs font-bold text-emerald-300">
                                            সত্যায়িত ও বৈধ সনদপত্র
                                        </span>
                                        {certificate.course?.is_certified && (
                                            <span className="rounded-full bg-[#FFF99A]/20 border border-[#FFF99A]/40 px-3.5 py-1 text-xs font-bold text-[#FFF99A] flex items-center gap-1">
                                                <span>★</span> স্কলার সার্টিফাইড কোর্স
                                            </span>
                                        )}
                                    </div>

                                    <h1 className="mt-3 text-2xl sm:text-3xl font-extrabold text-[#FFF99A] font-bangla">
                                        আত-তাআল্লুম একাডেমিক সনদ যাচাই
                                    </h1>
                                    <p className="mt-1 text-xs sm:text-sm text-slate-300">
                                        TaallumBD Digital Islamic Academy Certificate Verification
                                    </p>
                                </div>
                            </div>

                            {/* Certificate Details Card */}
                            <div className="p-8 sm:p-10 space-y-6">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-slate-100 pb-6">
                                    <div>
                                        <span className="text-xs font-semibold text-slate-400 block uppercase">সনদ প্রাপকের নাম</span>
                                        <h3 className="text-xl font-bold text-[#142425] mt-1 font-bangla">
                                            {certificate.user?.name}
                                        </h3>
                                    </div>

                                    <div>
                                        <span className="text-xs font-semibold text-slate-400 block uppercase">সনদপত্র নম্বর</span>
                                        <p className="text-base font-mono font-bold text-[#1A2E2F] mt-1">
                                            {certificate.certificate_no}
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-4 border-b border-slate-100 pb-6">
                                    <div>
                                        <span className="text-xs font-semibold text-slate-400 block uppercase">সম্পন্নকৃত কোর্স</span>
                                        <h4 className="text-lg font-bold text-emerald-900 mt-1 font-bangla">
                                            {certificate.course?.title}
                                        </h4>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span className="text-xs text-slate-500 block">কোর্স ইন্সট্রাক্টর/উস্তায:</span>
                                            <span className="font-semibold text-slate-800">
                                                {certificate.course?.instructor?.name || 'আত-তাআল্লুম উস্তায পরিষদ'}
                                            </span>
                                        </div>

                                        <div>
                                            <span className="text-xs text-slate-500 block">সনদ ইস্যুর তারিখ:</span>
                                            <span className="font-semibold text-slate-800">
                                                {certificate.issued_at ? new Date(certificate.issued_at).toLocaleDateString('bn-BD', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                }) : '—'}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Scholar Certification Note if applicable */}
                                    {certificate.course?.is_certified && certificate.course?.certified_by_scholar && (
                                        <div className="mt-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2.5">
                                            <span className="text-base leading-none">📜</span>
                                            <div>
                                                <span className="font-bold">স্কলার অনুমোদন:</span> এই কোর্সটি বিশিষ্ট স্কলার{' '}
                                                <strong>{certificate.course.certified_by_scholar.name}</strong> ({certificate.course.certified_by_scholar.designation || 'সিনিয়র স্কলার'}) কর্তৃক পর্যালোচনা ও শরিয়াহ মূল্যায়নে অনুমোদিত।
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* QR Code & Verification Proof */}
                                <div className="flex flex-col sm:flex-row items-center justify-between gap-6 pt-2">
                                    <div className="flex items-center gap-4">
                                        <img
                                            src={certificate.qr_code_url}
                                            alt="সনদপত্র কিউআর কোড"
                                            className="h-28 w-28 rounded-xl border border-slate-200 p-1 bg-white shadow-sm"
                                        />
                                        <div className="text-xs text-slate-600">
                                            <p className="font-bold text-slate-800">ডিজিটাল ভেরিফিকেশন কোড</p>
                                            <p className="mt-1 font-mono text-[11px] text-slate-500 break-all max-w-[200px]">
                                                {certificate.uuid}
                                            </p>
                                            <p className="mt-2 text-emerald-700 font-semibold">
                                                ✓ কেন্দ্রীয় সার্ভারে রেকর্ডভুক্ত ও সংরক্ষিত
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-2 w-full sm:w-auto">
                                        <Link
                                            href={`/courses/${certificate.course?.slug}`}
                                            className="btn-primary text-xs !py-2.5 text-center"
                                        >
                                            কোর্সের বিস্তারিত দেখুন
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            {/* Footer Notice */}
                            <div className="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center text-xs text-slate-500">
                                এই সনদটি আত-তাআল্লুম ডিজিটাল একাডেমি কর্তৃক শতভাগ পাঠ ও মূল্যায়নে উত্তীর্ণ হওয়ার পর প্রদান করা হয়েছে।
                            </div>
                        </div>
                    ) : (
                        /* Invalid Certificate State */
                        <div className="overflow-hidden rounded-3xl border border-rose-200 bg-white p-10 text-center shadow-lg">
                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-2xl text-rose-600">
                                ✕
                            </div>

                            <h2 className="mt-4 text-2xl font-bold text-slate-900 font-bangla">
                                সনদপত্রটি খুঁজে পাওয়া যায়নি
                            </h2>

                            <p className="mt-2 text-sm text-slate-600 max-w-md mx-auto">
                                আপনার প্রদত্ত কোড (<span className="font-mono font-semibold">{identifier}</span>) অনুযায়ী আত-তাআল্লুম ডাটাবেজে কোনো কার্যকর সনদ পাওয়া যায়নি।
                            </p>

                            <div className="mt-8 flex justify-center gap-4">
                                <Link href="/" className="btn-secondary text-xs">
                                    হোমে ফিরে যান
                                </Link>
                                <Link href="/contact" className="btn-primary text-xs">
                                    যোগাযোগ করুন
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}
