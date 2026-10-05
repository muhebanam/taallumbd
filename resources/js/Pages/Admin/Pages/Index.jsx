import React from 'react';
import { Head, Link } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

export default function PagesIndex({ pages }) {
    return (
        <DashboardLayout title="প্রাতিষ্ঠানিক পলিসি ও নীতিমালা ব্যবস্থাপনা">
            <Head title="পলিসি পৃষ্ঠা ব্যবস্থাপনা" />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-xl sm:text-2xl font-bold text-[#142425] font-bangla">
                        নীতিমালা ও শর্তাবলি পৃষ্ঠা (Policy Pages)
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        ব্যবহারের শর্তাবলি, গোপনীয়তা, রিফান্ড পলিসি ও ফতোয়া ডিসক্লেইমার সম্পাদনা ও প্রকাশনা।
                    </p>
                </div>
            </div>

            <div className="rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                <div className="divide-y divide-slate-100">
                    {pages && pages.map((page) => (
                        <div
                            key={page.id}
                            className="p-5 sm:p-6 hover:bg-slate-50/70 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        >
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <h3 className="text-base font-bold text-[#142425] font-bangla">
                                        {page.title}
                                    </h3>
                                    {page.is_published ? (
                                        <span className="rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                            প্রকাশিত
                                        </span>
                                    ) : (
                                        <span className="rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                                            খসড়া
                                        </span>
                                    )}
                                </div>

                                <p className="text-xs text-slate-500">
                                    পাথ: <code className="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono">/policy/{page.slug}</code>
                                    {page.last_updated_by && page.last_updated_by_user && (
                                        <span className="ml-2">• সর্বশেষ সম্পাদনা: {page.last_updated_by_user.name}</span>
                                    )}
                                </p>
                            </div>

                            <div className="flex items-center gap-2">
                                <a
                                    href={`/policy/${page.slug}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                                >
                                    ওয়েবসাইটে দেখুন ↗
                                </a>
                                <Link
                                    href={`/admin/pages/${page.id}/edit`}
                                    className="btn-primary text-xs !py-2 !px-4"
                                >
                                    সম্পাদনা করুন ✎
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </DashboardLayout>
    );
}
