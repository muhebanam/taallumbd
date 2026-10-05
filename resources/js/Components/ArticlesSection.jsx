import React from 'react';
import { Link } from '@inertiajs/react';

export default function ArticlesSection({ articles = [] }) {
    const articleList = Array.isArray(articles) ? articles : (articles && typeof articles === 'object' ? Object.values(articles) : []);
    if (articleList.length === 0) return null;

    return (
        <section className="py-16 sm:py-24 bg-[#F8FAF8]">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                            জ্ঞানের ভাণ্ডার
                        </span>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                            গবেষণাধর্মী প্রবন্ধ ও প্রবন্ধ সম্ভার
                        </h2>
                        <p className="mt-2 text-base text-slate-600 font-bangla">
                            ইসলামী চিন্তাধারা, সমসাময়িক প্রেক্ষাপট ও আত্মশুদ্ধিমূলক মূল্যবান লেখা।
                        </p>
                    </div>

                    <Link
                        href="/articles"
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-[#1A2E2F] hover:text-emerald-700 transition"
                    >
                        <span>সকল প্রবন্ধ দেখুন</span>
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                {/* Articles Grid */}
                <div className="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                    {articleList.map((art) => (
                        <article
                            key={art.id}
                            className="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-[#1A2E2F]/30"
                        >
                            <div>
                                {/* Thumbnail */}
                                <div className="aspect-[16/9] w-full overflow-hidden bg-[#102526] relative">
                                    {art.thumbnail ? (
                                        <img
                                            src={art.thumbnail.startsWith('http') ? art.thumbnail : `/storage/${art.thumbnail}`}
                                            alt={art.title}
                                            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#102526] to-[#1A2E2F] p-4 text-center">
                                            <span className="text-2xl">✍️</span>
                                        </div>
                                    )}

                                    {art.category && (
                                        <span className="absolute top-3 left-3 rounded-lg bg-black/60 backdrop-blur-md px-2.5 py-1 text-xs font-semibold text-white">
                                            {art.category.name}
                                        </span>
                                    )}
                                </div>

                                {/* Body */}
                                <div className="p-6">
                                    <div className="text-xs text-slate-500 mb-2">
                                        {art.published_at && new Date(art.published_at).toLocaleDateString('bn-BD')}
                                    </div>

                                    <h3 className="text-base font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors font-bangla line-clamp-2">
                                        <Link href={`/articles/${art.slug}`}>
                                            {art.title}
                                        </Link>
                                    </h3>

                                    <p className="mt-2 text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                        {art.excerpt || (art.body ? art.body.substring(0, 140) + '...' : '')}
                                    </p>
                                </div>
                            </div>

                            {/* Author & Read Link */}
                            <div className="p-6 pt-0 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span className="font-medium text-slate-700">
                                    {art.author?.name || 'আত-তাআল্লুম সম্পাদকীয়'}
                                </span>

                                <Link
                                    href={`/articles/${art.slug}`}
                                    className="font-bold text-[#1A2E2F] group-hover:text-emerald-700 transition"
                                >
                                    পাঠ করুন →
                                </Link>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
