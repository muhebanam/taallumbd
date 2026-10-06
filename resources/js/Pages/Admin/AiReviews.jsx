import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function AiReviews({ reviews }) {
    const [selectedItem, setSelectedItem] = useState(null);
    const [notes, setNotes] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const handleResolve = (e) => {
        e.preventDefault();
        if (!selectedItem || !notes.trim()) return;

        setSubmitting(true);
        router.post(route('admin.ai.reviews.resolve', selectedItem.id), {
            scholar_notes: notes,
        }, {
            onSuccess: () => {
                setSelectedItem(null);
                setNotes('');
                setSubmitting(false);
            },
            onError: () => setSubmitting(false),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="স্কলার রিভিউ কিউ (AI Governance)" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <div className="flex items-center justify-between bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-800 font-bold text-lg">
                                    ⚖️
                                </span>
                                <h1 className="text-xl font-bold text-slate-900 font-bangla">
                                    স্কলার রিভিউ কিউ (AI তত্ত্বাবধান)
                                </h1>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">
                                শিক্ষার্থীরা যেসব উত্তরে অসঙ্গতি বা প্রশ্ন রিপোর্ট করেছেন, বিজ্ঞ আলেমগণ তা পরীক্ষা করে সমাধান দিচ্ছেন।
                            </p>
                        </div>

                        <Link
                            href={route('admin.ai.index')}
                            className="px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition"
                        >
                            ← AI খরচ ড্যাশবোর্ড
                        </Link>
                    </div>

                    {/* Review List */}
                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div className="divide-y divide-slate-100">
                            {reviews.data.map((item) => (
                                <div key={item.id} className="p-6 space-y-4 hover:bg-slate-50/50 transition">
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="font-mono text-xs text-slate-400">#{item.id}</span>
                                                <span className="px-2 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-700">
                                                    {item.feature}
                                                </span>
                                                <span className="text-xs text-slate-500">
                                                    ইউজার: {item.user ? item.user.name : 'গেস্ট'}
                                                </span>
                                            </div>
                                            <h4 className="mt-2 text-xs font-bold text-slate-700">
                                                প্রশ্ন / প্রম্পট:
                                            </h4>
                                            <p className="text-xs text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-200 mt-1">
                                                {item.prompt_redacted}
                                            </p>
                                        </div>

                                        <div className="text-right shrink-0">
                                            {item.scholar_reviewed ? (
                                                <span className="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                                                    ✓ পরীক্ষিত ও সমাধানকৃত
                                                </span>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() => { setSelectedItem(item); setNotes(''); }}
                                                    className="px-4 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition"
                                                >
                                                    পর্যবেক্ষণ লিখুন
                                                </button>
                                            )}
                                        </div>
                                    </div>

                                    {/* AI Response */}
                                    <div>
                                        <h4 className="text-xs font-bold text-slate-700">এআই কর্তৃক প্রদত্ত উত্তর:</h4>
                                        <div className="mt-1 text-xs text-slate-800 bg-white p-3 rounded-xl border border-slate-200 leading-relaxed whitespace-pre-line">
                                            {item.response}
                                        </div>
                                    </div>

                                    {/* Flag Reason */}
                                    <div className="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 flex items-start gap-2">
                                        <span className="font-bold">⚠️ শিক্ষার্থীর রিপোর্ট:</span>
                                        <span>{item.flag_reason || 'অসঙ্গতিপূর্ণ তথ্য রিপোর্ট করা হয়েছে।'}</span>
                                    </div>

                                    {/* Scholar Notes if already reviewed */}
                                    {item.scholar_reviewed && item.scholar_notes && (
                                        <div className="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                                            <div className="font-bold flex items-center gap-1.5">
                                                <span>বিজ্ঞ আলেমের মন্তব্য ({item.reviewer ? item.reviewer.name : 'স্কলার'}):</span>
                                            </div>
                                            <p className="whitespace-pre-line">{item.scholar_notes}</p>
                                        </div>
                                    )}
                                </div>
                            ))}

                            {reviews.data.length === 0 && (
                                <div className="p-12 text-center text-slate-400 text-xs">
                                    মাশাআল্লাহ! পর্যালোচনার জন্য কোনো রিপোর্টকৃত উত্তর জমা নেই।
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Resolve Modal */}
                    {selectedItem && (
                        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                            <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
                                <h3 className="font-bold text-sm text-slate-900 font-bangla">
                                    স্কলার পর্যবেক্ষণ ও সমাধান সংযোজন (ইন্টারেকশন #{selectedItem.id})
                                </h3>
                                <form onSubmit={handleResolve} className="space-y-3">
                                    <label className="block text-xs font-semibold text-slate-700">
                                        শরয়ী ও অ্যাকাডেমিক সংশোধন/পরামর্শ লিখুন:
                                    </label>
                                    <textarea
                                        value={notes}
                                        onChange={e => setNotes(e.target.value)}
                                        rows={4}
                                        required
                                        placeholder="উত্তরের ত্রুটি ও সঠিক তথ্য উল্লেখ করুন..."
                                        className="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600"
                                    />

                                    <div className="flex justify-end gap-2 pt-2">
                                        <button
                                            type="button"
                                            onClick={() => setSelectedItem(null)}
                                            className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
                                        >
                                            বাতিল
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={submitting}
                                            className="px-4 py-2 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl disabled:opacity-50"
                                        >
                                            সংরক্ষণ ও সমাধান করুন
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
