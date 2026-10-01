import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function InstructorApplications({ applications }) {
    const [selectedApp, setSelectedApp] = useState(null);
    const [adminNotes, setAdminNotes] = useState('');
    const [modalAction, setModalAction] = useState(null); // 'approve' | 'reject'

    const openModal = (app, action) => {
        setSelectedApp(app);
        setAdminNotes(app.admin_notes ?? '');
        setModalAction(action);
    };

    const closeModal = () => {
        setSelectedApp(null);
        setAdminNotes('');
        setModalAction(null);
    };

    const submitReview = (e) => {
        e.preventDefault();
        const routeName = `/admin/instructor-applications/${selectedApp.id}/${modalAction}`;
        
        router.put(routeName, { admin_notes: adminNotes }, {
            onSuccess: () => closeModal()
        });
    };

    return (
        <DashboardLayout title="শিক্ষক আবেদনসমূহ (Instructor Applications)">
            <Head title="শিক্ষক আবেদন মডারেশন" />

            <div className="card overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm text-brand-text">
                        <thead className="bg-brand-light text-brand-deep text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th className="px-5 py-4">আবেদনকারী</th>
                                <th className="px-5 py-4">যোগাযোগ ও সিভি</th>
                                <th className="px-5 py-4">দক্ষতা ও অভিজ্ঞতা</th>
                                <th className="px-5 py-4">অ্যাকাউন্ট স্ট্যাটাস</th>
                                <th className="px-5 py-4">স্ট্যাটাস</th>
                                <th className="px-5 py-4 text-right">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-brand/5">
                            {applications.data.map((app) => (
                                <tr key={app.id} className="hover:bg-brand-cream/5">
                                    <td className="px-5 py-4">
                                        <div className="font-bold text-brand-deep">{app.name}</div>
                                        <div className="text-xs text-brand-text/60">{app.email}</div>
                                    </td>
                                    <td className="px-5 py-4">
                                        <div>📞 {app.phone}</div>
                                        {app.cv_link ? (
                                            <a href={app.cv_link} target="_blank" rel="noopener noreferrer" className="mt-1 inline-block text-xs font-semibold text-brand hover:underline">
                                                📄 সিভি দেখুন
                                            </a>
                                        ) : (
                                            <span className="text-xs text-brand-text/40">সিভি লিংক নেই</span>
                                        )}
                                    </td>
                                    <td className="px-5 py-4 max-w-xs">
                                        <div className="font-semibold text-brand">{app.expertise}</div>
                                        <p className="mt-1 line-clamp-2 text-xs text-brand-text/75 leading-relaxed">{app.experience}</p>
                                    </td>
                                    <td className="px-5 py-4">
                                        {app.user_id ? (
                                            <div>
                                                <span className="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">অ্যাকাউন্ট লিঙ্কড</span>
                                                <div className="mt-1 text-[11px] text-brand-text/50">রোল: {app.user?.role}</div>
                                            </div>
                                        ) : (
                                            <div>
                                                <span className="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">গেস্ট আবেদনকারী</span>
                                                <div className="mt-1 text-[11px] text-amber-600">ম্যানুয়ালি খুলতে হবে</div>
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-5 py-4">
                                        {app.status === 'pending' ? (
                                            <span className="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800">পেন্ডিং</span>
                                        ) : app.status === 'approved' ? (
                                            <div>
                                                <span className="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">অনুমোদিত</span>
                                                {app.reviewer && (
                                                    <div className="mt-1 text-[10px] text-brand-text/50">বাই: {app.reviewer.name}</div>
                                                )}
                                            </div>
                                        ) : (
                                            <div>
                                                <span className="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">প্রত্যাখ্যাত</span>
                                                {app.reviewer && (
                                                    <div className="mt-1 text-[10px] text-brand-text/50">বাই: {app.reviewer.name}</div>
                                                )}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-5 py-4 text-right whitespace-nowrap">
                                        {app.status === 'pending' ? (
                                            <div className="flex justify-end gap-1">
                                                <button onClick={() => openModal(app, 'approve')} className="btn-accent !px-3 !py-1 text-xs">অনুমোদন</button>
                                                <button onClick={() => openModal(app, 'reject')} className="btn-secondary !px-3 !py-1 text-xs text-red-600 border-red-200">প্রত্যাখ্যান</button>
                                            </div>
                                        ) : (
                                            <button onClick={() => openModal(app, app.status === 'approved' ? 'approve' : 'reject')} className="text-brand hover:underline text-xs font-bold">নোট দেখুন</button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {applications.data.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="px-5 py-8 text-center text-brand-text/50 italic">কোনো আবেদন পাওয়া যায়নি।</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination links={applications.links} />
            </div>

            {/* Approve/Reject Modal */}
            {selectedApp && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={submitReview} className="card max-w-md w-full p-6 space-y-4">
                        <div className="flex items-center justify-between border-b border-brand/5 pb-2">
                            <h3 className="text-lg font-bold text-brand-deep">
                                {modalAction === 'approve' ? 'আবেদন অনুমোদন করুন' : 'আবেদন প্রত্যাখ্যান করুন'}
                            </h3>
                            <button type="button" onClick={closeModal} className="text-brand-text/40 hover:text-brand-text text-lg">×</button>
                        </div>
                        
                        <div className="text-sm space-y-1">
                            <p><strong>আবেদনকারী:</strong> {selectedApp.name}</p>
                            <p><strong>ইমেইল:</strong> {selectedApp.email}</p>
                            <p><strong>দক্ষতার ক্ষেত্র:</strong> {selectedApp.expertise}</p>
                        </div>

                        {modalAction === 'approve' && !selectedApp.user_id && (
                            <div className="p-3 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                                ⚠️ <strong>দৃষ্টি আকর্ষণ:</strong> এই আবেদনকারীর কোনো ব্যবহারকারী অ্যাকাউন্ট লিঙ্কড নেই। অনুমোদন সফল হবে কিন্তু তাকে ম্যানুয়ালি যোগাযোগ করে অ্যাকাউন্ট খোলার অনুরোধ পাঠাতে হবে।
                            </div>
                        )}

                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">অ্যাডমিন নোট / বার্তা</label>
                            <textarea rows={3} className="mt-1 block w-full rounded-xl border-brand/20 text-xs focus:border-brand focus:ring-brand" placeholder="রিভিউ বা সিদ্ধান্তের নোট এখানে লিখুন..." value={adminNotes} onChange={(e) => setAdminNotes(e.target.value)} disabled={selectedApp.status !== 'pending'} />
                        </div>

                        <div className="flex justify-end gap-2 border-t border-brand/5 pt-3">
                            <button type="button" onClick={closeModal} className="btn-secondary !py-2 text-xs">বন্ধ করুন</button>
                            {selectedApp.status === 'pending' && (
                                <button type="submit" className={`btn-primary !py-2 text-xs ${modalAction === 'reject' ? 'bg-red-600 hover:bg-red-700 text-white' : ''}`}>
                                    {modalAction === 'approve' ? 'অনুমোদন নিশ্চিত করুন' : 'প্রত্যাখ্যান নিশ্চিত করুন'}
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            )}
        </DashboardLayout>
    );
}
