import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function AuditLogs({ logs, filters }) {
    const [actionFilter, setActionFilter] = useState(filters?.action || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/admin/audit-logs', { action: actionFilter }, { preserveState: true });
    };

    const handleReset = () => {
        setActionFilter('');
        router.get('/admin/audit-logs');
    };

    return (
        <DashboardLayout title="অডিট লগ (Audit Trail)">
            <Head title="অডিট লগ — অ্যাডমিন" />

            <div className="card overflow-x-auto">
                <div className="border-b border-gray-100 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-bold text-gray-900">সিকিউরিটি ও অ্যাডমিন অডিট ট্রেল</h2>
                        <p className="text-xs text-gray-500 mt-0.5">অ্যাডমিন অ্যাকশন, পেমেন্ট অনুমোদন/বাতিল এবং স্পর্শকাতর ইভেন্ট লগ</p>
                    </div>

                    <form onSubmit={handleFilter} className="flex items-center gap-2">
                        <input
                            type="text"
                            placeholder="অ্যাকশন ফিল্টার (যেমন: order, role)..."
                            value={actionFilter}
                            onChange={(e) => setActionFilter(e.target.value)}
                            className="input text-xs py-1.5 px-3 w-48 sm:w-64 border rounded"
                        />
                        <button type="submit" className="btn btn-secondary text-xs py-1.5 px-3 bg-[#102526] text-white rounded">
                            ফিল্টার
                        </button>
                        {filters?.action && (
                            <button
                                type="button"
                                onClick={handleReset}
                                className="text-xs text-gray-500 hover:text-red-500 underline"
                            >
                                রিসেট
                            </button>
                        )}
                    </form>
                </div>

                <table className="w-full text-left text-sm">
                    <thead className="bg-[#102526] text-[#FFF99A]">
                        <tr>
                            <th className="p-4 font-semibold">আইডি</th>
                            <th className="p-4 font-semibold">অ্যাকশন</th>
                            <th className="p-4 font-semibold">ব্যবহারকারী</th>
                            <th className="p-4 font-semibold">টার্গেট মডেল</th>
                            <th className="p-4 font-semibold">আইপি ও ডিভাইস</th>
                            <th className="p-4 font-semibold">পেলোড / বিবরণ</th>
                            <th className="p-4 font-semibold">তারিখ ও সময়</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {logs.data && logs.data.length > 0 ? (
                            logs.data.map((log) => (
                                <tr key={log.id} className="hover:bg-gray-50/60 transition-colors">
                                    <td className="p-4 font-mono text-xs text-gray-500">#{log.id}</td>
                                    <td className="p-4">
                                        <span className="font-mono text-xs font-semibold px-2.5 py-1 rounded bg-teal-50 text-teal-800 border border-teal-200">
                                            {log.action}
                                        </span>
                                    </td>
                                    <td className="p-4">
                                        {log.user ? (
                                            <div>
                                                <div className="font-medium text-gray-900">{log.user.name}</div>
                                                <div className="text-xs text-gray-500">{log.user.email} ({log.user.role})</div>
                                            </div>
                                        ) : (
                                            <span className="text-xs text-gray-400 italic">সিস্টেম / অতিথি</span>
                                        )}
                                    </td>
                                    <td className="p-4 font-mono text-xs text-gray-600">
                                        {log.model_type ? (
                                            <span>
                                                {log.model_type.split('\\').pop()} #{log.model_id}
                                            </span>
                                        ) : (
                                            <span className="text-gray-400">—</span>
                                        )}
                                    </td>
                                    <td className="p-4 text-xs text-gray-500">
                                        <div>{log.ip_address || '—'}</div>
                                    </td>
                                    <td className="p-4 text-xs font-mono text-gray-600 max-w-xs truncate" title={JSON.stringify(log.payload, null, 2)}>
                                        {log.payload ? JSON.stringify(log.payload) : '—'}
                                    </td>
                                    <td className="p-4 text-xs text-gray-500 whitespace-nowrap">
                                        {new Date(log.created_at).toLocaleString('bn-BD', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })}
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="7" className="p-8 text-center text-gray-400">
                                    কোনো অডিট লগ পাওয়া যায়নি।
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>

                {logs.links && (
                    <div className="p-4 border-t border-gray-100 flex justify-end">
                        <Pagination links={logs.links} />
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
