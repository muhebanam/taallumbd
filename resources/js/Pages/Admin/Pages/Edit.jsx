import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

export default function PolicyEdit({ page }) {
    const { data, setData, put, processing, errors } = useForm({
        title: page.title || '',
        body: page.body || '',
        meta_title: page.meta_title || '',
        meta_description: page.meta_description || '',
        is_published: page.is_published ?? true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/admin/pages/${page.id}`);
    };

    return (
        <DashboardLayout title={`পলিসি সম্পাদনা: ${page.title}`}>
            <Head title={`পলিসি সম্পাদনা — ${page.title}`} />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link
                        href="/admin/pages"
                        className="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2"
                    >
                        ← সকল নীতিমালায় ফিরুন
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-[#142425] font-bangla">
                        {page.title} সম্পাদনা
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        URL Slug: <code className="text-slate-700 font-mono">/policy/{page.slug}</code>
                    </p>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left 2 Cols: Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    পৃষ্ঠার শিরোনাম <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    className="w-full rounded-xl border-slate-300 text-sm focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                    required
                                />
                                {errors.title && <p className="text-[11px] text-rose-500 mt-1">{errors.title}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    পলিসি ও নীতিমালার মূল বক্তব্য (Markdown / বাংলা টেক্সট) <span className="text-rose-500">*</span>
                                </label>
                                <textarea
                                    rows="18"
                                    value={data.body}
                                    onChange={(e) => setData('body', e.target.value)}
                                    className="w-full rounded-2xl border-slate-300 text-sm leading-relaxed font-bangla focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                    required
                                ></textarea>
                                {errors.body && <p className="text-[11px] text-rose-500 mt-1">{errors.body}</p>}
                                <p className="text-[11px] text-slate-400">
                                    প্যারাগ্রাফ ও পয়েন্ট আকারে স্পষ্ট ভাষায় বাংলা নীতিমালা লিখুন।
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Right Col: SEO Metadata & Publishing */}
                    <div className="space-y-6">
                        <div className="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-sm space-y-4">
                            <h3 className="text-sm font-bold text-[#142425] border-b pb-3 font-bangla">
                                প্রকাশনা ও দৃশ্যমানতা
                            </h3>

                            <label className="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={data.is_published}
                                    onChange={(e) => setData('is_published', e.target.checked)}
                                    className="h-4 w-4 rounded border-slate-300 text-[#1A2E2F] focus:ring-[#1A2E2F]"
                                />
                                <span className="text-xs font-bold text-slate-800">
                                    ওয়েবসাইটে সরাসরি প্রকাশ করুন (Published)
                                </span>
                            </label>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full btn-primary text-xs !py-3 font-bold justify-center"
                            >
                                {processing ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন'}
                            </button>
                        </div>

                        <div className="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-sm space-y-4">
                            <h3 className="text-sm font-bold text-[#142425] border-b pb-3 font-bangla">
                                সার্চ ইঞ্জিন ও এসইও (SEO Metadata)
                            </h3>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">
                                    এসইও মেটা টাইটেল (Title)
                                </label>
                                <input
                                    type="text"
                                    value={data.meta_title}
                                    onChange={(e) => setData('meta_title', e.target.value)}
                                    placeholder="উদাঃ ব্যবহারের নিয়ম ও শর্তাবলি — আত-তাআল্লুম"
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                />
                                {errors.meta_title && <p className="text-[11px] text-rose-500 mt-1">{errors.meta_title}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">
                                    এসইও মেটা ডেসক্রিপশন (Description)
                                </label>
                                <textarea
                                    rows="3"
                                    value={data.meta_description}
                                    onChange={(e) => setData('meta_description', e.target.value)}
                                    placeholder="সার্চ ফলাফলে প্রদর্শনের জন্য ১০০-১৬০ অক্ষরের বিবরণ..."
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                ></textarea>
                                {errors.meta_description && <p className="text-[11px] text-rose-500 mt-1">{errors.meta_description}</p>}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </DashboardLayout>
    );
}
