import React from 'react';

export default function TeacherStats({ teacher, studentsCount = 0, className = '' }) {
    const stats = [
        { label: 'কোর্স', count: teacher.courses_count ?? 0, color: 'text-emerald-600 bg-emerald-50' },
        { label: 'শিক্ষার্থী', count: studentsCount || 0, color: 'text-indigo-600 bg-indigo-50', show: studentsCount > 0 },
        { label: 'প্রবন্ধ', count: teacher.articles_count ?? 0, color: 'text-blue-600 bg-blue-50' },
        { label: 'ফাতাওয়া', count: teacher.fatawa_count ?? 0, color: 'text-amber-600 bg-amber-50' },
        { label: 'প্রকাশনা', count: teacher.publications_count ?? 0, color: 'text-rose-600 bg-rose-50' },
        { label: 'ফলোয়ার', count: teacher.followers_count ?? 0, color: 'text-teal-600 bg-teal-50' },
    ];

    return (
        <div className={`grid grid-cols-3 gap-2 sm:grid-cols-6 ${className}`}>
            {stats.map((stat, idx) => {
                if (stat.show === false) return null;
                return (
                    <div key={idx} className="flex flex-col items-center justify-center rounded-lg border border-slate-100 p-2 text-center transition-all duration-300 hover:shadow-sm">
                        <span className="text-xl font-bold text-slate-800">{stat.count}</span>
                        <span className="text-[11px] font-medium text-slate-500 mt-0.5">{stat.label}</span>
                    </div>
                );
            })}
        </div>
    );
}
