import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function CommunityCreate({ courses = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        topic: 'general',
        course_id: '',
        body: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/community');
    };

    const inputClasses = "mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#102526] focus:outline-none focus:ring-2 focus:ring-[#102526]/20 transition";

    return (
        <MainLayout>
            <Head title="নতুন আলোচনা শুরু করুন — আত-তাআল্লুম কমিউনিটি" />

            <div className="bg-gradient-to-b from-[#102526] to-[#1A2E2F] py-12 text-white">
                <div className="mx-auto max-w-4xl px-4 text-center sm:px-6">
                    <span className="rounded-full bg-[#FFF99A]/20 px-3.5 py-1 text-xs font-semibold text-[#FFF99A]">
                        উম্মাহ ডিসকাশন ফোরাম
                    </span>
                    <h1 className="mt-3 text-3xl font-extrabold sm:text-4xl text-white">
                        নতুন ইলমী আলোচনা বা প্রশ্ন পেশ করুন
                    </h1>
                    <p className="mt-2 text-sm text-[#F8FAF8]/80 max-w-xl mx-auto">
                        সহপাঠী শিক্ষার্থী, গবেষক ও উস্তাযগণের সাথে ফলপ্রসূ আলোচনার জন্য আপনার বিষয়টি উপস্থাপন করুন।
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <form onSubmit={submit} className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
                            {/* Topic Selection */}
                            <div>
                                <label className="text-xs font-bold text-gray-700">আলোচনার মূল বিষয় / ক্যাটাগরি *</label>
                                <select
                                    required
                                    className={inputClasses}
                                    value={data.topic}
                                    onChange={(e) => setData('topic', e.target.value)}
                                >
                                    <option value="general">সাধারণ দ্বীনি আলোচনা</option>
                                    <option value="quran-hadith">কুরআন ও হাদিস গবেষণা</option>
                                    <option value="fiqh-masala">ফিকহ ও আহকাম জিজ্ঞাসা</option>
                                    <option value="arabic-lang">আরবি ভাষা ও ব্যাকরণ</option>
                                    <option value="course-qa">কোর্স সংক্রান্ত প্রশ্নোত্তর</option>
                                </select>
                                {errors.topic && <p className="mt-1 text-xs text-rose-600">{errors.topic}</p>}
                            </div>

                            {/* Optional Course Link */}
                            {courses.length > 0 && (
                                <div>
                                    <label className="text-xs font-bold text-gray-700">
                                        কোনো নির্দিষ্ট কোর্সের সাথে সম্পৃক্ত? (ঐচ্ছিক)
                                    </label>
                                    <select
                                        className={inputClasses}
                                        value={data.course_id}
                                        onChange={(e) => setData('course_id', e.target.value)}
                                    >
                                        <option value="">কোনো কোর্স নেই (উন্মুক্ত আলোচনা)</option>
                                        {courses.map((c) => (
                                            <option key={c.id} value={c.id}>{c.title}</option>
                                        ))}
                                    </select>
                                    {errors.course_id && <p className="mt-1 text-xs text-rose-600">{errors.course_id}</p>}
                                </div>
                            )}

                            {/* Title */}
                            <div>
                                <label className="text-xs font-bold text-gray-700">আলোচনা বা প্রশ্নের সুস্পষ্ট শিরোনাম *</label>
                                <input
                                    type="text"
                                    required
                                    className={inputClasses}
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="উদা: তারাবীহর রাকআত সংখ্যা ও তাহাজ্জুদের মধ্যে পার্থক্য কী?"
                                />
                                {errors.title && <p className="mt-1 text-xs text-rose-600">{errors.title}</p>}
                            </div>

                            {/* Body */}
                            <div>
                                <label className="text-xs font-bold text-gray-700">বিস্তারিত বিবরণ *</label>
                                <textarea
                                    rows={8}
                                    required
                                    className={inputClasses}
                                    value={data.body}
                                    onChange={(e) => setData('body', e.target.value)}
                                    placeholder="আপনার চিন্তা, সংশ্লিষ্ট আয়াত/হাদিস বা নির্দিষ্ট প্রশ্ন সুন্দর ও স্পষ্ট ভাষায় উপস্থাপন করুন..."
                                />
                                <p className="mt-1 text-[11px] text-gray-400">কমপক্ষে ১৫ অক্ষরের বোধগম্য বিবরণ লিখুন।</p>
                                {errors.body && <p className="mt-1 text-xs text-rose-600">{errors.body}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-2xl bg-[#102526] py-3.5 text-center text-sm font-bold text-[#FFF99A] shadow-md transition hover:bg-[#1A2E2F] hover:shadow-lg disabled:opacity-50"
                            >
                                {processing ? 'পোস্ট যুক্ত হচ্ছে...' : 'আলোচনাটি উন্মুক্ত করুন'}
                            </button>
                        </form>
                    </div>

                    {/* Guidelines Sidebar */}
                    <div className="space-y-6">
                        <div className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="font-bold text-sm text-[#102526] flex items-center gap-2">
                                <svg className="h-4 w-4 text-emerald-800" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                </svg>
                                সুন্দর আলোচনার পরামর্শ
                            </h3>
                            <ul className="mt-3 space-y-2 text-xs text-gray-600 leading-relaxed">
                                <li>• শিরোনাম যথাসম্ভব সংক্ষিপ্ত ও তথ্যবহুল রাখুন।</li>
                                <li>• বানান ও যতিচিহ্ন ব্যবহারে সচেতন থাকুন।</li>
                                <li>• কোনো বই বা কিতাবের উদ্ধৃতি দিলে রেফারেন্স উল্লেখ করুন।</li>
                                <li>• অপ্রাসঙ্গিক বা বিজ্ঞাপনমূলক পোস্ট ফোরামে নিষিদ্ধ।</li>
                            </ul>
                        </div>

                        <div className="rounded-3xl border border-emerald-900/10 bg-emerald-50/60 p-6 text-center">
                            <p className="text-xs text-emerald-950 font-medium leading-relaxed">
                                আপনি কি ফতোয়া সংক্রান্ত কোনো জরুরি ব্যক্তিগত প্রশ্নের সমাধান চান?
                            </p>
                            <Link
                                href="/fatawa/ask"
                                className="mt-3 inline-block rounded-xl bg-emerald-900 px-4 py-2 text-xs font-bold text-white shadow hover:bg-emerald-950"
                            >
                                মুফতী বোর্ডের কাছে প্রশ্ন করুন
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
