import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Exams({ exams, cohorts }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [showCreateModal, setShowCreateModal] = useState(false);

    const form = useForm({
        title: '',
        cohort_id: '',
        exam_type: 'quiz_mcq',
        duration_minutes: 60,
        total_marks: 100,
        pass_marks: 40,
        randomize_questions: true,
        description: '',
        question_bank_text: `[
  {
    "id": 1,
    "question": "কুরআন মজিদে সর্বমোট কতটি সূরা রয়েছে?",
    "options": ["১১২", "১১৪", "১১৩", "১১৫"],
    "correct_answer": "১১৪",
    "points": 50
  },
  {
    "id": 2,
    "question": "ইসলামের প্রথম স্তম্ভ কোনটি?",
    "options": ["সালাত", "ঈমান/কালিমা", "সিয়াম", "যাকাত"],
    "correct_answer": "ঈমান/কালিমা",
    "points": 50
  }
]`,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        let questionBankParsed = [];
        try {
            questionBankParsed = JSON.parse(form.data.question_bank_text);
        } catch (err) {
            alert('প্রশ্নব্যাংক JSON ফরম্যাটে ত্রুটি রয়েছে।');
            return;
        }

        form.transform((data) => ({
            ...data,
            question_bank: questionBankParsed,
        })).post(`/org/${subdomain}/exams`, {
            onSuccess: () => {
                setShowCreateModal(false);
                form.reset();
            },
        });
    };

    return (
        <OrgLayout title="পরীক্ষা ও প্রশ্নব্যাংক">
            <Head title="পরীক্ষা ও প্রশ্নব্যাংক" />

            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">পরীক্ষা ও প্রশ্নব্যাংক মূল্যায়ন</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        অনলাইন MCQ, হিফজ মৌখিক পরীক্ষা ও লিখিত পরীক্ষার স্বয়ংক্রিয় ও শিক্ষক-নিয়ন্ত্রিত গ্রেডিং।
                    </p>
                </div>

                <button
                    onClick={() => setShowCreateModal(true)}
                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                >
                    + নতুন পরীক্ষা তৈরি করুন
                </button>
            </div>

            {/* Exams Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                        <tr>
                            <th className="px-5 py-3">পরীক্ষার শিরোনাম</th>
                            <th className="px-5 py-3">শ্রেণি / হালাকা</th>
                            <th className="px-5 py-3">ধরন</th>
                            <th className="px-5 py-3">পূর্ণমান / পাস নম্বর</th>
                            <th className="px-5 py-3">সময়সীমা</th>
                            <th className="px-5 py-3">জমা হওয়া উত্তর</th>
                            <th className="px-5 py-3">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {exams.data?.length === 0 ? (
                            <tr>
                                <td colSpan="7" className="text-center py-10 text-slate-600">
                                    এখনও কোনো পরীক্ষা তৈরি করা হয়নি।
                                </td>
                            </tr>
                        ) : (
                            exams.data?.map((exam) => (
                                <tr key={exam.id} className="hover:bg-slate-50 transition">
                                    <td className="px-5 py-3.5 font-bold text-slate-900">
                                        {exam.title}
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-700">
                                        {exam.cohort?.name || 'উন্মুক্ত'}
                                    </td>
                                    <td className="px-5 py-3.5">
                                        <span className="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold">
                                            {exam.exam_type === 'quiz_mcq' ? 'MCQ কুইজ' : (exam.exam_type === 'oral_hifz' ? 'মৌখিক হিফজ' : 'লিখিত')}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-700">
                                        {exam.total_marks} / পাস: {exam.pass_marks}
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-700">
                                        {exam.duration_minutes} মিনিট
                                    </td>
                                    <td className="px-5 py-3.5 font-semibold text-emerald-800">
                                        {exam.submissions_count || 0} টি খাতা
                                    </td>
                                    <td className="px-5 py-3.5">
                                        <span className="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold capitalize">
                                            {exam.status}
                                        </span>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* Modal: Create Exam */}
            {showCreateModal && (
                <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-base font-bold text-slate-800">নতুন পরীক্ষা ও প্রশ্নব্যাংক</h3>
                            <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4 text-xs">
                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">পরীক্ষার নাম *</label>
                                <input
                                    type="text"
                                    required
                                    value={form.data.title}
                                    onChange={(e) => form.setData('title', e.target.value)}
                                    placeholder="উদা: কুরআন হিফজ অর্ধ-বার্ষিক পরীক্ষা"
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">শ্রেণি / হালাকা</label>
                                    <select
                                        value={form.data.cohort_id}
                                        onChange={(e) => form.setData('cohort_id', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="">সকলের জন্য / অনির্ধারিত</option>
                                        {cohorts?.map((c) => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">পরীক্ষার ধরন</label>
                                    <select
                                        value={form.data.exam_type}
                                        onChange={(e) => form.setData('exam_type', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="quiz_mcq">MCQ কুইজ (স্বয়ংক্রিয় গ্রেডিং)</option>
                                        <option value="oral_hifz">মৌখিক হিফজ পরীক্ষা</option>
                                        <option value="written">লিখিত পরীক্ষা</option>
                                        <option value="hybrid">হাইব্রিড</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-3">
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">সময় (মিনিট)</label>
                                    <input
                                        type="number"
                                        value={form.data.duration_minutes}
                                        onChange={(e) => form.setData('duration_minutes', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">পূর্ণমান</label>
                                    <input
                                        type="number"
                                        value={form.data.total_marks}
                                        onChange={(e) => form.setData('total_marks', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">পাস নম্বর</label>
                                    <input
                                        type="number"
                                        value={form.data.pass_marks}
                                        onChange={(e) => form.setData('pass_marks', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">প্রশ্নব্যাংক (JSON ফরম্যাট)</label>
                                <textarea
                                    rows="6"
                                    value={form.data.question_bank_text}
                                    onChange={(e) => form.setData('question_bank_text', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg font-mono text-xs focus:ring-emerald-500 focus:border-emerald-500"
                                ></textarea>
                            </div>

                            <div className="flex items-center space-x-2 rtl:space-x-reverse">
                                <input
                                    type="checkbox"
                                    id="rand"
                                    checked={form.data.randomize_questions}
                                    onChange={(e) => form.setData('randomize_questions', e.target.checked)}
                                    className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <label htmlFor="rand" className="text-slate-700 font-medium">প্রশ্ন র্যান্ডমাইজেশন চালু রাখুন</label>
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
                                    {form.processing ? 'তৈরি হচ্ছে...' : 'পরীক্ষা প্রকাশ করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </OrgLayout>
    );
}
