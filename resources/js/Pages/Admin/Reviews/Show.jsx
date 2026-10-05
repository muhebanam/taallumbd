import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

const STATUS_LABELS = {
    draft: 'খসড়া (Draft)',
    in_review: 'সম্পাদকীয় রিভিউ (In Review)',
    scholar_review: 'স্কলার রিভিউ (Scholar Review)',
    approved: 'অনুমোদিত (Approved)',
    published: 'প্রকাশিত (Published)',
    rejected: 'প্রত্যাখ্যাত / সংশোধন প্রয়োজন (Rejected)',
};

export default function ReviewShow({ type, item, reviews, scholars, userRole, canApproveScholar, canEditorReview }) {
    const { data, setData, post, processing, errors } = useForm({
        to_status: item.status === 'in_review' ? 'scholar_review' : (item.status === 'scholar_review' ? 'approved' : 'published'),
        decision: '',
        notes: '',
        certified_by_scholar_id: item.certified_by_scholar_id || '',
    });

    const [activeTab, setActiveTab] = useState('details');

    const handleSubmit = (e) => {
        e.preventDefault();
        post(`/admin/reviews/${type}/${item.id}/decision`);
    };

    return (
        <DashboardLayout title={`রিভিউ: ${item.title || item.question_title}`}>
            <Head title={`রিভিউ — ${item.title || item.question_title}`} />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link
                        href="/admin/reviews"
                        className="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2"
                    >
                        ← রিভিউ কিউ-তে ফিরুন
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-[#142425] font-bangla">
                        {item.title || item.question_title}
                    </h1>
                    <div className="flex items-center gap-3 mt-1.5 text-xs text-slate-500">
                        <span>ধরণ: <strong className="text-slate-700 capitalize">{type}</strong></span>
                        <span>•</span>
                        <span>বর্তমান স্ট্যাটাস: <strong className="text-emerald-700">{STATUS_LABELS[item.status] || item.status}</strong></span>
                    </div>
                </div>

                <div className="flex gap-2">
                    <button
                        onClick={() => setActiveTab('details')}
                        className={`rounded-xl px-4 py-2 text-xs font-bold transition ${
                            activeTab === 'details' ? 'bg-[#1A2E2F] text-[#FFF99A]' : 'bg-white border text-slate-600'
                        }`}
                    >
                        কনটেন্ট বিবরণী
                    </button>
                    <button
                        onClick={() => setActiveTab('history')}
                        className={`rounded-xl px-4 py-2 text-xs font-bold transition ${
                            activeTab === 'history' ? 'bg-[#1A2E2F] text-[#FFF99A]' : 'bg-white border text-slate-600'
                        }`}
                    >
                        রিভিউ ইতিহাস ও মন্তব্য ({reviews?.length || 0})
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left 2 Cols: Content or History */}
                <div className="lg:col-span-2 space-y-6">
                    {activeTab === 'details' ? (
                        <div className="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
                            <h2 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla">
                                বিস্তারিত কনটেন্ট প্রিভিউ
                            </h2>

                            {/* Render Fields according to type */}
                            {type === 'fatwa' && (
                                <div className="space-y-4">
                                    <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                                        <h4 className="text-xs font-bold text-slate-500 uppercase">প্রশ্নকারীর প্রশ্ন</h4>
                                        <p className="mt-1 text-sm text-slate-800 whitespace-pre-line leading-relaxed font-bangla">
                                            {item.question_text || item.question}
                                        </p>
                                    </div>

                                    <div className="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                                        <h4 className="text-xs font-bold text-emerald-800 uppercase">প্রদত্ত ফাতাওয়া / উত্তর</h4>
                                        <p className="mt-1 text-sm text-slate-800 whitespace-pre-line leading-relaxed font-bangla">
                                            {item.answer_text || item.answer}
                                        </p>
                                    </div>
                                </div>
                            )}

                            {type === 'article' && (
                                <div className="space-y-4">
                                    {item.excerpt && (
                                        <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200/60">
                                            <h4 className="text-xs font-bold text-slate-500 uppercase">সংক্ষিপ্ত সারসংক্ষেপ</h4>
                                            <p className="mt-1 text-sm text-slate-700 italic font-bangla">{item.excerpt}</p>
                                        </div>
                                    )}

                                    <div className="prose max-w-none text-sm text-slate-800 leading-relaxed font-bangla whitespace-pre-line">
                                        {item.body || item.content}
                                    </div>
                                </div>
                            )}

                            {type === 'course' && (
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4 text-xs">
                                        <div className="bg-slate-50 p-3 rounded-xl">
                                            <span className="text-slate-400 block">কোর্স মূল্য:</span>
                                            <span className="font-bold text-slate-800">৳{Number(item.price || 0).toFixed(0)}</span>
                                        </div>
                                        <div className="bg-slate-50 p-3 rounded-xl">
                                            <span className="text-slate-400 block">লেভেল:</span>
                                            <span className="font-bold text-slate-800">{item.level || 'সকলের জন্য'}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 className="text-xs font-bold text-slate-500 uppercase mb-1">কোর্সের বিবরণ</h4>
                                        <p className="text-sm text-slate-800 whitespace-pre-line leading-relaxed font-bangla">
                                            {item.description}
                                        </p>
                                    </div>
                                </div>
                            )}

                            {type === 'publication' && (
                                <div className="space-y-4">
                                    <p className="text-sm text-slate-800 whitespace-pre-line leading-relaxed font-bangla">
                                        {item.description || item.body}
                                    </p>
                                </div>
                            )}
                        </div>
                    ) : (
                        /* Review History Tab */
                        <div className="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                            <h2 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla">
                                পর্যালোচনা ও পরিবর্তনের ইতিহাস ({reviews?.length || 0})
                            </h2>

                            {reviews && reviews.length > 0 ? (
                                <div className="mt-6 space-y-6">
                                    {reviews.map((rev) => (
                                        <div key={rev.id} className="relative pl-6 border-l-2 border-slate-200">
                                            <div className="absolute -left-2 top-0 h-4 w-4 rounded-full bg-[#1A2E2F] border-2 border-white"></div>
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <div className="text-xs">
                                                    <strong className="text-slate-900 font-bangla">{rev.reviewer?.name || 'পর্যালোচক'}</strong>{' '}
                                                    <span className="text-slate-400">({rev.role_at_review})</span>
                                                </div>
                                                <span className="text-[11px] text-slate-400">
                                                    {new Date(rev.created_at).toLocaleDateString('bn-BD', {
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: 'numeric',
                                                        hour: '2-digit',
                                                        minute: '2-digit'
                                                    })}
                                                </span>
                                            </div>

                                            <div className="mt-2 text-xs">
                                                <span className="rounded bg-slate-100 px-2 py-0.5 font-medium text-slate-600">
                                                    {rev.from_status} → {rev.to_status}
                                                </span>{' '}
                                                <strong className="ml-1 text-slate-800">{rev.decision}</strong>
                                            </div>

                                            {rev.notes && (
                                                <p className="mt-2 text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                                                    {rev.notes}
                                                </p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-xs text-slate-400 mt-4">এখনও কোনো রিভিউ ইতিহাস নথিভুক্ত হয়নি।</p>
                            )}
                        </div>
                    )}
                </div>

                {/* Right Col: Decision Form */}
                <div className="space-y-6">
                    <div className="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-sm">
                        <h3 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla">
                            সিদ্ধান্ত গ্রহণ ও ওয়ার্কফ্লো
                        </h3>

                        <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                            {/* To Status Selector */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    নতুন স্ট্যাটাস নির্ধারণ করুন <span className="text-rose-500">*</span>
                                </label>
                                <select
                                    value={data.to_status}
                                    onChange={(e) => setData('to_status', e.target.value)}
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                >
                                    <option value="in_review">সম্পাদকীয় রিভিউ (in_review)</option>
                                    <option value="scholar_review">স্কলার রিভিউ প্রেরণ (scholar_review)</option>
                                    <option value="approved">অনুমোদন (approved)</option>
                                    <option value="published">সরাসরি প্রকাশ (published)</option>
                                    <option value="rejected">প্রত্যাখ্যান / সংশোধন অনুরোধ (rejected)</option>
                                </select>
                                {errors.to_status && <p className="text-[11px] text-rose-500 mt-1">{errors.to_status}</p>}
                            </div>

                            {/* Decision Title */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    সিদ্ধান্তের শিরোনাম / সারসংক্ষেপ <span className="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.decision}
                                    onChange={(e) => setData('decision', e.target.value)}
                                    placeholder="উদাঃ শরিয়াহ ও রেফারেন্স যাচাই সম্পন্ন"
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                    required
                                />
                                {errors.decision && <p className="text-[11px] text-rose-500 mt-1">{errors.decision}</p>}
                            </div>

                            {/* Scholar Certification (Only for courses and scholar/admin) */}
                            {type === 'course' && (canApproveScholar || userRole === 'admin') && (
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                        সার্টিফাইং স্কলার নির্বাচন (ঐচ্ছিক)
                                    </label>
                                    <select
                                        value={data.certified_by_scholar_id}
                                        onChange={(e) => setData('certified_by_scholar_id', e.target.value)}
                                        className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                    >
                                        <option value="">— কোনো স্কলার সার্টিফিকেশন নেই —</option>
                                        {scholars && scholars.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.name} ({s.designation || 'স্কলার'})
                                            </option>
                                        ))}
                                    </select>
                                    <p className="text-[10px] text-slate-400 mt-1">
                                        স্কলার নির্বাচন করলে কোর্সে "Certified Course" ব্যাজ যুক্ত হবে।
                                    </p>
                                </div>
                            )}

                            {/* Notes / Remarks */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    পর্যালোচনাকারীর বিস্তারিত মন্তব্য / সংশোধন নোট
                                </label>
                                <textarea
                                    rows="4"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="লেখক বা পরবর্তী পর্যালোচকের জন্য কোনো নির্দেশনা থাকলে লিখুন..."
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                ></textarea>
                                {errors.notes && <p className="text-[11px] text-rose-500 mt-1">{errors.notes}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full btn-primary text-xs !py-3 font-bold justify-center"
                            >
                                {processing ? 'প্রক্রিয়াধীন...' : 'সিদ্ধান্ত কার্যকর করুন'}
                            </button>
                        </form>
                    </div>

                    {/* Guidelines Box */}
                    <div className="rounded-3xl bg-[#102526] p-6 text-white text-xs space-y-2">
                        <h4 className="font-bold text-[#FFF99A]">ওয়ার্কফ্লো নির্দেশিকা:</h4>
                        <ul className="list-disc list-inside space-y-1 text-slate-300">
                            <li>খসড়া কনটেন্ট প্রথমে এডিটর পর্যালোচনায় যায়।</li>
                            <li>ধর্মীয় স্পর্শকাতর বা শরিয়াহ বিষয়ের ক্ষেত্রে স্কলার পর্যালোচনায় পাঠান।</li>
                            <li>স্কলার অনুমোদন পেলে তা "Approved" হয় এবং এডমিন প্রকাশ করতে পারেন।</li>
                        </ul>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
