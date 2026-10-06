import React, { useState } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Gradebook({ cohorts, selected_cohort_id, selected_term, grade_books }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [termInput, setTermInput] = useState(selected_term);

    const generateForm = useForm({
        cohort_id: selected_cohort_id,
        term: selected_term,
    });

    const handleFilterChange = (cohortId, term) => {
        router.get(`/org/${subdomain}/gradebook`, {
            cohort_id: cohortId,
            term: term,
        }, { preserveState: false });
    };

    const handleGenerate = (e) => {
        e.preventDefault();
        generateForm.transform(() => ({
            cohort_id: selected_cohort_id,
            term: termInput,
        })).post(`/org/${subdomain}/gradebook/generate`, {
            preserveScroll: true,
        });
    };

    return (
        <OrgLayout title="রিপোর্ট কার্ড ও গ্রেডবুক">
            <Head title="রিপোর্ট কার্ড ও গ্রেডবুক" />

            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">শ্রেণিভিত্তিক ফলাফল ও মেধাক্রম (রিপোর্ট কার্ড)</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        ঐতিহ্যবাহী ইসলামিক গ্রেডিং (মুমতায ممتاز, জায়্যিদ জিদ্দান جيد جداً, জায়্যিদ جيد, মাকবুল مقبول, রাসিব راسب) ও মেধাক্রম।
                    </p>
                </div>

                <button
                    onClick={handleGenerate}
                    disabled={generateForm.processing || !selected_cohort_id}
                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                >
                    {generateForm.processing ? 'হিসাব হচ্ছে...' : '⚡ ফলাফল হিসাব ও রিপোর্ট কার্ড জেনারেট'}
                </button>
            </div>

            {/* Filter and Term selectors */}
            <div className="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-xs flex flex-col sm:flex-row gap-4">
                <div className="flex-1">
                    <label className="block text-xs font-semibold text-slate-700 mb-1">শ্রেণি / হালাকা</label>
                    <select
                        value={selected_cohort_id || ''}
                        onChange={(e) => handleFilterChange(e.target.value, termInput)}
                        className="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        {cohorts.map((c) => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>
                </div>

                <div className="flex-1">
                    <label className="block text-xs font-semibold text-slate-700 mb-1">পরীক্ষার টার্ম বা সেশন</label>
                    <input
                        type="text"
                        value={termInput}
                        onChange={(e) => setTermInput(e.target.value)}
                        onBlur={() => handleFilterChange(selected_cohort_id, termInput)}
                        placeholder="উদা: ষান্মাসিক পরীক্ষা ১৪৪৭"
                        className="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                    />
                </div>
            </div>

            {/* Report Cards Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                        <tr>
                            <th className="px-5 py-3">মেধাক্রম (র‍্যাংক)</th>
                            <th className="px-5 py-3">শিক্ষার্থী নাম</th>
                            <th className="px-5 py-3">প্রাপ্ত নম্বর / মোট</th>
                            <th className="px-5 py-3">শতাংশ (%)</th>
                            <th className="px-5 py-3">ইসলামিক মূল্যায়ন গ্রেড</th>
                            <th className="px-5 py-3">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {grade_books.length === 0 ? (
                            <tr>
                                <td colSpan="6" className="text-center py-10 text-slate-600">
                                    এই টার্মে এখনও কোনো রিপোর্ট কার্ড তৈরি করা হয়নি। উপরের বাটন চাপুন।
                                </td>
                            </tr>
                        ) : (
                            grade_books.map((gb) => (
                                <tr key={gb.id} className="hover:bg-slate-50 transition">
                                    <td className="px-5 py-3.5">
                                        <span className={`inline-flex items-center justify-center w-7 h-7 rounded-full font-extrabold text-xs ${
                                            gb.position_in_class === 1 ? 'bg-amber-100 text-amber-800 border border-amber-300' :
                                            gb.position_in_class === 2 ? 'bg-slate-100 text-slate-800 border border-slate-300' :
                                            gb.position_in_class === 3 ? 'bg-orange-100 text-orange-800 border border-orange-300' :
                                            'bg-slate-50 text-slate-700'
                                        }`}>
                                            {gb.position_in_class}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3.5 font-bold text-slate-900">
                                        {gb.student?.name}
                                    </td>
                                    <td className="px-5 py-3.5 font-semibold text-slate-700">
                                        {gb.obtained_marks} / {gb.total_marks}
                                    </td>
                                    <td className="px-5 py-3.5 font-bold text-slate-900">
                                        {gb.overall_percentage}%
                                    </td>
                                    <td className="px-5 py-3.5">
                                        <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${
                                            gb.overall_percentage >= 90 ? 'bg-emerald-100 text-emerald-800' :
                                            gb.overall_percentage >= 80 ? 'bg-teal-100 text-teal-800' :
                                            gb.overall_percentage >= 65 ? 'bg-blue-100 text-blue-800' :
                                            gb.overall_percentage >= 50 ? 'bg-amber-100 text-amber-800' :
                                            'bg-red-100 text-red-800'
                                        }`}>
                                            {gb.overall_grade}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3.5">
                                        <span className="text-emerald-800 font-semibold">
                                            {gb.is_published ? 'প্রকাশিত' : 'খসড়া'}
                                        </span>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </OrgLayout>
    );
}
