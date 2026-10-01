import React, { useState } from 'react';

export default function Newsletter() {
    const [email, setEmail] = useState('');
    const [submitted, setSubmitted] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (email.trim()) {
            setSubmitted(true);
            setEmail('');
        }
    };

    return (
        <section className="relative overflow-hidden bg-[#102526] py-16 sm:py-20 text-white">
            {/* Background Texture */}
            <div className="absolute inset-0 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px] opacity-5"></div>
            <div className="absolute -top-24 right-0 h-64 w-64 rounded-full bg-[#1A2E2F] blur-3xl opacity-50"></div>

            <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-3xl text-center">
                    <span className="inline-block rounded-full border border-[#FFF99A]/30 bg-white/5 px-4 py-1 text-xs font-semibold text-[#FFF99A]">
                        সাপ্তাহিক দ্বীনি বার্তা
                    </span>

                    <h2 className="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl font-bangla text-white">
                        নতুন কোর্স ও গুরুত্বপূর্ণ ফাতাওয়ার আপডেট পান
                    </h2>

                    <p className="mt-3 text-base text-slate-300 font-bangla max-w-2xl mx-auto">
                        নিয়মিত প্রকাশিত গবেষণাধর্মী প্রবন্ধ, আসন্ন উন্মুক্ত ওয়েবিনার এবং নতুন কোর্সের নোটিফিকেশন সবার আগে পেতে যুক্ত থাকুন।
                    </p>

                    {submitted ? (
                        <div className="mt-8 rounded-2xl bg-emerald-900/60 border border-emerald-500/40 p-5 text-center text-emerald-200 backdrop-blur-md">
                            <span className="text-xl">✅</span>
                            <p className="mt-1 font-bold">জাযাকাল্লাহু খাইরান! আপনার ইমেইল সফলভাবে যুক্ত করা হয়েছে।</p>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit} className="mt-8 flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="আপনার সচল ইমেইল অ্যাড্রেস লিখুন..."
                                className="flex-1 rounded-xl border border-white/20 bg-white/10 px-5 py-3.5 text-sm text-white placeholder-slate-400 backdrop-blur-md focus:border-[#FFF99A] focus:outline-none focus:ring-2 focus:ring-[#FFF99A]/40"
                            />
                            <button
                                type="submit"
                                className="inline-flex items-center justify-center rounded-xl bg-[#FFF99A] px-7 py-3.5 text-sm font-bold text-[#102526] shadow-md transition-all duration-200 hover:bg-[#fff780] hover:scale-105"
                            >
                                সাবস্ক্রাইব করুন
                            </button>
                        </form>
                    )}

                    <p className="mt-4 text-xs text-slate-400">
                        🔒 আমরা আপনার তথ্যের গোপনীয়তা রক্ষা করি। কোনো অনাকাঙ্ক্ষিত স্প্যাম পাঠানো হবে না।
                    </p>
                </div>
            </div>
        </section>
    );
}
