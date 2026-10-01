import React from 'react';
import { Link } from '@inertiajs/react';
import CourseCard from './CourseCard';

export default function FeaturedCourses({ courses = [] }) {
    return (
        <section className="py-16 sm:py-24 bg-white">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                            জনপ্রিয় কোর্সসমূহ
                        </span>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                            আপনার দ্বীনি জ্ঞানার্জনের যাত্রা শুরু করুন
                        </h2>
                        <p className="mt-2 text-base text-slate-600 font-bangla">
                            দক্ষ উস্তাযদের প্রণীত আধুনিক ও প্রাতিষ্ঠানিক কোর্সে যুক্ত হয়ে নিজেকে সমৃদ্ধ করুন।
                        </p>
                    </div>

                    <Link
                        href="/courses"
                        className="inline-flex items-center gap-2 text-sm font-bold text-[#1A2E2F] hover:text-emerald-700 transition"
                    >
                        <span>সকল কোর্স দেখুন</span>
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                {/* Course Grid */}
                <div className="mt-12">
                    {courses.length > 0 ? (
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {courses.map((course) => (
                                <CourseCard key={course.id} course={course} />
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-slate-300 p-12 text-center bg-slate-50">
                            <span className="text-4xl">📚</span>
                            <h3 className="mt-3 text-lg font-bold text-slate-800">শীঘ্রই নতুন কোর্স উন্মুক্ত হচ্ছে</h3>
                            <p className="text-sm text-slate-500 mt-1">আমাদের শিক্ষকমণ্ডলী নতুন পাঠ্যক্রম প্রস্তুত করছেন।</p>
                            <Link href="/courses" className="btn-primary mt-5 text-xs">কোর্স তালিকা দেখুন</Link>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
