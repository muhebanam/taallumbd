import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function CertificateView({ certificate }) {
    const [copied, setCopied] = useState(false);
    const verifyUrl = certificate.verification_url || `${window.location.origin}/verify/${certificate.uuid || certificate.certificate_no}`;

    const handleCopy = () => {
        navigator.clipboard.writeText(verifyUrl);
        setCopied(true);
        setTimeout(() => setCopied(false), 3000);
    };

    return (
        <AppLayout>
            <Head title={`সনদপত্র — ${certificate.course?.title} | আত-তাআল্লুম`} />

            <div className="mx-auto max-w-4xl px-4 py-12">
                {/* Certificate Frame */}
                <div 
                    className="relative overflow-hidden rounded-3xl border-8 border-[#1A2E2F] bg-[#FCFDFC] p-8 sm:p-14 text-center shadow-2xl print:border-4 print:shadow-none print:m-0 print:p-8" 
                    id="certificate"
                >
                    {/* Inner Decorative Border */}
                    <div className="absolute inset-3 border-2 border-[#FFF99A]/80 pointer-events-none rounded-2xl"></div>

                    {/* Watermark Logo Background */}
                    <div className="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
                        <img src="/images/logo.png" alt="" className="h-96 w-96 object-contain" />
                    </div>

                    {/* Bismillah Header */}
                    <div className="text-sm font-arabic tracking-widest text-[#1A2E2F] opacity-90 mb-3">
                        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                    </div>

                    {/* Academy Header */}
                    <div className="flex flex-col items-center">
                        <img 
                            src="/images/logo.png" 
                            alt="আত-তাআল্লুম" 
                            onError={(e) => {
                                e.target.onerror = null;
                                e.target.style.display = 'none';
                            }}
                            className="h-16 w-16 rounded-full bg-[#1A2E2F]/10 p-1 object-contain" 
                        />
                        <h1 className="mt-2 text-2xl sm:text-3xl font-extrabold text-[#1A2E2F] font-bangla tracking-wide">
                            আত-তাআল্লুম ডিজিটাল একাডেমি
                        </h1>
                        <p className="text-xs tracking-wider text-slate-500 uppercase font-semibold">
                            TaallumBD Digital Islamic Academy • Dhaka, Bangladesh
                        </p>
                    </div>

                    <div className="mx-auto my-6 h-0.5 w-1/2 bg-gradient-to-r from-transparent via-[#1A2E2F]/30 to-transparent" />

                    <div className="inline-block rounded-full bg-[#1A2E2F] px-6 py-1.5 text-xs font-bold uppercase tracking-widest text-[#FFF99A]">
                        প্রশংসাপত্র ও সনদ
                    </div>

                    {/* Main Text */}
                    <p className="mt-6 text-sm text-slate-600 font-bangla">এই মর্মে প্রত্যয়ন করা যাচ্ছে যে,</p>
                    <h2 className="mt-2 text-3xl sm:text-4xl font-extrabold text-[#102526] font-bangla tracking-tight">
                        {certificate.user?.name}
                    </h2>

                    <p className="mt-4 text-sm text-slate-600 font-bangla">
                        আত-তাআল্লুম পরিচালিত নিম্নোক্ত কোর্সটির সকল পাঠ, মূল্যায়ন ও পরীক্ষায় সফলতার সাথে উত্তীর্ণ হয়েছেন:
                    </p>
                    <h3 className="mt-3 text-xl sm:text-2xl font-bold text-emerald-900 font-bangla">
                        “{certificate.course?.title}”
                    </h3>

                    {/* Certificate Meta & Signatures */}
                    <div className="mt-12 grid grid-cols-1 sm:grid-cols-3 items-end gap-6 border-t border-slate-200/80 pt-8 text-left">
                        {/* QR Code */}
                        <div className="flex items-center gap-3">
                            <img
                                src={certificate.qr_code_url || `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(verifyUrl)}`}
                                alt="ভেরিফিকেশন কিউআর"
                                className="h-20 w-20 rounded-lg border border-slate-300 p-1 bg-white"
                            />
                            <div className="text-[11px] text-slate-500 space-y-0.5">
                                <span className="font-bold text-slate-800 block">অনলাইন যাচাই</span>
                                <span className="font-mono block">নং: {certificate.certificate_no}</span>
                                <span className="block">তারিখ: {new Date(certificate.issued_at).toLocaleDateString('bn-BD')}</span>
                            </div>
                        </div>

                        {/* Signature 1 */}
                        <div className="text-center sm:text-center">
                            <div className="mx-auto w-36 border-b border-slate-400 pb-1 text-xs font-serif text-slate-800 italic">
                                Ustadz Council
                            </div>
                            <span className="mt-1 block text-xs font-bold text-slate-700">কোর্স উস্তায</span>
                            <span className="text-[10px] text-slate-500">আত-তাআল্লুম একাডেমি</span>
                        </div>

                        {/* Signature 2 */}
                        <div className="text-center sm:text-right">
                            <div className="sm:ml-auto w-36 border-b border-slate-400 pb-1 text-xs font-serif text-slate-800 italic text-center sm:text-right">
                                Academic Director
                            </div>
                            <span className="mt-1 block text-xs font-bold text-slate-700">একাডেমিক পরিচালক</span>
                            <span className="text-[10px] text-slate-500">আত-তাআল্লুম শরীয়াহ বোর্ড</span>
                        </div>
                    </div>
                </div>

                {/* Print & Share Actions */}
                <div className="mt-8 flex flex-wrap items-center justify-center gap-4 print:hidden">
                    <button 
                        onClick={() => window.print()} 
                        className="btn-primary !px-6 !py-3 text-sm flex items-center gap-2 shadow-md hover:scale-105"
                    >
                        <span>🖨️</span>
                        <span>প্রিন্ট / PDF হিসেবে সংরক্ষণ করুন</span>
                    </button>

                    <button
                        onClick={handleCopy}
                        className="btn-secondary !px-5 !py-3 text-sm flex items-center gap-2"
                    >
                        <span>🔗</span>
                        <span>{copied ? 'লিংক কপি হয়েছে!' : 'যাচাইকরণ লিংক কপি করুন'}</span>
                    </button>

                    <Link
                        href={`/verify/${certificate.uuid || certificate.certificate_no}`}
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-800 hover:text-emerald-950 px-3 py-3"
                    >
                        <span>পাবলিক ভেরিফিকেশন পেজ দেখুন →</span>
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
