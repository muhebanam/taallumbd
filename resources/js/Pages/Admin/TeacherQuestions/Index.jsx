import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';
import Pagination from '../../../Components/Pagination';

export default function QuestionsIndex({ questions }) {
    const [answeringId, setAnsweringId] = useState(null);
    const [answerText, setAnswerText] = useState('');

    const handleAnswerSubmit = (e, qId) => {
        e.preventDefault();
        router.post(`/admin/teacher-questions/${qId}/answer`, {
            answer_body: answerText
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setAnsweringId(null);
                setAnswerText('');
            }
        });
    };

    const handleReject = (qId) => {
        if (confirm('আপনি কি নিশ্চিত যে এই প্রশ্নটি বাতিল করতে চান?')) {
            router.post(`/admin/teacher-questions/${qId}/reject`, {}, { preserveScroll: true });
        }
    };

    return (
        <DashboardLayout title="শিক্ষকদের প্রশ্নোত্তর মডারেশন">
            <Head title="প্রশ্নোত্তর মডারেশন" />

            <div className="card overflow-x-auto border border-slate-150 rounded-2xl bg-white shadow-sm">
                <table className="w-full text-left text-sm border-collapse">
                    <thead className="bg-[#102526] text-white">
                        <tr>
                            <th className="px-4 py-3 text-right">বিষয় ও প্রশ্ন</th>
                            <th className="px-4 py-3">শিক্ষক</th>
                            <th className="px-4 py-3">প্রশ্নকর্তা</th>
                            <th className="px-4 py-3 text-center">গোপনীয়তা</th>
                            <th className="px-4 py-3 text-center">স্ট্যাটাস</th>
                            <th className="px-4 py-3 text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {questions.data.length > 0 ? (
                            questions.data.map((q) => (
                                <tr key={q.id} className="hover:bg-slate-50/50">
                                    <td className="px-4 py-3 text-right max-w-md">
                                        <h5 className="font-bold text-slate-800 text-sm">বিষয়: {q.subject}</h5>
                                        <p className="text-xs text-slate-500 mt-1">{q.question_body}</p>
                                        
                                        {/* Answer input field */}
                                        {answeringId === q.id ? (
                                            <form onSubmit={(e) => handleAnswerSubmit(e, q.id)} className="mt-3 space-y-2">
                                                <textarea
                                                    value={answerText}
                                                    onChange={e => setAnswerText(e.target.value)}
                                                    rows="3"
                                                    placeholder="প্রশ্নের উত্তর লিখুন..."
                                                    className="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-emerald-500"
                                                    required
                                                ></textarea>
                                                <div className="flex gap-2 justify-end">
                                                    <button 
                                                        type="button" 
                                                        onClick={() => setAnsweringId(null)}
                                                        className="rounded bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600 hover:bg-slate-200"
                                                    >
                                                        বাতিল
                                                    </button>
                                                    <button 
                                                        type="submit"
                                                        className="rounded bg-emerald-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-emerald-700"
                                                    >
                                                        উত্তর প্রকাশ করুন
                                                    </button>
                                                </div>
                                            </form>
                                        ) : q.answer_body ? (
                                            <div className="mt-2.5 bg-emerald-50/30 border border-emerald-50 rounded-lg p-2.5 text-xs text-slate-700">
                                                <span className="font-bold text-emerald-800 text-[10px] block uppercase">উত্তর:</span>
                                                <p className="mt-0.5">{q.answer_body}</p>
                                            </div>
                                        ) : null}
                                    </td>
                                    <td className="px-4 py-3 font-semibold text-slate-700">{q.teacher?.name}</td>
                                    <td className="px-4 py-3 text-xs text-slate-500 font-semibold">{q.user?.name}</td>
                                    <td className="px-4 py-3 text-center">
                                        <span className={`inline-block rounded-md border px-2 py-0.5 text-[10px] font-bold ${
                                            q.is_private 
                                                ? 'bg-purple-50 border-purple-100 text-purple-700' 
                                                : 'bg-slate-50 border-slate-200 text-slate-500'
                                        }`}>
                                            {q.is_private ? 'গোপন' : 'পাবলিক'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <span className={`inline-block rounded-md border px-2 py-0.5 text-[10px] font-bold ${
                                            q.status === 'published' || q.status === 'answered'
                                                ? 'bg-emerald-50 border-emerald-100 text-emerald-800'
                                                : q.status === 'pending'
                                                ? 'bg-amber-50 border-amber-100 text-amber-800'
                                                : 'bg-red-50 border-red-100 text-red-800'
                                        }`}>
                                            {q.status === 'published' || q.status === 'answered' ? 'উত্তরিত' : q.status === 'pending' ? 'পেন্ডিং' : 'বাতিল'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="flex items-center justify-center gap-2">
                                            {q.status === 'pending' && (
                                                <>
                                                    <button
                                                        onClick={() => {
                                                            setAnsweringId(q.id);
                                                            setAnswerText(q.answer_body ?? '');
                                                        }}
                                                        className="text-xs font-bold text-emerald-600 hover:underline"
                                                    >
                                                        উত্তর দিন
                                                    </button>
                                                    <button
                                                        onClick={() => handleReject(q.id)}
                                                        className="text-xs font-bold text-red-500 hover:underline"
                                                    >
                                                        বাতিল
                                                    </button>
                                                </>
                                            )}
                                            {q.status !== 'pending' && (
                                                <button
                                                    onClick={() => {
                                                        setAnsweringId(q.id);
                                                        setAnswerText(q.answer_body ?? '');
                                                    }}
                                                    className="text-xs font-bold text-blue-600 hover:underline"
                                                >
                                                    সম্পাদনা
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="6" className="px-4 py-12 text-center text-slate-400 font-semibold">
                                    কোনো প্রশ্ন পাওয়া যায়নি।
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-6 flex justify-center">
                <Pagination links={questions.links} />
            </div>
        </DashboardLayout>
    );
}
