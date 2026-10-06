import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Certificates({ certificates, cohorts, students, branding }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [showIssueModal, setShowIssueModal] = useState(false);
    const [previewCert, setPreviewCert] = useState(null);

    const form = useForm({
        title: 'হিফজুল কুরআন সমাপন সনদপত্র',
        cohort_id: '',
        student_id: '',
        signers: 'প্রধান মুহাদ্দিস ও প্রিন্সিপাল',
    });

    const handleIssue = (e) => {
        e.preventDefault();
        form.post(`/org/${subdomain}/certificates`, {
            onSuccess: () => {
                setShowIssueModal(false);
                form.reset();
            },
        });
    };

    return (
        <OrgLayout title="সনদপত্র ও সার্টিফিকেট প্রদান">
            <Head title="সনদপত্র ও সার্টিফিকেট প্রদান" />

            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">প্রতিষ্ঠানের ব্র্যান্ডেড সনদপত্র (সার্টিফিকেট)</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        মাদ্রাসার নিজস্ব সিল, লোগো ও স্বাক্ষরসহ একক বা বাল্ক সনদ প্রদান ও তাৎক্ষণিক ভেরিফিকেশন।
                    </p>
                </div>

                <button
                    onClick={() => setShowIssueModal(true)}
                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                >
                    + নতুন সনদপত্র ইস্যু করুন
                </button>
            </div>

            {/* Certificates Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                        <tr>
                            <th className="px-5 py-3">সনদ নম্বর</th>
                            <th className="px-5 py-3">সনদের শিরোনাম</th>
                            <th className="px-5 py-3">গ্রাহক / শিক্ষার্থীর নাম</th>
                            <th className="px-5 py-3">শ্রেণি / হালাকা</th>
                            <th className="px-5 py-3">ইস্যুর তারিখ</th>
                            <th className="px-5 py-3 text-right">প্রিভিউ</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {certificates.data?.length === 0 ? (
                            <tr>
                                <td colSpan="6" className="text-center py-10 text-slate-600">
                                    এখনও কোনো সনদপত্র ইস্যু করা হয়নি।
                                </td>
                            </tr>
                        ) : (
                            certificates.data?.map((cert) => (
                                <tr key={cert.id} className="hover:bg-slate-50 transition">
                                    <td className="px-5 py-3.5 font-mono font-bold text-emerald-800">
                                        {cert.certificate_number}
                                    </td>
                                    <td className="px-5 py-3.5 font-bold text-slate-900">
                                        {cert.title}
                                    </td>
                                    <td className="px-5 py-3.5 font-medium text-slate-800">
                                        {cert.recipient_name}
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-700">
                                        {cert.cohort?.name || 'উন্মুক্ত'}
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-700">
                                        {cert.issued_date ? new Date(cert.issued_date).toLocaleDateString('bn-BD') : '—'}
                                    </td>
                                    <td className="px-5 py-3.5 text-right">
                                        <button
                                            onClick={() => setPreviewCert(cert)}
                                            className="px-2.5 py-1 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-semibold rounded-md border border-emerald-200"
                                        >
                                            সনদ দেখুন
                                        </button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* Modal: Issue Certificate */}
            {showIssueModal && (
                <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-base font-bold text-slate-800">সনদপত্র ইস্যু করুন</h3>
                            <button onClick={() => setShowIssueModal(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <form onSubmit={handleIssue} className="space-y-4 text-xs">
                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">সনদের শিরোনাম *</label>
                                <input
                                    type="text"
                                    required
                                    value={form.data.title}
                                    onChange={(e) => form.setData('title', e.target.value)}
                                    placeholder="উদা: হিফজুল কুরআন সমাপ্তি সনদ"
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>

                            <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 text-[11px]">
                                টিপস: আপনি একক কোনো শিক্ষার্থীকে অথবা একটি পূর্ণ শ্রেণির সকল শিক্ষার্থীকে একসাথে বাল্ক সার্টিফিকেট দিতে পারেন।
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">নির্দিষ্ট শিক্ষার্থী (একক সনদের জন্য)</label>
                                <select
                                    value={form.data.student_id}
                                    onChange={(e) => form.setData('student_id', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option value="">সকল / বাল্ক ইস্যুর জন্য ফাঁকা রাখুন</option>
                                    {students?.map((s) => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">শ্রেণি / হালাকা (বাল্ক সনদের জন্য)</label>
                                <select
                                    value={form.data.cohort_id}
                                    onChange={(e) => form.setData('cohort_id', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option value="">নির্বাচন করুন</option>
                                    {cohorts?.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">অনুমোদনকারী ও স্বাক্ষর পদবি</label>
                                <input
                                    type="text"
                                    value={form.data.signers}
                                    onChange={(e) => form.setData('signers', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>

                            <div className="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowIssueModal(false)}
                                    className="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg"
                                >
                                    {form.processing ? 'ইস্যু হচ্ছে...' : 'সনদ ইস্যু সম্পন্ন করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal: Preview Certificate */}
            {previewCert && (
                <div className="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-2xl w-full p-8 shadow-2xl border-8 border-emerald-800 relative text-center">
                        <button
                            onClick={() => setPreviewCert(null)}
                            className="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold"
                        >
                            ✕
                        </button>

                        <div className="text-xs uppercase tracking-widest text-emerald-800 font-bold mb-2">
                            {previewCert.custom_metadata?.org_name || tenant.name}
                        </div>
                        <h2 className="text-2xl font-serif font-extrabold text-slate-900 mb-4">
                            {previewCert.title}
                        </h2>

                        <div className="w-24 h-0.5 bg-amber-400 mx-auto mb-6"></div>

                        <p className="text-xs text-slate-600 mb-2">এই মর্মে প্রত্যয়ন করা যাচ্ছে যে,</p>
                        <h3 className="text-xl font-bold text-emerald-900 mb-3">{previewCert.recipient_name}</h3>
                        <p className="text-xs text-slate-600 max-w-lg mx-auto mb-8 leading-relaxed">
                            অত্র প্রতিষ্ঠানের নির্ধারিত পাঠ্যক্রম ও পরীক্ষায় কৃতিত্বের সাথে উত্তীর্ণ হয়ে এই সনদ লাভ করেছেন।
                        </p>

                        <div className="grid grid-cols-2 gap-8 text-xs text-slate-700 pt-6 border-t border-slate-200">
                            <div>
                                <div className="font-semibold">{previewCert.custom_metadata?.signers || 'কর্তৃপক্ষ'}</div>
                                <div className="text-[10px] text-slate-600">স্বাক্ষর ও মোহর</div>
                            </div>
                            <div>
                                <div className="font-mono font-bold text-slate-900">{previewCert.certificate_number}</div>
                                <div className="text-[10px] text-slate-600">সনদ ভেরিফিকেশন কোড</div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </OrgLayout>
    );
}
