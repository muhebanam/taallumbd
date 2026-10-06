import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function CommunityModeration({ reports, stats = {}, filters = {} }) {
    const [actionModal, setActionModal] = useState(null); // report being acted on
    const [actionType, setActionType] = useState('hide_content');
    const [muteHours, setMuteHours] = useState(24);

    const handleFilter = (status) => {
        router.get('/admin/community/reports', { status }, { preserveState: true });
    };

    const handleResolve = (e) => {
        e.preventDefault();
        if (!actionModal) return;

        router.post(`/admin/community/reports/${actionModal.id}/resolve`, {
            action: actionType,
            mute_hours: actionType === 'mute_author' ? muteHours : undefined,
        }, {
            onSuccess: () => setActionModal(null),
        });
    };

    return (
        <DashboardLayout role="admin">
            <Head title="কমিউনিটি মডারেশন কিউ — অ্যাডমিন" />

            <div className="py-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">কমিউনিটি ও ফোরাম মডারেশন</h1>
                        <p className="text-sm text-gray-500">অনুপযুক্ত আলোচনা, স্প্যাম, বিভ্রান্তিকর তথ্য ও ইউজার রিপোর্ট পর্যালোচনা</p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <div className="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <div className="text-xs font-semibold text-amber-800">অপেক্ষমাণ রিপোর্ট</div>
                        <div className="mt-2 text-3xl font-extrabold text-amber-950">{stats.pending || 0}</div>
                    </div>
                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <div className="text-xs font-semibold text-emerald-800">সমাধানকৃত রিপোর্ট</div>
                        <div className="mt-2 text-3xl font-extrabold text-emerald-950">{stats.resolved || 0}</div>
                    </div>
                    <div className="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-sm">
                        <div className="text-xs font-semibold text-gray-700">খারিজকৃত রিপোর্ট</div>
                        <div className="mt-2 text-3xl font-extrabold text-gray-900">{stats.dismissed || 0}</div>
                    </div>
                </div>

                {/* Filters */}
                <div className="flex border-b border-gray-200 gap-4 mb-6">
                    <button
                        onClick={() => handleFilter('pending')}
                        className={`pb-3 text-sm font-semibold border-b-2 transition ${
                            filters.status === 'pending'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        অপেক্ষমাণ ({stats.pending || 0})
                    </button>
                    <button
                        onClick={() => handleFilter('resolved')}
                        className={`pb-3 text-sm font-semibold border-b-2 transition ${
                            filters.status === 'resolved'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        সমাধানকৃত ({stats.resolved || 0})
                    </button>
                    <button
                        onClick={() => handleFilter('dismissed')}
                        className={`pb-3 text-sm font-semibold border-b-2 transition ${
                            filters.status === 'dismissed'
                                ? 'border-[#102526] text-[#102526]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        খারিজকৃত ({stats.dismissed || 0})
                    </button>
                </div>

                {/* Reports Table / List */}
                {reports.data && reports.data.length > 0 ? (
                    <div className="space-y-4">
                        {reports.data.map((report) => (
                            <div key={report.id} className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div>
                                        <div className="flex items-center gap-2 mb-2">
                                            <span className="rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">
                                                কারণ: {report.reason}
                                            </span>
                                            <span className="text-xs text-gray-400">
                                                রিপোর্টার: {report.reporter?.name} ({new Date(report.created_at).toLocaleDateString('bn-BD')})
                                            </span>
                                        </div>

                                        {report.details && (
                                            <p className="text-xs text-gray-600 mb-3 bg-gray-50 p-2 rounded-lg">
                                                <strong>অভিযোগের বিবরণ:</strong> {report.details}
                                            </p>
                                        )}

                                        {/* Reported Content Snippet */}
                                        <div className="rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-xs">
                                            <span className="font-bold text-gray-700">বিষয়বস্তুর ধরন:</span> {report.reportable_type?.split('\\').pop()}<br />
                                            <span className="text-gray-800 mt-1 block">
                                                "{report.reportable?.body || report.reportable?.title || 'কনটেন্ট মুছে ফেলা বা অনুপলব্ধ'}"
                                            </span>
                                        </div>

                                        {report.action_taken && (
                                            <div className="mt-3 text-xs text-emerald-800 font-semibold">
                                                ✓ গৃহীত ব্যবস্থা: {report.action_taken} (পর্যালোচক: {report.reviewer?.name})
                                            </div>
                                        )}
                                    </div>

                                    {report.status === 'pending' && (
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => setActionModal(report)}
                                                className="rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-white hover:bg-[#1A2E2F]"
                                            >
                                                ব্যবস্থা নিন
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-gray-300 p-12 text-center bg-gray-50 text-sm text-gray-500">
                        এই তালিকায় কোনো রিপোর্ট অপেক্ষমাণ নেই।
                    </div>
                )}

                {reports.links && (
                    <div className="mt-8">
                        <Pagination links={reports.links} />
                    </div>
                )}

                {/* Resolve Modal */}
                {actionModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                            <h3 className="text-lg font-bold text-gray-900">মডারেশন অ্যাকশন নির্ধারণ</h3>
                            <p className="mt-1 text-xs text-gray-500">রিপোর্ট # {actionModal.id} এর বিপরীতে গৃহীত পদক্ষেপ নির্বাচন করুন</p>

                            <form onSubmit={handleResolve} className="mt-4 space-y-4">
                                <div className="space-y-2">
                                    <label className="flex items-center gap-2 text-xs font-semibold text-gray-800">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="hide_content"
                                            checked={actionType === 'hide_content'}
                                            onChange={(e) => setActionType(e.target.value)}
                                        />
                                        বিষয়বস্তু লুকিয়ে ফেলুন (Hide Content)
                                    </label>
                                    <label className="flex items-center gap-2 text-xs font-semibold text-gray-800">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="mute_author"
                                            checked={actionType === 'mute_author'}
                                            onChange={(e) => setActionType(e.target.value)}
                                        />
                                        লেখককে সাময়িক মিউট করুন (Mute Author)
                                    </label>
                                    <label className="flex items-center gap-2 text-xs font-semibold text-gray-800">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="ban_author"
                                            checked={actionType === 'ban_author'}
                                            onChange={(e) => setActionType(e.target.value)}
                                        />
                                        ব্যবহারকারীকে স্থায়ীভাবে ব্যান করুন (Ban User)
                                    </label>
                                    <label className="flex items-center gap-2 text-xs font-semibold text-gray-800">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="dismiss"
                                            checked={actionType === 'dismiss'}
                                            onChange={(e) => setActionType(e.target.value)}
                                        />
                                        রিপোর্ট খারিজ করুন (Dismiss)
                                    </label>
                                </div>

                                {actionType === 'mute_author' && (
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700">মিউট এর সময়কাল (ঘণ্টা)</label>
                                        <input
                                            type="number"
                                            value={muteHours}
                                            onChange={(e) => setMuteHours(parseInt(e.target.value) || 24)}
                                            className="mt-1 w-24 rounded-lg border-gray-300 text-xs"
                                        />
                                    </div>
                                )}

                                <div className="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                                    <button
                                        type="button"
                                        onClick={() => setActionModal(null)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                    >
                                        বাতিল
                                    </button>
                                    <button
                                        type="submit"
                                        className="rounded-xl bg-[#102526] px-4 py-2 text-xs font-bold text-white hover:bg-[#1A2E2F]"
                                    >
                                        সংরক্ষণ ও কার্যকর করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
