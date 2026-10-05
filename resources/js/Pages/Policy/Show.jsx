import React from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import SeoHead from '../../Components/SeoHead';

const POLICY_LINKS = [
    { slug: 'terms', label: 'ব্যবহারের শর্তাবলী', icon: '📜' },
    { slug: 'privacy', label: 'গোপনীয়তা নীতি', icon: '🔒' },
    { slug: 'refund', label: 'রিফান্ড ও বাতিলকরণ নীতি', icon: '🪙' },
    { slug: 'content-policy', label: 'কনটেন্ট ও গবেষণা নীতি', icon: '📖' },
    { slug: 'fatwa-disclaimer', label: 'ফাতাওয়া সংক্রান্ত ডিসক্লেইমার', icon: '⚖️' },
];

export default function PolicyShow({ page }) {
    const breadcrumbSchema = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'হোম', item: 'https://taallum.org' },
            { '@type': 'ListItem', position: 2, name: page.title, item: `https://taallum.org/${page.slug}` },
        ],
    };

    return (
        <MainLayout>
            <SeoHead
                title={page.meta_title || page.title}
                description={page.meta_description || `${page.title} — আত-তাআল্লুম ডিজিটাল একাডেমি।`}
                canonical={`https://taallum.org/${page.slug}`}
                jsonLd={breadcrumbSchema}
            />

            {/* Header Banner */}
            <div className="bg-[#102526] text-white py-14 border-b border-brand/20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-2 text-xs text-brand-cream/80 mb-3">
                        <Link href="/" className="hover:underline">হোম</Link>
                        <span>›</span>
                        <span>নীতিমালা</span>
                        <span>›</span>
                        <span className="text-white">{page.title}</span>
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-extrabold font-bangla text-[#FFF99A]">
                        {page.title}
                    </h1>
                    <p className="mt-2 text-xs sm:text-sm text-slate-300">
                        সর্বশেষ পরিমার্জন: {new Date(page.updated_at).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' })}
                    </p>
                </div>
            </div>

            {/* Content Container */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    {/* Sidebar Links */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-24 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 px-2">
                                প্রাতিষ্ঠানিক নীতিমালা
                            </h3>
                            <nav className="space-y-1">
                                {POLICY_LINKS.map((item) => {
                                    const active = item.slug === page.slug;
                                    return (
                                        <Link
                                            key={item.slug}
                                            href={`/${item.slug}`}
                                            className={`flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold rounded-xl transition ${
                                                active
                                                    ? 'bg-[#1A2E2F] text-white shadow-sm'
                                                    : 'text-slate-700 hover:bg-slate-100 hover:text-brand'
                                            }`}
                                        >
                                            <span>{item.icon}</span>
                                            <span>{item.label}</span>
                                        </Link>
                                    );
                                })}
                            </nav>
                        </div>
                    </div>

                    {/* Main Document Body */}
                    <div className="lg:col-span-3">
                        <div className="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-10 shadow-sm leading-relaxed text-slate-700">
                            <div className="prose prose-slate max-w-none font-bangla text-base whitespace-pre-line space-y-4">
                                {page.body}
                            </div>

                            <div className="mt-12 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                                <p>আত-তাআল্লুম একাডেমি কর্তৃপক্ষ যেকোনো সময় এই নীতিমালা হালনাগাদ করার অধিকার সংরক্ষণ করে।</p>
                                <Link href="/contact" className="text-emerald-700 hover:underline font-bold">
                                    প্রশ্ন আছে? যোগাযোগ করুন →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
