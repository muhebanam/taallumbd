import { Head } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function Enrollments({ enrollments }) {
    return (
        <DashboardLayout title="এনরোলমেন্ট">
            <Head title="এনরোলমেন্ট" />
            <div className="card overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="bg-brand text-brand-cream">
                        <tr><th className="px-4 py-3">শিক্ষার্থী</th><th className="px-4 py-3">কোর্স</th><th className="px-4 py-3">অগ্রগতি</th><th className="px-4 py-3">অবস্থা</th></tr>
                    </thead>
                    <tbody className="divide-y divide-brand/5">
                        {enrollments.data.map((en) => (
                            <tr key={en.id}>
                                <td className="px-4 py-3">{en.user?.name} <span className="text-xs text-brand-text/50">({en.user?.email})</span></td>
                                <td className="px-4 py-3">{en.course?.title}</td>
                                <td className="px-4 py-3">{en.progress}%</td>
                                <td className="px-4 py-3">{en.status === 'completed' ? '✅ সম্পন্ন' : en.status === 'active' ? 'চলমান' : 'বাতিল'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={enrollments.links} />
        </DashboardLayout>
    );
}
