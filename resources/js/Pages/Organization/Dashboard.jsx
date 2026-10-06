import React from 'react';
import { Head, Link } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Dashboard({ organization, stats, recent_cohorts, recent_exams }) {
    const subdomain = organization.subdomain;

    return (
        <OrgLayout title={`${organization.name} - ড্যাশবোর্ড`}>
            <Head title={`${organization.name} - প্রতিষ্ঠান ড্যাশবোর্ড`} />

            {/* Welcome & Top Banner */}
            <div className="bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900 rounded-2xl p-6 text-white shadow-md mb-8">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div className="inline-flex items-center space-x-2 rtl:space-x-reverse px-2.5 py-1 rounded-full bg-emerald-700/60 text-emerald-200 text-xs font-semibold mb-2">
                            <span>প্ল্যান: {organization.plan}</span>
                            <span>•</span>
                            <span>{organization.type === 'madrasah' ? 'কওমি/মাদ্রাসা সংস্করণ' : 'ইনস্টিটিউট LMS'}</span>
                        </div>
                        <h2 className="text-2xl font-bold tracking-tight">{organization.name} এ স্বাগতম</h2>
                        <p className="text-emerald-100 text-sm mt-1">
                            আপনার প্রতিষ্ঠান, শ্রেণি, শিক্ষক, শিক্ষার্থী ও ইসলামিক পরীক্ষা ব্যবস্থাপনা প্যানেল।
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Link
                            href={`/org/${subdomain}/members`}
                            className="px-4 py-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold text-xs sm:text-sm rounded-lg shadow-sm transition"
                        >
                            + নতুন শিক্ষার্থী যোগ
                        </Link>
                        <Link
                            href={`/org/${subdomain}/attendance`}
                            className="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-medium text-xs sm:text-sm rounded-lg border border-emerald-500 transition"
                        >
                            আজকের হাজিরা গ্রহণ
                        </Link>
                    </div>
                </div>
            </div>

            {/* Metrics Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                {/* Students */}
                <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-700">মোট শিক্ষার্থী</span>
                        <span className="p-2 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold">তালিবুল ইলম</span>
                    </div>
                    <div className="text-3xl font-extrabold text-slate-900">{stats.total_students}</div>
                    <div className="text-xs text-slate-700 mt-2">
                        মুদাররিস/শিক্ষক: <span className="font-semibold text-slate-800">{stats.total_teachers}</span> জন
                    </div>
                </div>

                {/* Seat Capacity */}
                <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-700">সিট ব্যবহার ও কোটা</span>
                        <span className="text-xs font-semibold text-emerald-800">{stats.seat_usage_percentage}% ব্যবহৃত</span>
                    </div>
                    <div className="text-3xl font-extrabold text-slate-900">
                        {stats.used_seats} <span className="text-base font-normal text-slate-600">/ {stats.seat_limit}</span>
                    </div>
                    <div className="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                        <div
                            className={`h-2 rounded-full ${stats.seat_usage_percentage > 90 ? 'bg-amber-500' : 'bg-emerald-600'}`}
                            style={{ width: `${Math.min(100, stats.seat_usage_percentage)}%` }}
                        ></div>
                    </div>
                </div>

                {/* Today's Attendance */}
                <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-700">আজকের হাজিরা হার</span>
                        <span className="p-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold">দৈনিক রেকর্ড</span>
                    </div>
                    <div className="text-3xl font-extrabold text-slate-900">{stats.attendance_rate}%</div>
                    <div className="text-xs text-slate-700 mt-2">
                        আজকের মোট রেকর্ডকৃত: <span className="font-semibold text-slate-800">{stats.today_attendance_marked}</span> জন
                    </div>
                </div>

                {/* Active Cohorts */}
                <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-700">সক্রিয় শ্রেণি / হালাকা</span>
                        <span className="p-2 bg-purple-50 text-purple-700 rounded-lg text-xs font-bold">কোহর্ট</span>
                    </div>
                    <div className="text-3xl font-extrabold text-slate-900">{stats.active_cohorts}</div>
                    <div className="text-xs text-slate-700 mt-2">
                        অভিভাবক সক্রিয়: <span className="font-semibold text-slate-800">{stats.total_guardians}</span> জন
                    </div>
                </div>
            </div>

            {/* Two Column Section: Cohorts & Exams */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Cohorts / Halaqat List */}
                <div className="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-bold text-slate-800">শ্রেণি / হালাকা তালিকা</h3>
                        <Link
                            href={`/org/${subdomain}/cohorts`}
                            className="text-xs font-medium text-emerald-800 hover:text-emerald-900"
                        >
                            সকল দেখুন →
                        </Link>
                    </div>

                    {recent_cohorts?.length === 0 ? (
                        <div className="text-center py-8 text-slate-600 text-sm">
                            এখনও কোনো শ্রেণি বা হালাকা খোলা হয়নি।
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {recent_cohorts?.map((c) => (
                                <div key={c.id} className="p-3.5 rounded-lg border border-slate-100 hover:border-emerald-200 bg-slate-50/50 flex items-center justify-between">
                                    <div>
                                        <div className="font-semibold text-slate-800 text-sm">{c.name}</div>
                                        <div className="text-xs text-slate-700">
                                            শিক্ষাবর্ষ: {c.academic_year} • রুম: {c.room_number || 'অনির্ধারিত'}
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-3 rtl:space-x-reverse text-xs">
                                        <span className="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-semibold">
                                            {c.students_count || 0} শিক্ষার্থী
                                        </span>
                                        <span className="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-semibold">
                                            {c.courses_count || 0} কোর্স
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Recent Exams */}
                <div className="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-bold text-slate-800">পরীক্ষা ও মূল্যায়ন কার্যক্রম</h3>
                        <Link
                            href={`/org/${subdomain}/exams`}
                            className="text-xs font-medium text-emerald-800 hover:text-emerald-900"
                        >
                            পরীক্ষা প্যানেল →
                        </Link>
                    </div>

                    {recent_exams?.length === 0 ? (
                        <div className="text-center py-8 text-slate-600 text-sm">
                            কোনো পরীক্ষা রেকর্ড পাওয়া যায়নি।
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {recent_exams?.map((exam) => (
                                <div key={exam.id} className="p-3.5 rounded-lg border border-slate-100 hover:border-emerald-200 bg-slate-50/50 flex items-center justify-between">
                                    <div>
                                        <div className="font-semibold text-slate-800 text-sm">{exam.title}</div>
                                        <div className="text-xs text-slate-700">
                                            ধরন: {exam.exam_type} • মোট নম্বর: {exam.total_marks} • সময়: {exam.duration_minutes} মিনিট
                                        </div>
                                    </div>
                                    <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 capitalize">
                                        {exam.status}
                                    </span>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </OrgLayout>
    );
}
