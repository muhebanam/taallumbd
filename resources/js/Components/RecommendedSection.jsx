import React from 'react';
import { Link } from '@inertiajs/react';
import CourseCard from './CourseCard';

export default function RecommendedSection({
    title = 'আপনার জন্য প্রস্তাবিত কোর্স',
    subtitle = 'আপনার পছন্দ, পূর্ববর্তী শিক্ষা ও জনপ্রিয়তার ভিত্তিতে নির্বাচিত',
    badge = 'Recommended For You',
    courses = [],
    className = 'py-12 bg-[#0E1F20]',
    theme = 'dark'
}) {
    const list = Array.isArray(courses) ? courses : (courses && typeof courses === 'object' ? Object.values(courses) : []);
    if (!list || list.length === 0) return null;

    const isDark = theme === 'dark';

    return (
        <section className={`${className} border-y ${isDark ? 'border-[#254244]' : 'border-slate-200'}`}>
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <span className={`inline-block rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider ${
                            isDark 
                                ? 'bg-[#FFF99A]/15 text-[#FFF99A] border border-[#FFF99A]/30' 
                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        }`}>
                            {badge}
                        </span>
                        <h2 className={`mt-2 text-2xl sm:text-3xl font-extrabold font-bangla ${
                            isDark ? 'text-white' : 'text-[#142425]'
                        }`}>
                            {title}
                        </h2>
                        {subtitle && (
                            <p className={`mt-1 text-sm font-bangla ${
                                isDark ? 'text-gray-300' : 'text-slate-600'
                            }`}>
                                {subtitle}
                            </p>
                        )}
                    </div>

                    <Link
                        href="/courses"
                        className={`inline-flex items-center gap-1.5 text-xs font-bold transition ${
                            isDark ? 'text-[#FFF99A] hover:underline' : 'text-[#1A2E2F] hover:text-emerald-700'
                        }`}
                    >
                        <span>সকল কোর্স অন্বেষণ করুন &rarr;</span>
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {list.map((course) => (
                        <div key={course.id} className="relative group">
                            <CourseCard course={course} />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
