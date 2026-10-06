import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Guardian({ wards, selected_student_id, selected_ward, attendance_stats, recent_exams, grade_books }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const handleSelectWard = (wardId) => {
        router.get(`/org/${subdomain}/guardian`, { student_id: wardId }, { preserveState: false });
    };

    return (
        <OrgLayout title="অভিভাবক পোর্টাল">
            <Head title="অভিভাবক পোর্টাল - সন্তানের অগ্রগতি ও হাজিরা" />

            {/* Header */}
            <div className="bg-gradient-to-r from-emerald-800 to-teal-900 rounded-2xl p-6 text-white shadow-md mb-8">
                <h2 className="text-2xl font-bold tracking-tight">অভিভাবক পোর্টাল</h2>
                <p className="text-emerald-100 text-sm mt-1">
                    আপনার সন্তানের দৈনিক ক্লাসে উপস্থিতি, পরীক্ষার ফলাফল ও মুদাররিসের মূল্যায়ন পর্যবেক্ষণ করুন।
                </p>
            </div>

            {/* Ward Selector (if multiple children) */}
            {wards?.length > 1 && (
                <div className="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-xs flex items-center space-x-3 rtl:space-x-reverse">
                    <span className="text-xs font-semibold text-slate-700">সন্তান নির্বাচন করুন:</span>
                    <div className="flex space-x-2 rtl:space-x-reverse">
                        {wards.map((ward) => (
                            <button
                                key={ward.id}
                                onClick={() => handleSelectWard(ward.id)}
                                className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition ${
                                    selected_student_id === ward.id
                                        ? 'bg-emerald-800 text-white'
                                        : 'bg-slate-100 text-slate-800 hover:bg-slate-200'
                                }`}
                            >
                                {ward.name} ({ward.id_number || 'আইডি নাই'})
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {!selected_ward ? (
                <div className="bg-white p-12 rounded-xl border border-slate-200 text-center text-slate-600 text-sm">
                    কোনো শিক্ষার্থী প্রোফাইল আপনার অভিভাবক অ্যাকাউন্টের সাথে যুক্ত পাওয়া যায়নি। প্রতিষ্ঠানের এডমিনের সাথে যোগাযোগ করুন।
                </div>
            ) : (
                <div className="space-y-8">
                    {/* Attendance & Performance Metrics */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                            <div className="text-xs font-semibold text-slate-700 mb-1">শিক্ষার্থীর পরিচিতি</div>
                            <div className="text-lg font-bold text-slate-900">{selected_ward.name}</div>
                            <div className="text-xs text-slate-700 mt-1">আইডি / রোল: <strong className="text-slate-800">{selected_ward.id_number || '—'}</strong></div>
                        </div>

                        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                            <div className="text-xs font-semibold text-slate-700 mb-1">উপস্থিতি হার</div>
                            <div className="text-2xl font-extrabold text-emerald-800">
                                {attendance_stats?.rate || 0}%
                            </div>
                            <div className="text-xs text-slate-700 mt-1">
                                মোট ক্লাস: {attendance_stats?.total || 0} দিন | উপস্থিত: {attendance_stats?.present || 0} দিন
                            </div>
                        </div>

                        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                            <div className="text-xs font-semibold text-slate-700 mb-1">সাম্প্রতিক টার্ম গ্রেড</div>
                            <div className="text-xl font-bold text-teal-800">
                                {grade_books?.[0]?.overall_grade || 'মূল্যায়নাধীন'}
                            </div>
                            <div className="text-xs text-slate-700 mt-1">
                                সর্বশেষ মেধাক্রম: <strong className="text-slate-800">{grade_books?.[0]?.position_in_class ? `${grade_books[0].position_in_class}ম` : '—'}</strong>
                            </div>
                        </div>
                    </div>

                    {/* Report Cards / Gradebooks */}
                    <div className="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                        <h3 className="text-base font-bold text-slate-800 mb-4">প্রকাশিত রিপোর্ট কার্ড (টার্ম গ্রেড)</h3>
                        {grade_books?.length === 0 ? (
                            <div className="text-center py-6 text-slate-600 text-xs">
                                এখনও কোনো অফিসিয়াল রিপোর্ট কার্ড প্রকাশিত হয়নি।
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {grade_books?.map((gb) => (
                                    <div key={gb.id} className="p-4 rounded-xl border border-emerald-100 bg-emerald-50/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <div className="text-sm font-bold text-slate-900">{gb.term}</div>
                                            <div className="text-xs text-slate-700 mt-0.5">
                                                শ্রেণি: {gb.cohort?.name} • প্রাপ্ত নম্বর: {gb.obtained_marks} / {gb.total_marks} ({gb.overall_percentage}%)
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-3 rtl:space-x-reverse">
                                            <span className="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                {gb.overall_grade}
                                            </span>
                                            <span className="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                                                মেধাক্রম: {gb.position_in_class}ম
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Recent Exam Submissions */}
                    <div className="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                        <h3 className="text-base font-bold text-slate-800 mb-4">সাম্প্রতিক পরীক্ষার খাতা ও নম্বর</h3>
                        {recent_exams?.length === 0 ? (
                            <div className="text-center py-6 text-slate-600 text-xs">
                                কোনো পরীক্ষার রেকর্ড নেই।
                            </div>
                        ) : (
                            <div className="divide-y divide-slate-100">
                                {recent_exams?.map((sub) => (
                                    <div key={sub.id} className="py-3 flex items-center justify-between text-xs">
                                        <div>
                                            <div className="font-semibold text-slate-900">{sub.exam?.title}</div>
                                            <div className="text-[11px] text-slate-700">
                                                জমা: {sub.submitted_at ? new Date(sub.submitted_at).toLocaleDateString('bn-BD') : '—'}
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className="font-bold text-emerald-800">
                                                {sub.total_score} নম্বর ({sub.percentage}%)
                                            </div>
                                            <div className="text-[11px] text-slate-700 font-medium">
                                                {sub.grade || 'গ্রেড প্রক্রিয়াধীন'}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </OrgLayout>
    );
}
