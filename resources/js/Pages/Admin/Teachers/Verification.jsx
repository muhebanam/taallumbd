import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

export default function ScholarVerification({ teacher, defaultChecklist }) {
    // Checklist form
    const initialChecklist = teacher.verification_checklist || {};
    const checklistForm = useForm({
        checklist: initialChecklist,
        notes: teacher.verification_notes || '',
    });

    // Document upload form
    const docForm = useForm({
        title: '',
        document: null,
    });

    const handleCheckboxChange = (key) => {
        checklistForm.setData('checklist', {
            ...checklistForm.data.checklist,
            [key]: !checklistForm.data.checklist[key],
        });
    };

    const submitChecklist = (e) => {
        e.preventDefault();
        checklistForm.post(`/admin/teachers/${teacher.id}/verification/checklist`, {
            preserveScroll: true,
        });
    };

    const submitDocument = (e) => {
        e.preventDefault();
        docForm.post(`/admin/teachers/${teacher.id}/verification/document`, {
            preserveScroll: true,
            onSuccess: () => docForm.reset(),
        });
    };

    const toggleVerification = () => {
        if (teacher.is_verified) {
            if (confirm(`আপনি কি নিশ্চিত যে ${teacher.name}-এর স্কলার ভেরিফিকেশন প্রত্যাহার করতে চান?`)) {
                router.post(`/admin/teachers/${teacher.id}/verification/unverify`);
            }
        } else {
            if (confirm(`আপনি কি নিশ্চিত যে ${teacher.name}-কে প্রাতিষ্ঠানিকভাবে ভেরিফায়েড স্কলার হিসেবে অনুমোদন দিতে চান?`)) {
                router.post(`/admin/teachers/${teacher.id}/verification/verify`);
            }
        }
    };

    return (
        <DashboardLayout title={`স্কলার ভেরিফিকেশন: ${teacher.name}`}>
            <Head title={`স্কলার ভেরিফিকেশন — ${teacher.name}`} />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link
                        href="/admin/teachers"
                        className="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-2"
                    >
                        ← উস্তায/স্কলার তালিকায় ফিরুন
                    </Link>
                    <div className="flex items-center gap-3">
                        <h1 className="text-xl sm:text-2xl font-bold text-[#142425] font-bangla">
                            {teacher.name}
                        </h1>
                        {teacher.is_verified ? (
                            <span className="rounded-full bg-emerald-100 border border-emerald-300 px-3 py-1 text-xs font-bold text-emerald-800 flex items-center gap-1">
                                ✓ ভেরিফায়েড স্কলার
                            </span>
                        ) : (
                            <span className="rounded-full bg-amber-100 border border-amber-300 px-3 py-1 text-xs font-bold text-amber-800">
                                ⏳ ভেরিফিকেশন বাকি
                            </span>
                        )}
                    </div>
                    <p className="text-xs text-slate-500 mt-1">
                        {teacher.designation || 'ইসলামিক স্কলার'} • {teacher.institution || 'আত-তাআল্লুম একাডেমি'}
                    </p>
                </div>

                <div>
                    {teacher.is_verified ? (
                        <button
                            onClick={toggleVerification}
                            className="rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition"
                        >
                            ভেরিফিকেশন প্রত্যাহার করুন
                        </button>
                    ) : (
                        <button
                            onClick={toggleVerification}
                            className="btn-primary text-xs !py-2.5 !px-5"
                        >
                            ✓ ভেরিফায়েড হিসেবে অনুমোদন দিন
                        </button>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left 2 Cols: Checklist & Private Documents */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Verification Checklist */}
                    <div className="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                        <h2 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla flex items-center justify-between">
                            <span>শরিয়াহ ও একাডেমিক চেকলিস্ট</span>
                            <span className="text-xs font-normal text-slate-500">গভর্ন্যান্স যাচাই ধাপ</span>
                        </h2>

                        <form onSubmit={submitChecklist} className="mt-5 space-y-4">
                            <div className="space-y-3">
                                {Object.entries(defaultChecklist).map(([key, label]) => {
                                    const checked = !!checklistForm.data.checklist[key];
                                    return (
                                        <label
                                            key={key}
                                            className={`flex items-start gap-3 p-3.5 rounded-2xl border transition cursor-pointer ${
                                                checked ? 'bg-emerald-50/50 border-emerald-300' : 'bg-slate-50/50 border-slate-200'
                                            }`}
                                        >
                                            <input
                                                type="checkbox"
                                                checked={checked}
                                                onChange={() => handleCheckboxChange(key)}
                                                className="mt-1 h-4 w-4 rounded border-slate-300 text-[#1A2E2F] focus:ring-[#1A2E2F]"
                                            />
                                            <div className="text-xs">
                                                <p className="font-bold text-slate-800 font-bangla">{label}</p>
                                                <span className="text-[11px] text-slate-400">
                                                    {checked ? '✓ যাচাই সম্পন্ন ও সত্যায়িত' : 'এখনও যাচাই করা হয়নি'}
                                                </span>
                                            </div>
                                        </label>
                                    );
                                })}
                            </div>

                            <div className="mt-4">
                                <label className="block text-xs font-bold text-slate-700 mb-1.5">
                                    যাচাইকরণ পর্যবেক্ষণ ও মন্তব্য
                                </label>
                                <textarea
                                    rows="3"
                                    value={checklistForm.data.notes}
                                    onChange={(e) => checklistForm.setData('notes', e.target.value)}
                                    placeholder="উস্তাযের সনদ, ইজাজাহ বা প্রাতিষ্ঠানিক রেফারেন্স সম্পর্কে অভ্যন্তরীণ নোট..."
                                    className="w-full rounded-xl border-slate-300 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                                ></textarea>
                            </div>

                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={checklistForm.processing}
                                    className="rounded-xl bg-[#1A2E2F] px-4 py-2 text-xs font-bold text-[#FFF99A] hover:bg-[#142425] transition"
                                >
                                    {checklistForm.processing ? 'সংরক্ষণ হচ্ছে...' : 'চেকলিস্ট সংরক্ষণ করুন'}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Uploaded Documents (Private) */}
                    <div className="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                        <h2 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla flex items-center justify-between">
                            <span>সংরক্ষিত গোপনীয় সনদপত্র ও নথি</span>
                            <span className="text-xs font-semibold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                                গোপনীয় ও সুরক্ষিত
                            </span>
                        </h2>

                        <div className="mt-5 space-y-3">
                            {teacher.verification_documents && teacher.verification_documents.length > 0 ? (
                                teacher.verification_documents.map((doc, idx) => (
                                    <div
                                        key={doc.id || idx}
                                        className="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs"
                                    >
                                        <div className="flex items-center gap-3">
                                            <span className="text-xl">📄</span>
                                            <div>
                                                <p className="font-bold text-slate-800">{doc.title}</p>
                                                <p className="text-[11px] text-slate-500 font-mono">{doc.filename} • আপলোড: {new Date(doc.uploaded_at).toLocaleDateString('bn-BD')}</p>
                                            </div>
                                        </div>
                                        <span className="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            সংরক্ষিত
                                        </span>
                                    </div>
                                ))
                            ) : (
                                <p className="text-xs text-slate-400">এখনও কোনো নথি আপলোড করা হয়নি।</p>
                            )}
                        </div>

                        {/* Document Upload Form */}
                        <form onSubmit={submitDocument} className="mt-6 pt-6 border-t border-slate-100 space-y-4">
                            <h3 className="text-xs font-bold text-slate-700">নতুন সনদ/নথি আপলোড করুন:</h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-[11px] font-semibold text-slate-600 mb-1">নথির শিরোনাম</label>
                                    <input
                                        type="text"
                                        value={docForm.data.title}
                                        onChange={(e) => docForm.setData('title', e.target.value)}
                                        placeholder="উদাঃ দাওরায়ে হাদীস সনদপত্র"
                                        className="w-full rounded-xl border-slate-300 text-xs"
                                        required
                                    />
                                    {docForm.errors.title && <p className="text-[11px] text-rose-500 mt-1">{docForm.errors.title}</p>}
                                </div>
                                <div>
                                    <label className="block text-[11px] font-semibold text-slate-600 mb-1">ফাইল (PDF, JPG, PNG)</label>
                                    <input
                                        type="file"
                                        onChange={(e) => docForm.setData('document', e.target.files[0])}
                                        className="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1A2E2F] file:text-[#FFF99A]"
                                        required
                                    />
                                    {docForm.errors.document && <p className="text-[11px] text-rose-500 mt-1">{docForm.errors.document}</p>}
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={docForm.processing}
                                className="btn-secondary text-xs !py-2"
                            >
                                {docForm.processing ? 'আপলোড হচ্ছে...' : 'নথি আপলোড করুন'}
                            </button>
                        </form>
                    </div>
                </div>

                {/* Right Col: Scholar Status & Trust Metrics */}
                <div className="space-y-6">
                    <div className="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-sm space-y-4">
                        <h3 className="text-base font-bold text-[#142425] border-b pb-3 font-bangla">
                            ভেরিফিকেশন সারসংক্ষেপ
                        </h3>

                        <div className="space-y-3 text-xs">
                            <div>
                                <span className="text-slate-400 block">বর্তমান স্ট্যাটাস:</span>
                                <span className={`font-bold mt-0.5 inline-block ${teacher.is_verified ? 'text-emerald-700' : 'text-amber-700'}`}>
                                    {teacher.is_verified ? '✓ প্রাতিষ্ঠানিকভাবে সত্যায়িত' : 'অপেক্ষমাণ / অসত্যায়িত'}
                                </span>
                            </div>

                            {teacher.verified_at && (
                                <div>
                                    <span className="text-slate-400 block">অনুমোদনের তারিখ:</span>
                                    <span className="font-semibold text-slate-800">
                                        {new Date(teacher.verified_at).toLocaleDateString('bn-BD', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        })}
                                    </span>
                                </div>
                            )}

                            {teacher.verified_by && teacher.verified_by_user && (
                                <div>
                                    <span className="text-slate-400 block">যাচাইকারী কর্মকর্তা:</span>
                                    <span className="font-semibold text-slate-800">
                                        {teacher.verified_by_user.name}
                                    </span>
                                </div>
                            )}
                        </div>

                        <div className="rounded-2xl bg-amber-50/70 border border-amber-200/70 p-4 text-xs text-amber-900 space-y-1">
                            <span className="font-bold">ট্রাস্ট ও আস্থা নীতি:</span>
                            <p className="text-[11px] leading-relaxed">
                                ভেরিফায়েড স্কলারদের প্রোফাইল সরাসরি "Scholar Board"-এ দৃশ্যমান হবে এবং তারা কোর্সের শরিয়াহ সার্টিফিকেশন প্রদান করতে পারবেন।
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
