import React, { useState } from 'react';
import { useForm, usePage, Link } from '@inertiajs/react';

export default function TeacherReviewForm({ teacher, courses = [] }) {
    const { auth } = usePage().props;
    const [hoverRating, setHoverRating] = useState(0);

    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
        rating: 5,
        review: '',
        course_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(`/teachers/${teacher.slug}/reviews`, {
            preserveScroll: true,
            onSuccess: () => reset({ rating: 5, review: '', course_id: '' }),
        });
    };

    if (!auth.user) {
        return (
            <div className="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm">
                <h3 className="text-base font-bold text-slate-800">শিক্ষার্থী ও পাঠকদের মতামত</h3>
                <p className="mt-2 text-xs text-slate-500">মতামত ও মূল্যায়ন প্রকাশ করতে অনুগ্রহ করে প্রথমে লগইন করুন।</p>
                <div className="mt-4">
                    <Link
                        href="/login"
                        className="inline-flex items-center justify-center rounded-xl bg-[#1A2E2F] hover:bg-[#102526] text-white px-5 py-2.5 text-xs font-semibold shadow-sm transition-all"
                    >
                        লগইন করুন
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span className="text-amber-500">★</span>
                <span>শিক্ষক সম্পর্কে আপনার মূল্যায়ন ও রিভিউ লিখুন</span>
            </h3>

            {wasSuccessful && (
                <div className="mt-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-xs font-semibold text-emerald-800">
                    আপনার রিভিউটি সফলভাবে জমা হয়েছে। মডারেশনের পর এটি প্রোফাইলে প্রদর্শিত হবে।
                </div>
            )}

            <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                {/* Course Selection (Optional) */}
                <div>
                    <label htmlFor="course_id" className="block text-xs font-semibold text-slate-700">
                        কোর্স নির্বাচন করুন (ঐচ্ছিক)
                    </label>
                    <select
                        id="course_id"
                        value={data.course_id}
                        onChange={(e) => setData('course_id', e.target.value)}
                        className="mt-1 w-full rounded-xl border border-slate-200 text-xs p-2.5 focus:border-[#1A2E2F] focus:ring-1 focus:ring-[#1A2E2F]"
                    >
                        <option value="">সামগ্রিক উস্তায মূল্যায়ন (সাধারণ রিভিউ)</option>
                        {courses.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.title}
                            </option>
                        ))}
                    </select>
                    {errors.course_id && (
                        <p className="mt-1 text-[11px] text-rose-600">{errors.course_id}</p>
                    )}
                </div>

                {/* Star Rating Selector */}
                <div>
                    <label className="block text-xs font-semibold text-slate-700 mb-1">
                        আপনার রেটিং নির্বাচন করুন
                    </label>
                    <div className="flex items-center gap-1">
                        {[1, 2, 3, 4, 5].map((star) => (
                            <button
                                key={star}
                                type="button"
                                onClick={() => setData('rating', star)}
                                onMouseEnter={() => setHoverRating(star)}
                                onMouseLeave={() => setHoverRating(0)}
                                className="p-1 focus:outline-none"
                            >
                                <svg
                                    className={`h-6 w-6 transition-colors ${
                                        star <= (hoverRating || data.rating)
                                            ? 'fill-amber-400 text-amber-400'
                                            : 'fill-slate-100 text-slate-200'
                                    }`}
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        ))}
                        <span className="ml-2 text-xs font-bold text-slate-600">
                            {data.rating} / ৫.০ স্টার
                        </span>
                    </div>
                </div>

                {/* Review Text */}
                <div>
                    <label htmlFor="review" className="block text-xs font-semibold text-slate-700">
                        আপনার মতামত ও মন্তব্য
                    </label>
                    <textarea
                        id="review"
                        rows={4}
                        required
                        value={data.review}
                        onChange={(e) => setData('review', e.target.value)}
                        placeholder="শিক্ষকের শিক্ষাদান পদ্ধতি, আন্তরিকতা ও অভিজ্ঞতা সম্পর্কে লিখুন..."
                        className="mt-1 w-full rounded-2xl border border-slate-200 p-3 text-xs leading-relaxed focus:border-[#1A2E2F] focus:ring-1 focus:ring-[#1A2E2F]"
                    />
                    {errors.review && (
                        <p className="mt-1 text-[11px] text-rose-600">{errors.review}</p>
                    )}
                </div>

                {/* Submit */}
                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn-primary text-xs !py-2.5"
                    >
                        {processing ? 'জমা হচ্ছে...' : 'রিভিউ জমা দিন'}
                    </button>
                </div>
            </form>
        </div>
    );
}
