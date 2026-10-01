import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function HadithIndex({ books = [] }) {
    return (
        <MainLayout>
            <Head>
                <title>হাদীস সংকলন ও পাঠশালা — আত-তাআল্লুম</title>
                <meta name="description" content="সিহাহ সিত্তাহ সহ বিশুদ্ধ হাদীস গ্রন্থসমূহের প্রামাণ্য আরবী ইবারত, বাংলা অনুবাদ ও তাহকীক।" />
            </Head>

            {/* Hadith Banner */}
            <div className="relative overflow-hidden bg-[#102526] text-white py-16 sm:py-20">
                <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px] opacity-10"></div>
                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                    <span className="inline-block rounded-full border border-[#FFF99A]/30 bg-white/5 px-4 py-1 text-xs font-semibold text-[#FFF99A]">
                        مَنْ يُرِدِ اللَّهُ بِهِ خَيْرًا يُفَقِّهْهُ فِي الدِّينِ
                    </span>
                    <h1 className="mt-4 text-3xl sm:text-5xl font-extrabold font-bangla tracking-tight">
                        হাদীস শরীফ লাইব্রেরি
                    </h1>
                    <p className="mt-3 text-base text-slate-300 font-bangla max-w-2xl mx-auto">
                        সিহাহ সিত্তাহ সহ বিশ্বস্ত প্রামাণ্য হাদীস গ্রন্থসমূহের বিশুদ্ধ আরবী পাঠ, নির্ভরযোগ্য বাংলা অনুবাদ ও মান বিশ্লেষণ।
                    </p>
                </div>
            </div>

            {/* Books Grid */}
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
                <div className="text-center max-w-2xl mx-auto mb-12">
                    <h2 className="text-2xl sm:text-3xl font-bold text-[#142425] font-bangla">
                        প্রধান হাদীস গ্রন্থসমূহ
                    </h2>
                    <p className="mt-2 text-xs sm:text-sm text-slate-500">
                        যেকোনো গ্রন্থ নির্বাচন করে অধ্যায়ভিত্তিক হাদীস পাঠ ও গবেষণা শুরু করুন।
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {books.map((book) => (
                        <div
                            key={book.slug || book.id}
                            className="group flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-[#1A2E2F]/40 hover:shadow-xl"
                        >
                            <div>
                                {/* Header Strip */}
                                <div className="bg-gradient-to-r from-[#102526] to-[#1A2E2F] p-6 text-white text-center relative overflow-hidden">
                                    <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:16px_16px]"></div>
                                    <span className="font-arabic text-2xl font-bold text-[#FFF99A]">
                                        {book.name_arabic}
                                    </span>
                                </div>

                                <div className="p-6">
                                    <h3 className="text-xl font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors font-bangla">
                                        {book.name_bangla}
                                    </h3>

                                    <p className="mt-1 text-xs font-semibold text-emerald-800">
                                        {book.author}
                                    </p>

                                    <p className="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                        {book.description}
                                    </p>

                                    <div className="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500 font-medium">
                                        <span>📜 সর্বমোট হাদীস:</span>
                                        <span className="font-bold text-[#1A2E2F]">{book.total_hadith} টি</span>
                                    </div>
                                </div>
                            </div>

                            <div className="p-6 pt-0">
                                <Link
                                    href={`/hadith/${book.slug}`}
                                    className="flex w-full items-center justify-center rounded-xl bg-[#1A2E2F] py-3 text-xs font-bold text-white transition-all duration-200 hover:bg-[#102526] hover:shadow-md"
                                >
                                    হাদীস পাঠ শুরু করুন →
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </MainLayout>
    );
}
