import React from 'react';
import { Link } from '@inertiajs/react';
import TeacherCard from './TeacherCard';

export default function TopScholars({ teachers = [] }) {
    const teacherList = Array.isArray(teachers) ? teachers : (teachers && typeof teachers === 'object' ? Object.values(teachers) : []);
    if (teacherList.length === 0) return null;

    return (
        <section className="py-16 sm:py-24 bg-white">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                            যোগ্য শিক্ষকবৃন্দ
                        </span>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                            বিজ্ঞ উলামায়ে কেরাম ও গবেষক পরিষদ
                        </h2>
                        <p className="mt-2 text-base text-slate-600 font-bangla">
                            দেশের স্বনামধন্য ইসলামিক স্কলার ও গবেষকদের প্রত্যক্ষ তত্ত্বাবধানে জ্ঞান অর্জন করুন।
                        </p>
                    </div>

                    <Link
                        href="/about/teachers"
                        className="inline-flex items-center gap-2 text-sm font-bold text-[#1A2E2F] hover:text-emerald-700 transition"
                    >
                        <span>সকল শিক্ষক দেখুন</span>
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                {/* Teachers Grid */}
                <div className="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {teacherList.map((teacher) => (
                        <TeacherCard key={teacher.id} teacher={teacher} />
                    ))}
                </div>
            </div>
        </section>
    );
}
