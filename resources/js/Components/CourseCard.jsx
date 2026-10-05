import React from 'react';
import { Link } from '@inertiajs/react';

export default function CourseCard({ course }) {
    const isFree = course.is_free || Number(course.price) === 0;
    const rating = course.reviews_avg_rating ? parseFloat(course.reviews_avg_rating) : 0;

    return (
        <div className="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#1A2E2F]/40 hover:shadow-xl">
            {/* Thumbnail Box */}
            <div className="relative aspect-video w-full overflow-hidden bg-[#102526]">
                {course.thumbnail ? (
                    <img
                        src={course.thumbnail.startsWith('http') ? course.thumbnail : `/storage/${course.thumbnail}`}
                        alt={course.title}
                        width="640"
                        height="360"
                        loading="lazy"
                        decoding="async"
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#102526] to-[#1A2E2F] p-4 text-center">
                        <span className="font-bangla text-lg font-bold text-[#FFF99A]/80 line-clamp-2">
                            {course.title}
                        </span>
                    </div>
                )}

                {/* Floating Badges */}
                <div className="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    {course.category && (
                        <span className="rounded-lg bg-black/60 backdrop-blur-md px-2.5 py-1 text-xs font-semibold text-white">
                            {course.category.name}
                        </span>
                    )}
                    {course.status === 'coming_soon' && (
                        <span className="rounded-lg bg-amber-500/90 backdrop-blur-md px-2.5 py-1 text-xs font-bold text-white">
                            শীঘ্রই আসছে
                        </span>
                    )}
                </div>

                <div className="absolute top-3 right-3">
                    <span className={`rounded-lg px-2.5 py-1 text-xs font-bold backdrop-blur-md shadow-sm ${
                        isFree 
                            ? 'bg-emerald-600 text-white' 
                            : 'bg-[#FFF99A] text-[#102526]'
                    }`}>
                        {isFree ? 'ফ্রি কোর্স' : `৳ ${Number(course.price).toLocaleString('bn-BD')}`}
                    </span>
                </div>
            </div>

            {/* Content Body */}
            <div className="flex flex-1 flex-col p-5">
                {/* Level and Duration */}
                <div className="flex items-center justify-between text-xs text-slate-500 mb-2 font-medium">
                    <span className="flex items-center gap-1">
                        <span>📚</span> {course.level || 'সকল স্তরের'}
                    </span>
                    {course.duration && (
                        <span className="flex items-center gap-1">
                            <span>⏱</span> {course.duration}
                        </span>
                    )}
                </div>

                {/* Title */}
                <h3 className="text-base font-bold text-[#142425] line-clamp-2 group-hover:text-[#1A2E2F] transition-colors font-bangla">
                    <Link href={`/courses/${course.slug}`}>
                        {course.title}
                    </Link>
                </h3>

                {/* Instructor */}
                <div className="mt-2.5 flex items-center gap-2">
                    <div className="flex h-6 w-6 items-center justify-center rounded-full bg-[#1A2E2F]/10 text-xs font-bold text-[#1A2E2F]">
                        {course.instructor?.name ? course.instructor.name.charAt(0) : 'উ'}
                    </div>
                    <span className="text-xs font-medium text-slate-600 line-clamp-1">
                        {course.instructor?.name || 'আত-তাআল্লুম উস্তায পরিষদ'}
                    </span>
                </div>

                {/* Short Description */}
                <p className="mt-2.5 flex-1 text-xs text-slate-600 line-clamp-2 leading-relaxed">
                    {course.short_description}
                </p>

                {/* Meta Strip: Lessons and Rating */}
                <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <span className="flex items-center gap-1">
                        <svg className="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{course.lessons_count ?? 0} টি পাঠ</span>
                    </span>

                    <div className="flex items-center gap-1">
                        <span className="text-amber-500">★</span>
                        <span className="font-bold text-slate-700">
                            {rating > 0 ? rating.toFixed(1) : '৫.০'}
                        </span>
                    </div>
                </div>

                {/* Card Action CTA */}
                <div className="mt-4 pt-1">
                    <Link
                        href={`/courses/${course.slug}`}
                        className="flex w-full items-center justify-center rounded-xl bg-[#1A2E2F] py-2.5 text-xs font-bold text-white transition-all duration-200 hover:bg-[#102526] hover:shadow-md"
                    >
                        বিস্তারিত পাঠ্যক্রম দেখুন
                    </Link>
                </div>
            </div>
        </div>
    );
}
