import React, { useState } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import OrgLayout from '@/Layouts/OrgLayout';

export default function Members({ members, filters, seat_info }) {
    const { tenant } = usePage().props;
    const subdomain = tenant.subdomain;

    const [showAddModal, setShowAddModal] = useState(false);
    const [showCsvModal, setShowCsvModal] = useState(false);

    // Single member form
    const addForm = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'student',
        id_number: '',
        guardian_user_id: '',
    });

    // CSV bulk import form
    const csvForm = useForm({
        csv_text: '',
        default_role: 'student',
    });

    const handleSingleSubmit = (e) => {
        e.preventDefault();
        addForm.post(`/org/${subdomain}/members`, {
            onSuccess: () => {
                setShowAddModal(false);
                addForm.reset();
            },
        });
    };

    const handleCsvSubmit = (e) => {
        e.preventDefault();
        csvForm.post(`/org/${subdomain}/members/bulk-import`, {
            onSuccess: () => {
                setShowCsvModal(false);
                csvForm.reset();
            },
        });
    };

    const handleFilterRole = (role) => {
        router.get(`/org/${subdomain}/members`, { role: role || undefined, search: filters.search }, { preserveState: true });
    };

    const handleSearch = (e) => {
        e.preventDefault();
        const search = e.target.search.value;
        router.get(`/org/${subdomain}/members`, { role: filters.role, search: search || undefined }, { preserveState: true });
    };

    const handleDelete = (memberId) => {
        if (confirm('আপনি কি নিশ্চিত যে এই সদস্যকে অপসারণ করতে চান?')) {
            router.delete(`/org/${subdomain}/members/${memberId}`);
        }
    };

    return (
        <OrgLayout title="সদস্য ও সিট ব্যবস্থাপনা">
            <Head title="সদস্য ও সিট ব্যবস্থাপনা" />

            {/* Top Bar with Seat Card & Action Buttons */}
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">শিক্ষক, শিক্ষার্থী ও অভিভাবক ব্যবস্থাপনা</h2>
                    <p className="text-xs text-slate-700 mt-0.5">
                        সিট বরাদ্দ: মোট {seat_info.seat_limit} | ব্যবহৃত: {seat_info.used_seats} | অবশিষ্ট সিট: <span className="font-bold text-emerald-800">{seat_info.available_seats}</span>
                    </p>
                </div>

                <div className="flex items-center space-x-2 rtl:space-x-reverse">
                    <button
                        onClick={() => setShowCsvModal(true)}
                        className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-lg transition border border-slate-300 flex items-center space-x-1.5 rtl:space-x-reverse"
                    >
                        <span>📄 CSV বাল্ক ইম্পোর্ট</span>
                    </button>
                    <button
                        onClick={() => setShowAddModal(true)}
                        className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                    >
                        + সদস্য যোগ করুন
                    </button>
                </div>
            </div>

            {/* Filters & Search */}
            <div className="bg-white p-4 rounded-xl border border-slate-200 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-xs">
                <div className="flex flex-wrap gap-1.5">
                    {['', 'student', 'teacher', 'guardian', 'org_admin'].map((r) => (
                        <button
                            key={r}
                            onClick={() => handleFilterRole(r)}
                            className={`px-3 py-1.5 text-xs font-medium rounded-lg transition ${
                                (filters.role || '') === r
                                    ? 'bg-emerald-800 text-white font-semibold'
                                    : 'bg-slate-100 text-slate-800 hover:bg-slate-200'
                            }`}
                        >
                            {r === '' ? 'সকল' : (r === 'student' ? 'শিক্ষার্থী' : (r === 'teacher' ? 'মুদাররিস/শিক্ষক' : (r === 'guardian' ? 'অভিভাবক' : 'এডমিন')))}
                        </button>
                    ))}
                </div>

                <form onSubmit={handleSearch} className="flex items-center space-x-2 rtl:space-x-reverse">
                    <input
                        type="text"
                        name="search"
                        defaultValue={filters.search || ''}
                        placeholder="নাম, ইমেইল বা রোল নম্বর..."
                        className="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 w-52"
                    />
                    <button
                        type="submit"
                        className="px-3 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-900 transition"
                    >
                        খুঁজুন
                    </button>
                </form>
            </div>

            {/* Members Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                            <tr>
                                <th className="px-5 py-3">আইডি / রোল</th>
                                <th className="px-5 py-3">নাম ও ইমেইল</th>
                                <th className="px-5 py-3">রোল (পদবি)</th>
                                <th className="px-5 py-3">অভিভাবক</th>
                                <th className="px-5 py-3">যোগদানের তারিখ</th>
                                <th className="px-5 py-3 text-right">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {members.data?.length === 0 ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-10 text-slate-600">
                                        কোনো সদস্য পাওয়া যায়নি।
                                    </td>
                                </tr>
                            ) : (
                                members.data?.map((m) => (
                                    <tr key={m.id} className="hover:bg-slate-50/80 transition">
                                        <td className="px-5 py-3.5 font-medium text-slate-700">
                                            {m.id_number || '—'}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <div className="font-semibold text-slate-900">{m.user?.name}</div>
                                            <div className="text-[11px] text-slate-700">{m.user?.email}</div>
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <span className={`inline-flex px-2 py-0.5 rounded-full font-semibold ${
                                                m.role === 'org_admin' ? 'bg-purple-100 text-purple-800' :
                                                m.role === 'teacher' ? 'bg-blue-100 text-blue-800' :
                                                m.role === 'guardian' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'
                                            }`}>
                                                {m.role === 'org_admin' ? 'এডমিন' : (m.role === 'teacher' ? 'মুদাররিস' : (m.role === 'guardian' ? 'অভিভাবক' : 'শিক্ষার্থী'))}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-slate-700">
                                            {m.guardian?.name ? (
                                                <div>
                                                    <span className="font-medium text-slate-800">{m.guardian.name}</span>
                                                    <div className="text-[10px] text-slate-600">{m.guardian.email}</div>
                                                </div>
                                            ) : '—'}
                                        </td>
                                        <td className="px-5 py-3.5 text-slate-700">
                                            {m.joined_at ? new Date(m.joined_at).toLocaleDateString('bn-BD') : '—'}
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <button
                                                onClick={() => handleDelete(m.id)}
                                                className="text-red-700 hover:text-red-800 font-semibold"
                                            >
                                                অপসারণ
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal: Single Member Add */}
            {showAddModal && (
                <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-base font-bold text-slate-800">নতুন সদস্য যোগ করুন</h3>
                            <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <form onSubmit={handleSingleSubmit} className="space-y-4 text-xs">
                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">পূর্ণ নাম *</label>
                                <input
                                    type="text"
                                    required
                                    value={addForm.data.name}
                                    onChange={(e) => addForm.setData('name', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="মুহাম্মাদুল্লাহ বা শিক্ষার্থী নাম"
                                />
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">ইমেইল ঠিকানা *</label>
                                <input
                                    type="email"
                                    required
                                    value={addForm.data.email}
                                    onChange={(e) => addForm.setData('email', e.target.value)}
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="student@example.com"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">পদবি (রোল) *</label>
                                    <select
                                        value={addForm.data.role}
                                        onChange={(e) => addForm.setData('role', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="student">শিক্ষার্থী (Student)</option>
                                        <option value="teacher">মুদাররিস (Teacher)</option>
                                        <option value="guardian">অভিভাবক (Guardian)</option>
                                        <option value="org_admin">এডমিন (Org Admin)</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block font-semibold text-slate-700 mb-1">রোল / আইডি নম্বর</label>
                                    <input
                                        type="text"
                                        value={addForm.data.id_number}
                                        onChange={(e) => addForm.setData('id_number', e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="ম-১০১"
                                    />
                                </div>
                            </div>

                            {addForm.errors.seat_limit && (
                                <div className="p-2 bg-red-50 text-red-600 rounded text-xs">
                                    {addForm.errors.seat_limit}
                                </div>
                            )}

                            <div className="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowAddModal(false)}
                                    className="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={addForm.processing}
                                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg"
                                >
                                    {addForm.processing ? 'সংরক্ষণ হচ্ছে...' : 'যুক্ত করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal: CSV Bulk Import */}
            {showCsvModal && (
                <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-base font-bold text-slate-800">CSV বাল্ক ইম্পোর্ট</h3>
                            <button onClick={() => setShowCsvModal(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <form onSubmit={handleCsvSubmit} className="space-y-4 text-xs">
                            <div className="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-[11px] leading-relaxed">
                                <span className="font-bold">ফরম্যাট নির্দেশনা:</span><br />
                                <code>name,email,phone,role,id_number,guardian_email</code><br />
                                উদাহরণ: <br />
                                <code>আব্দুর রহমান,abdur@example.com,01700000001,student,101,parent@example.com</code>
                            </div>

                            <div>
                                <label className="block font-semibold text-slate-700 mb-1">CSV ডেটা পেস্ট করুন *</label>
                                <textarea
                                    required
                                    rows="6"
                                    value={csvForm.data.csv_text}
                                    onChange={(e) => csvForm.setData('csv_text', e.target.value)}
                                    placeholder="name,email,phone,role,id_number,guardian_email"
                                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 font-mono text-xs"
                                ></textarea>
                            </div>

                            {csvForm.errors.seat_limit && (
                                <div className="p-2 bg-red-50 text-red-600 rounded text-xs">
                                    {csvForm.errors.seat_limit}
                                </div>
                            )}

                            <div className="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowCsvModal(false)}
                                    className="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    disabled={csvForm.processing}
                                    className="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg"
                                >
                                    {csvForm.processing ? 'ইম্পোর্ট হচ্ছে...' : 'বাল্ক ইম্পোর্ট সম্পন্ন করুন'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </OrgLayout>
    );
}
