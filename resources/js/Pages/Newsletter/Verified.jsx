import React from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';
import SeoHead from '../../Components/SeoHead';

export default function NewsletterVerified({ success, message }) {
    return (
        <MainLayout>
            <SeoHead title="নিউজলেটার সাবস্ক্রিপশন স্ট্যাটাস" />

            <div className="min-h-[60vh] flex items-center justify-center py-16 px-4">
                <div className="max-w-md w-full rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-lg">
                    <span className="text-5xl block mb-4">
                        {success ? '🎉' : '⚠️'}
                    </span>

                    <h1 className="text-2xl font-extrabold font-bangla text-[#142425]">
                        {success ? 'সাবস্ক্রিপশন নিশ্চিত হয়েছে!' : 'যাচাইকরণ ব্যর্থ হয়েছে'}
                    </h1>

                    <p className="mt-3 text-sm text-slate-600 leading-relaxed font-bangla">
                        {message}
                    </p>

                    <div className="mt-8 flex flex-col gap-3">
                        <Link
                            href="/"
                            className="w-full rounded-xl bg-[#1A2E2F] py-3 text-sm font-bold text-white shadow-md hover:bg-[#102526] transition"
                        >
                            হোমপেজে ফিরে যান
                        </Link>
                        <Link
                            href="/courses"
                            className="w-full rounded-xl border border-slate-200 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"
                        >
                            কোর্সসমূহ দেখুন
                        </Link>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
