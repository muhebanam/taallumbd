import { Head, Link, useForm, usePage } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function FatwaAsk({ categories = [], scholars = [] }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;

    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        questioner_name: user?.name || '',
        questioner_email: user?.email || '',
        questioner_phone: user?.phone || '',
        question_title: '',
        question_body: '',
        category_id: '',
        teacher_id: '',
        is_private: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/fatawa/ask', {
            onSuccess: () => {
                reset('question_title', 'question_body', 'category_id', 'teacher_id');
            },
        });
    };

    const inputClasses = "mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#102526] focus:outline-none focus:ring-2 focus:ring-[#102526]/20 transition";

    return (
        <MainLayout>
            <Head title="শরয়ী প্রশ্ন জমা দিন — আত-তাআল্লুম ফাতাওয়া বিভাগ" />

            {/* Header */}
            <div className="bg-gradient-to-b from-[#102526] to-[#1A2E2F] py-12 text-white">
                <div className="mx-auto max-w-4xl px-4 text-center sm:px-6">
                    <span className="rounded-full bg-[#FFF99A]/20 px-3.5 py-1 text-xs font-semibold text-[#FFF99A]">
                        দারুল ইফতা ও ফাতাওয়া বোর্ড
                    </span>
                    <h1 className="mt-3 text-3xl font-extrabold sm:text-4xl text-white">
                        শরয়ী প্রশ্ন পেশ করুন
                    </h1>
                    <p className="mt-2 text-sm text-[#F8FAF8]/80 max-w-xl mx-auto">
                        আপনার দ্বীনি, পারিবারিক বা সামাজিক জীবনের যেকোনো জটিল মাসআলা সম্পর্কে নির্ভরযোগ্য আলেমগণের নিকট প্রশ্ন করতে নিচের ফর্মটি পূরণ করুন।
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                {flash?.success && (
                    <div className="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-5 text-emerald-900 flex items-start gap-3 shadow-sm">
                        <svg className="h-6 w-6 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 className="font-bold text-sm">আলহামদুলিল্লাহ! আপনার প্রশ্নটি গৃহীত হয়েছে।</h4>
                            <p className="mt-1 text-xs text-emerald-800 leading-relaxed">
                                {flash.success}
                            </p>
                            {user && (
                                <Link href="/dashboard/my-questions" className="mt-2 inline-block font-bold underline text-xs text-emerald-950">
                                    আপনার প্রশ্নসমূহের তালিকা দেখুন &rarr;
                                </Link>
                            )}
                        </div>
                    </div>
                )}

                <div className="grid gap-8 lg:grid-cols-3">
                    {/* Left Form (2 cols) */}
                    <div className="lg:col-span-2">
                        <form onSubmit={submit} className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
                            {/* Personal Info Grid */}
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="text-xs font-bold text-gray-700">আপনার নাম *</label>
                                    <input
                                        type="text"
                                        required
                                        className={inputClasses}
                                        value={data.questioner_name}
                                        onChange={(e) => setData('questioner_name', e.target.value)}
                                        placeholder="উদা: আব্দুল্লাহ মাহমুদ"
                                    />
                                    {errors.questioner_name && <p className="mt-1 text-xs text-rose-600">{errors.questioner_name}</p>}
                                </div>

                                <div>
                                    <label className="text-xs font-bold text-gray-700">ইমেইল ঠিকানা *</label>
                                    <input
                                        type="email"
                                        required
                                        className={inputClasses}
                                        value={data.questioner_email}
                                        onChange={(e) => setData('questioner_email', e.target.value)}
                                        placeholder="উদা: example@mail.com"
                                    />
                                    {errors.questioner_email && <p className="mt-1 text-xs text-rose-600">{errors.questioner_email}</p>}
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="text-xs font-bold text-gray-700">মোবাইল নম্বর (ঐচ্ছিক)</label>
                                    <input
                                        type="tel"
                                        className={inputClasses}
                                        value={data.questioner_phone}
                                        onChange={(e) => setData('questioner_phone', e.target.value)}
                                        placeholder="০১৭xxxxxxxx"
                                    />
                                    {errors.questioner_phone && <p className="mt-1 text-xs text-rose-600">{errors.questioner_phone}</p>}
                                </div>

                                <div>
                                    <label className="text-xs font-bold text-gray-700">বিভাগ নির্বাচন করুন *</label>
                                    <select
                                        required
                                        className={inputClasses}
                                        value={data.category_id}
                                        onChange={(e) => setData('category_id', e.target.value)}
                                    >
                                        <option value="">একটি বিভাগ বেছে নিন</option>
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                    {errors.category_id && <p className="mt-1 text-xs text-rose-600">{errors.category_id}</p>}
                                </div>
                            </div>

                            {/* Preferred Scholar Option */}
                            {scholars.length > 0 && (
                                <div>
                                    <label className="text-xs font-bold text-gray-700">
                                        নির্দিষ্ট কোনো মুফতী/উস্তাযের কাছ থেকে উত্তর চান? (ঐচ্ছিক)
                                    </label>
                                    <select
                                        className={inputClasses}
                                        value={data.teacher_id}
                                        onChange={(e) => setData('teacher_id', e.target.value)}
                                    >
                                        <option value="">সাধারণ মুফতী বোর্ড (যেকোনো বিজ্ঞ আলেম)</option>
                                        {scholars.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.title_prefix} {s.user?.name} {s.designation ? `(${s.designation})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.teacher_id && <p className="mt-1 text-xs text-rose-600">{errors.teacher_id}</p>}
                                </div>
                            )}

                            {/* Question Title */}
                            <div>
                                <label className="text-xs font-bold text-gray-700">প্রশ্নের মূল বিষয় / শিরোনাম *</label>
                                <input
                                    type="text"
                                    required
                                    className={inputClasses}
                                    value={data.question_title}
                                    onChange={(e) => setData('question_title', e.target.value)}
                                    placeholder="উদা: সফর অবস্থায় নামায কসর করার সঠিক নিয়ম কী?"
                                />
                                {errors.question_title && <p className="mt-1 text-xs text-rose-600">{errors.question_title}</p>}
                            </div>

                            {/* Detailed Question */}
                            <div>
                                <label className="text-xs font-bold text-gray-700">বিস্তারিত প্রশ্ন বিবরণ *</label>
                                <textarea
                                    required
                                    rows={7}
                                    className={inputClasses}
                                    value={data.question_body}
                                    onChange={(e) => setData('question_body', e.target.value)}
                                    placeholder="আপনার সমস্যার প্রেক্ষাপট, বর্তমান অবস্থা এবং নির্দিষ্ট জানার বিষয় পরিষ্কারভাবে বর্ণনা করুন..."
                                />
                                <p className="mt-1 text-[11px] text-gray-400">কমপক্ষে ২০ অক্ষরের স্পষ্ট বিবরণ দিন।</p>
                                {errors.question_body && <p className="mt-1 text-xs text-rose-600">{errors.question_body}</p>}
                            </div>

                            {/* Privacy Checkbox */}
                            <div className="rounded-2xl border border-gray-200 bg-gray-50/70 p-4">
                                <label className="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        className="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#102526] focus:ring-[#102526]"
                                        checked={data.is_private}
                                        onChange={(e) => setData('is_private', e.target.checked)}
                                    />
                                    <span className="text-xs text-gray-700 leading-relaxed">
                                        <strong className="block text-gray-900">প্রশ্নটি ব্যক্তিগত ও গোপন রাখুন</strong>
                                        প্রশ্ন এবং সমাধান ওয়েবসাইটের উন্মুক্ত আর্কাইভে প্রকাশিত হবে না। কেবল আপনি এবং উত্তরদাতা মুফতী এটি দেখতে পাবেন।
                                    </span>
                                </label>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-2xl bg-[#102526] py-3.5 text-center text-sm font-bold text-[#FFF99A] shadow-md transition hover:bg-[#1A2E2F] hover:shadow-lg disabled:opacity-50"
                            >
                                {processing ? 'প্রশ্ন পাঠানো হচ্ছে...' : 'প্রশ্ন জমা দিন'}
                            </button>
                        </form>
                    </div>

                    {/* Right Guidelines Card */}
                    <div className="space-y-6">
                        <div className="rounded-3xl border border-amber-200 bg-amber-50/80 p-6 text-amber-950 shadow-sm">
                            <h3 className="font-bold text-sm text-amber-900 flex items-center gap-2">
                                <svg className="h-5 w-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                প্রশ্ন করার শরয়ী আদব
                            </h3>
                            <ul className="mt-4 space-y-3 text-xs leading-relaxed text-amber-900/90">
                                <li className="flex items-start gap-2">
                                    <span className="font-bold text-amber-700">১.</span>
                                    <span>প্রশ্ন কেবল নিজের আমল ও জানার উদ্দেশ্যে করুন, কাউকে পরীক্ষা করা বা বিতর্ক সৃষ্টির জন্য নয়।</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="font-bold text-amber-700">২.</span>
                                    <span>কাল্পনিক কোনো বিষয় নয়, বরং বাস্তব প্রয়োজন সংক্রান্ত প্রশ্ন করুন।</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="font-bold text-amber-700">৩.</span>
                                    <span>প্রশ্নে প্রয়োজনীয় সকল তথ্য (যেমন: আর্থিক মাসআলায় টাকার পরিমাণ, চুক্তির শর্ত) স্পষ্ট করুন।</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="font-bold text-amber-700">৪.</span>
                                    <span>উত্তর প্রস্তুত হতে কয়েক কার্যদিবস সময় লাগতে পারে, অনুগ্রহ করে ধৈর্য ধারণ করুন।</span>
                                </li>
                            </ul>
                        </div>

                        {/* Recent Archive Link */}
                        <div className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm text-center">
                            <h4 className="font-bold text-sm text-gray-900">ইতিমধ্যে কোনো সমাধান আছে কি?</h4>
                            <p className="mt-1 text-xs text-gray-500 leading-relaxed">
                                প্রশ্ন করার আগে আমাদের ফাতাওয়া আর্কাইভে অনুসন্ধান করে দেখতে পারেন।
                            </p>
                            <Link
                                href="/fatawa"
                                className="mt-4 inline-block w-full rounded-xl border border-gray-300 py-2.5 text-xs font-bold text-gray-800 hover:bg-gray-50 transition"
                            >
                                ফাতাওয়া আর্কাইভ দেখুন
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
