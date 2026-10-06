import React, { useState } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Attendance({ cohorts, selected_cohort_id, selected_date, selected_session, students, summary }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [attendanceState, setAttendanceState] = useState(
        students.map((s) => ({
            user_id: s.user_id,
            status: s.status || 'present',
            remarks: s.remarks || '',
        }))
    );

    const form = useForm({
        cohort_id: selected_cohort_id,
        date: selected_date,
        session_name: selected_session,
        attendance: attendanceState,
    });

    const handleFilterChange = (cohortId, date, session) => {
        router.get(`/org/${subdomain}/attendance`, {
            cohort_id: cohortId,
            date: date,
            session: session,
        }, { preserveState: false });
    };

    const setStatusForUser = (userId, status) => {
        const updated = attendanceState.map((item) =>
            item.user_id === userId ? { ...item, status } : item
        );
        setAttendanceState(updated);
        form.setData('attendance', updated);
    };

    const setRemarksForUser = (userId, remarks) => {
        const updated = attendanceState.map((item) =>
            item.user_id === userId ? { ...item, remarks } : item
        );
        setAttendanceState(updated);
        form.setData('attendance', updated);
    };

    const markAllPresent = () => {
        const updated = attendanceState.map((item) => ({ ...item, status: 'present' }));
        setAttendanceState(updated);
        form.setData('attendance', updated);
    };

    const handleSave = (e) => {
        e.preventDefault();
        form.post(`/org/${subdomain}/attendance`, {
            preserveScroll: true,
        });
    };

    return (
        <OrgLayout title="দৈনিক হাজিরা ও উপস্থিতি খাতা">
            <Head title="দৈনিক হাজিরা ও উপস্থিতি খাতা" />

            {/* Header & Controls */}
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">দৈনিক হাজিরা রেজিস্টার</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        মাদ্রাসার শ্রেণি বা মক্তব হালাকার সেশনভিত্তিক উপস্থিতি নিবন্ধন।
                    </p>
                </div>

                <div className="flex items-center space-x-2 rtl:space-x-reverse">
                    <button
                        type="button"
                        onClick={markAllPresent}
                        className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg transition border border-slate-300"
                    >
                        ✓ সবাইকে উপস্থিত করুন
                    </button>
                    <button
                        type="button"
                        onClick={handleSave}
                        disabled={form.processing || students.length === 0}
                        className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                    >
                        {form.processing ? 'সংরক্ষণ হচ্ছে...' : 'হাজিরা সংরক্ষণ করুন'}
                    </button>
                </div>
            </div>

            {/* Selector Filters */}
            <div className="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-xs grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label className="block text-xs font-semibold text-slate-700 mb-1">শ্রেণি / হালাকা</label>
                    <select
                        value={selected_cohort_id || ''}
                        onChange={(e) => handleFilterChange(e.target.value, selected_date, selected_session)}
                        className="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        {cohorts.map((c) => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-xs font-semibold text-slate-700 mb-1">তারিখ</label>
                    <input
                        type="date"
                        value={selected_date}
                        onChange={(e) => handleFilterChange(selected_cohort_id, e.target.value, selected_session)}
                        className="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                    />
                </div>

                <div>
                    <label className="block text-xs font-semibold text-slate-700 mb-1">সেশন / বেলা</label>
                    <select
                        value={selected_session}
                        onChange={(e) => handleFilterChange(selected_cohort_id, selected_date, e.target.value)}
                        className="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        <option value="daily">দৈনিক (সাধারণ)</option>
                        <option value="fajr_halqa">ফজর হালাকা (হিফজ)</option>
                        <option value="morning">সকাল শিফট</option>
                        <option value="evening">আসর/মাগরিব মক্তব</option>
                    </select>
                </div>
            </div>

            {/* Attendance Summary Badges */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div className="bg-white p-3 rounded-lg border border-slate-200 text-center">
                    <div className="text-xs text-slate-700">মোট শিক্ষার্থী</div>
                    <div className="text-lg font-bold text-slate-900">{summary.total}</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-emerald-200 bg-emerald-50/30 text-center">
                    <div className="text-xs text-emerald-800">উপস্থিত</div>
                    <div className="text-lg font-bold text-emerald-800">{summary.present}</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-red-200 bg-red-50/30 text-center">
                    <div className="text-xs text-red-800">অনুপস্থিত</div>
                    <div className="text-lg font-bold text-red-800">{summary.absent}</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-amber-200 bg-amber-50/30 text-center">
                    <div className="text-xs text-amber-800">বিলম্বে / ছুটি</div>
                    <div className="text-lg font-bold text-amber-800">{summary.late}</div>
                </div>
            </div>

            {/* Students Attendance Register Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                        <tr>
                            <th className="px-5 py-3">রোল</th>
                            <th className="px-5 py-3">শিক্ষার্থী নাম</th>
                            <th className="px-5 py-3 text-center">উপস্থিতি স্ট্যাটাস</th>
                            <th className="px-5 py-3">মন্তব্য</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {students.length === 0 ? (
                            <tr>
                                <td colSpan="4" className="text-center py-10 text-slate-600">
                                    এই শ্রেণিতে কোনো শিক্ষার্থী অন্তর্ভুক্ত নেই।
                                </td>
                            </tr>
                        ) : (
                            students.map((student) => {
                                const state = attendanceState.find((item) => item.user_id === student.user_id) || { status: 'present', remarks: '' };
                                return (
                                    <tr key={student.user_id} className="hover:bg-slate-50 transition">
                                        <td className="px-5 py-3.5 font-bold text-slate-700">
                                            {student.roll_number || '—'}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <div className="font-semibold text-slate-900">{student.name}</div>
                                            <div className="text-[11px] text-slate-700">{student.email}</div>
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <div className="flex items-center justify-center space-x-1.5 rtl:space-x-reverse">
                                                {[
                                                    { key: 'present', label: 'উপস্থিত', color: 'bg-emerald-600 text-white' },
                                                    { key: 'absent', label: 'অনুপস্থিত', color: 'bg-red-600 text-white' },
                                                    { key: 'late', label: 'বিলম্বে', color: 'bg-amber-500 text-white' },
                                                    { key: 'excused', label: 'ছুটি', color: 'bg-blue-600 text-white' },
                                                ].map((opt) => (
                                                    <button
                                                        key={opt.key}
                                                        type="button"
                                                        onClick={() => setStatusForUser(student.user_id, opt.key)}
                                                        className={`px-2.5 py-1 text-xs font-semibold rounded-md transition ${
                                                            state.status === opt.key
                                                                ? opt.color
                                                                : 'bg-slate-100 text-slate-800 hover:bg-slate-200'
                                                        }`}
                                                    >
                                                        {opt.label}
                                                    </button>
                                                ))}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <input
                                                type="text"
                                                value={state.remarks}
                                                onChange={(e) => setRemarksForUser(student.user_id, e.target.value)}
                                                placeholder="মন্তব্য (ঐচ্ছিক)"
                                                className="w-full text-xs px-2.5 py-1 border border-slate-200 rounded-md focus:ring-emerald-500 focus:border-emerald-500"
                                            />
                                        </td>
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>
        </OrgLayout>
    );
}
