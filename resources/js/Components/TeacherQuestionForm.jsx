import React from 'react';
import { useForm, usePage, Link } from '@inertiajs/react';

export default function TeacherQuestionForm({ teacher, categories = [] }) {
    const { auth } = usePage().props;

    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
        subject: '',
        question_body: '',
        category_id: '',
        is_private: false,
    });

    const categoryOptions = categories.flatMap((category) => [
        { id: category.id, name: category.name },
        ...(category.children ?? []).map((child) => ({
            id: child.id,
            name: `- ${child.name}`,
        })),
    ]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(`/teachers/${teacher.slug}/questions`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    if (!auth.user) {
        return (
            <div className="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm">
                <h3 className="text-lg font-bold text-slate-800">শিক্ষককে প্রশ্ন করুন</h3>
                <p className="mt-2 text-sm text-slate-500">প্রশ্ন করতে হলে আপনাকে প্রথমে লগইন করতে হবে।</p>
                <div className="mt-4">
                    <Link
                        href="/login"
                        className="inline-flex items-center justify-center rounded-xl bg-[#102526] hover:bg-[#1A2E2F] text-white px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-300"
                    >
                        লগইন করুন
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 className="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg className="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>এই শিক্ষককে প্রশ্ন করুন</span>
            </h3>

            {wasSuccessful && (
                <div className="mt-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-sm font-semibold text-emerald-800">
                    আপনার প্রশ্নটি সফলভাবে পাঠানো হয়েছে। শিক্ষকের উত্তরের পর এটি প্রকাশিত হবে।
                </div>
            )}

            <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                <div>
                    <label htmlFor="category_id" className="block text-sm font-semibold text-slate-700">বিষয়ভিত্তিক ক্যাটেগরি</label>
                    <select
                        id="category_id"
                        value={data.category_id}
                        onChange={e => setData('category_id', e.target.value)}
                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    >
                        <option value="">একটি ক্যাটেগরি নির্বাচন করুন</option>
                        {categoryOptions.map((category) => (
                            <option key={category.id} value={category.id}>{category.name}</option>
                        ))}
                    </select>
                    {errors.category_id && <p className="mt-1 text-xs font-semibold text-red-600">{errors.category_id}</p>}
                </div>

                <div>
                    <label htmlFor="subject" className="block text-sm font-semibold text-slate-700">বিষয়</label>
                    <input
                        type="text"
                        id="subject"
                        value={data.subject}
                        onChange={e => setData('subject', e.target.value)}
                        placeholder="প্রশ্নের মূল বিষয় সংক্ষেপে লিখুন"
                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    />
                    {errors.subject && <p className="mt-1 text-xs font-semibold text-red-600">{errors.subject}</p>}
                </div>

                <div>
                    <label htmlFor="question_body" className="block text-sm font-semibold text-slate-700">আপনার প্রশ্ন</label>
                    <textarea
                        id="question_body"
                        rows="4"
                        value={data.question_body}
                        onChange={e => setData('question_body', e.target.value)}
                        placeholder="বিস্তারিতভাবে আপনার প্রশ্নটি এখানে লিখুন..."
                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        required
                    ></textarea>
                    {errors.question_body && <p className="mt-1 text-xs font-semibold text-red-600">{errors.question_body}</p>}
                </div>

                <div className="flex items-start">
                    <div className="flex h-5 items-center">
                        <input
                            id="is_private"
                            type="checkbox"
                            checked={data.is_private}
                            onChange={e => setData('is_private', e.target.checked)}
                            className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        />
                    </div>
                    <div className="ml-2.5 text-sm">
                        <label htmlFor="is_private" className="font-semibold text-slate-700">প্রশ্নটি গোপন রাখুন</label>
                        <p className="text-xs text-slate-400 mt-0.5">গোপন প্রশ্নগুলো কেবল শিক্ষক দেখতে পারবেন এবং পাবলিকলি প্রকাশিত হবে না।</p>
                    </div>
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full inline-flex items-center justify-center rounded-xl bg-[#102526] hover:bg-[#1A2E2F] text-white py-3 text-sm font-bold shadow-sm hover:shadow transition-all duration-300 disabled:opacity-50"
                    >
                        {processing ? 'জমা দেওয়া হচ্ছে...' : 'প্রশ্ন পাঠান'}
                    </button>
                </div>
            </form>
        </div>
    );
}
