import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Cohorts({ cohorts, teachers }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [showCreateModal, setShowCreateModal] = useState(false);

    const form = useForm({
        name: '',
        academic_year: '1447-1448 AH',
        head_teacher_id: '',
        room_number: '',
        description: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        form.post(`/org/${subdomain}/cohorts`, {
            onSuccess: () => {
                setShowCreateModal(false);
                form.reset();
            },
        });
    };

    return (
        <OrgLayout title="শ্রেণি ও হালাকা ব্যবস্থাপনা">
            <Head title="শ্রেণি ও হালাকা ব্যবস্থাপনা" />

            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">শ্রেণি, জামাত ও হালাকা সমূহ</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        মাদ্রাসার জামাত, মক্তবের শিফট কিংবা হিফজ হালাকার শ্রেণিভিত্তিক শিক্ষার্থী ও সিলেবাস পরিচালনা।
                    </p>
                </div>

                <button
                    onClick={() => setShowCreateModal(true)}
                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                >
                    + নতুন শ্রেণি / হালাকা খুলুন
                </button>
            </div>

            {/* Cohorts Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {cohorts.data?.length === 0 ? (
                    <div className="col-span-full bg-white p-10 rounded-xl border border-slate-200 text-center text-slate-600 text-sm">
                        কোনো শ্রেণি বা হালাকা খোলা নেই। উপরের বাটন ক্লিক করে নতুন শ্রেণি তৈরি করুন।
                    </div>
                ) : (
                    cohorts.data?.map((cohort) => (
                        <div key={cohort.id} className="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
                            <div>
                                <div className="flex items-center justify-between mb-2">
                                    <span className="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {cohort.academic_year}
                                    </span>
                                    <span className="text-xs text-slate-700">
                                        রুম: <span className="font-semibold text-slate-700">{cohort.room_number || 'অনির্দিষ্ট'}</span>
                                    </span>
                                </div>

                                <h3 className="text-base font-bold text-slate-900 mb-1">{cohort.name}</h3>
                                {cohort.description && (
                                    <p className="text-xs text-slate-700 line-clamp-2 mb-3">{cohort.description}</p>
                                )}

                                <div className="p-2.5 bg-slate-50 rounded-lg text-xs text-slate-700 space-y-1 mt-3">
                                    <div>
                                        প্রধান মুদাররিস: <span className="font-medium text-slate-800">{cohort.head_teacher?.name || 'নির্ধারণ করা হয়নি'}</span>
                                    </div>
                                    <div className="flex items-center space-x-3 rtl:space-x-reverse">
                                        <span>শিক্ষার্থী: <strong className="text-slate-800">{cohort.students_count || 0}</strong> জন</span>
                                        <span>•</span>
                                        <span>পাঠ্য কোর্স: <strong className="text-slate-800">{cohort.courses_count || 0}</strong> টি</span>
                                    </div>
                                </div>
                            </div>

                            <div className="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span className="text-emerald-800 font-medium">সক্রিয় শ্রেণি</span>
                                <span className="text-slate-600">আইডি: #{cohort.id}</span>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Modal: Create Cohort */}
            {showCreateModal && (
                <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-base font-bold text-slate-800">নতুন শ্রেণি / হালাকা যোগ</h3>
                            <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4 text-xs">
                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">শ্রেণি / হালাকার নাম *</label>
                                <input
                                    type="text"
                                    required
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    placeholder="উদা: হিফজুল কুরআন হালাকা (গ্রুপ ক)"
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">শিক্ষাবর্ষ / সেশন</label>
                                    <input
                                        type="text"
                                        value={form.data.academic_year}
                                        onChange={(e) => form.setData('academic_year', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">রুম / কক্ষ নম্বর</label>
                                    <input
                                        type="text"
                                        value={form.data.room_number}
                                        onChange={(e) => form.setData('room_number', e.target.value)}
                                        placeholder="১০২ / মসজিদ হল"
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">প্রধান শিক্ষক / নিগরান</label>
                                <select
                                    value={form.data.head_teacher_id}
                                    onChange={(e) => form.setData('head_teacher_id', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option value="">নির্বাচন করুন</option>
                                    {teachers?.map((t) => (
                                        <option key={t.id} value={t.id}>{t.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">বিবরণ / নিয়মাবলী</label>
                                <textarea
                                    rows="3"
                                    value={form.data.description}
                                    onChange={(e) => form.setData('description', e.target.value)}
                                    placeholder="হালাকার সময়সূচী ও অন্যান্য নিয়ম..."
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                ></textarea>
                            </div>

                            <div className="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowCreateModal(false)}
                                    className="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg"
                                >
                                    {form.processing ? 'তৈরি হচ্ছে...' : 'শ্রেণি তৈরি করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </OrgLayout>
    );
}
