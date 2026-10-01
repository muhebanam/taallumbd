import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

const STATUSES = [
    ['', 'সব ফাতাওয়া'],
    ['pending', 'অপেক্ষমাণ (Pending)'],
    ['answered', 'উত্তর দেওয়া (অপ্রকাশিত)'],
    ['published', 'প্রকাশিত (Published)'],
    ['rejected', 'প্রত্যাখ্যাত (Rejected)']
];

function AnswerForm({ fatwa, scholars = [], courses = [], onDone }) {
    const { data, setData, put, processing } = useForm({
        answer_body: fatwa.answer_body ?? '',
        references: fatwa.references ?? '',
        assigned_scholar_id: fatwa.assigned_scholar_id ?? fatwa.teacher_id ?? '',
        related_course_id: fatwa.related_course_id ?? '',
        status: fatwa.status === 'pending' ? 'published' : fatwa.status,
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/admin/fatawa/${fatwa.id}/answer`, { onSuccess: onDone });
    };

    return (
        <form onSubmit={submit} className="mt-4 space-y-4 rounded-2xl bg-gray-50 p-6 border border-gray-200">
            <div className="rounded-xl bg-amber-50/70 p-4 border border-amber-200 text-xs">
                <span className="font-bold text-amber-900 block mb-1">প্রশ্নকারীর পূর্ণ বিবরণ:</span>
                <p className="text-gray-800 whitespace-pre-line leading-relaxed">{fatwa.question_body}</p>
                <div className="mt-2 flex gap-4 text-gray-500">
                    <span>ইমেইল: {fatwa.questioner_email}</span>
                    {fatwa.questioner_phone && <span>ফোন: {fatwa.questioner_phone}</span>}
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div>
                    <label className="block text-xs font-bold text-gray-700">অনুমোদনকারী মুফতী / শিক্ষক</label>
                    <select
                        className="mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-[#102526] focus:ring-1 focus:ring-[#102526]"
                        value={data.assigned_scholar_id}
                        onChange={(e) => setData('assigned_scholar_id', e.target.value)}
                    >
                        <option value="">নির্বাচন করুন</option>
                        {scholars.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.title_prefix} {s.user?.name} {s.designation ? `(${s.designation})` : ''}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-xs font-bold text-gray-700">সম্পর্কিত একাডেমি কোর্স লিংক (ঐচ্ছিক)</label>
                    <select
                        className="mt-1 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-[#102526] focus:ring-1 focus:ring-[#102526]"
                        value={data.related_course_id}
                        onChange={(e) => setData('related_course_id', e.target.value)}
                    >
                        <option value="">কোনো কোর্স নেই</option>
                        {courses.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.title}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div>
                <label className="block text-xs font-bold text-gray-700">শরয়ী উত্তর *</label>
                <textarea
                    rows={8}
                    required
                    className="mt-1 w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 focus:border-[#102526] focus:ring-1 focus:ring-[#102526]"
                    placeholder="তাহকীকপূর্ণ উত্তর বিস্তারিত লিখুন..."
                    value={data.answer_body}
                    onChange={(e) => setData('answer_body', e.target.value)}
                />
            </div>

            <div>
                <label className="block text-xs font-bold text-gray-700">দলীল ও কিতাবের হাওয়ালা (References / Daleel)</label>
                <textarea
                    rows={3}
                    className="mt-1 w-full rounded-xl border border-gray-300 bg-white p-3 font-amiri text-sm text-gray-900 focus:border-[#102526] focus:ring-1 focus:ring-[#102526]"
                    placeholder="উদা: [১] আল-হিদায়া: খণ্ড ১, পৃষ্ঠা ১২৩; [২] রদ্দুল মুহতার: খণ্ড ২, পৃষ্ঠা ৪৫"
                    value={data.references}
                    onChange={(e) => setData('references', e.target.value)}
                />
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4">
                <div className="flex items-center gap-3">
                    <label className="text-xs font-bold text-gray-700">স্ট্যাটাস:</label>
                    <select
                        className="rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-[#102526]"
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                    >
                        <option value="published">প্রকাশ করুন (ওয়েবসাইটে দৃশ্যমান)</option>
                        <option value="answered">উত্তর সংরক্ষণ (অপ্রকাশিত/ড্রাফট)</option>
                        <option value="rejected">প্রত্যাখ্যান করুন</option>
                        <option value="pending">অপেক্ষমাণ (Pending)</option>
                    </select>
                </div>

                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={onDone}
                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100"
                    >
                        বাতিল
                    </button>
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-[#102526] px-5 py-2 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                    >
                        সংরক্ষণ করুন
                    </button>
                </div>
            </div>
        </form>
    );
}

export default function AdminFatawa({ fatawa, filters = {}, scholars = [], courses = [] }) {
    const [openId, setOpenId] = useState(null);

    return (
        <DashboardLayout title="ফাতাওয়া ও দ্বীনি প্রশ্নোত্তর ব্যবস্থাপনা">
            <Head title="ফাতাওয়া ব্যবস্থাপনা — অ্যাডমিন প্যানেল" />

            {/* Filter Tabs */}
            <div className="mb-6 flex flex-wrap gap-2">
                {STATUSES.map(([s, label]) => (
                    <button
                        key={s}
                        onClick={() => router.get('/admin/fatawa', s ? { status: s } : {})}
                        className={`rounded-full px-4 py-1.5 text-xs font-bold transition ${(filters.status ?? '') === s
                            ? 'bg-[#102526] text-[#FFF99A] shadow'
                            : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
                        }`}
                    >
                        {label}
                    </button>
                ))}
            </div>

            {/* List Table / Cards */}
            <div className="rounded-3xl border border-gray-200 bg-white shadow-sm divide-y divide-gray-100">
                {fatawa.data && fatawa.data.length > 0 ? (
                    fatawa.data.map((f) => (
                        <div key={f.id} className="p-6 transition hover:bg-gray-50/50">
                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-bold ${
                                            f.status === 'published' ? 'bg-emerald-100 text-emerald-800' :
                                            f.status === 'answered' ? 'bg-blue-100 text-blue-800' :
                                            f.status === 'rejected' ? 'bg-rose-100 text-rose-800' :
                                            'bg-amber-100 text-amber-800'
                                        }`}>
                                            {f.status}
                                        </span>
                                        {f.category && (
                                            <span className="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] text-gray-600">
                                                {f.category.name}
                                            </span>
                                        )}
                                        {f.is_private && (
                                            <span className="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                                ব্যক্তিগত / গোপন
                                            </span>
                                        )}
                                    </div>
                                    <h3 className="text-base font-bold text-[#102526]">
                                        {f.question_title}
                                    </h3>
                                    <p className="text-xs text-gray-500">
                                        প্রশ্নকারী: <span className="font-medium text-gray-700">{f.questioner_name || 'সাধারণ ইউজার'}</span> • তারিখ: {new Date(f.created_at).toLocaleDateString('bn-BD')}
                                        {f.assigned_scholar && (
                                            <span className="ml-2 font-medium text-emerald-800">
                                                (নিয়োগপ্রাপ্ত: {f.assigned_scholar.user?.name})
                                            </span>
                                        )}
                                    </p>
                                </div>

                                <button
                                    onClick={() => setOpenId(openId === f.id ? null : f.id)}
                                    className="shrink-0 rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                                >
                                    {openId === f.id ? 'বন্ধ করুন' : f.answer_body ? 'উত্তর সম্পাদনা' : 'উত্তর দিন'}
                                </button>
                            </div>

                            {openId === f.id && (
                                <AnswerForm
                                    fatwa={f}
                                    scholars={scholars}
                                    courses={courses}
                                    onDone={() => setOpenId(null)}
                                />
                            )}
                        </div>
                    ))
                ) : (
                    <div className="p-12 text-center text-xs text-gray-500">
                        এই ফিল্টারে কোনো ফাতাওয়া পাওয়া যায়নি।
                    </div>
                )}
            </div>

            <div className="mt-6">
                <Pagination links={fatawa.links} />
            </div>
        </DashboardLayout>
    );
}
