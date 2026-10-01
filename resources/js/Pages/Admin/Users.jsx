import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function Users({ users, filters }) {
    const setRole = (user, role) => router.put(`/admin/users/${user.id}/role`, { role });

    return (
        <DashboardLayout title="ইউজার ব্যবস্থাপনা">
            <Head title="ইউজার" />
            <div className="mb-4 flex flex-wrap gap-2">
                {['', 'admin', 'instructor', 'student'].map((r) => (
                    <button key={r} onClick={() => router.get('/admin/users', r ? { role: r } : {})} className={`rounded-full px-4 py-1.5 text-sm font-medium ${(filters.role ?? '') === r ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>
                        {r === '' ? 'সব' : r === 'admin' ? 'অ্যাডমিন' : r === 'instructor' ? 'ইন্সট্রাক্টর' : 'শিক্ষার্থী'}
                    </button>
                ))}
            </div>
            <div className="card overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="bg-brand text-brand-cream">
                        <tr><th className="px-4 py-3">নাম</th><th className="px-4 py-3">ইমেইল</th><th className="px-4 py-3">ভূমিকা</th><th className="px-4 py-3">অ্যাকশন</th></tr>
                    </thead>
                    <tbody className="divide-y divide-brand/5">
                        {users.data.map((u) => (
                            <tr key={u.id}>
                                <td className="px-4 py-3 font-medium">{u.name}</td>
                                <td className="px-4 py-3 text-brand-text/70">{u.email}</td>
                                <td className="px-4 py-3">
                                    <select value={u.role} onChange={(e) => setRole(u, e.target.value)} className="rounded-lg border-brand/20 text-sm focus:border-brand focus:ring-brand">
                                        <option value="student">শিক্ষার্থী</option>
                                        <option value="instructor">ইন্সট্রাক্টর</option>
                                        <option value="admin">অ্যাডমিন</option>
                                    </select>
                                </td>
                                <td className="px-4 py-3">
                                    <button onClick={() => confirm('নিশ্চিত?') && router.delete(`/admin/users/${u.id}`)} className="text-sm font-semibold text-red-600 hover:underline">ডিলিট</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </DashboardLayout>
    );
}
