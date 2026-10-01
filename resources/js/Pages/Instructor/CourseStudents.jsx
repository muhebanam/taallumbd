import { Head } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function CourseStudents({ course, enrollments }) {
    return (
        <DashboardLayout title={`শিক্ষার্থী তালিকা — ${course.title}`}>
            <Head title="শিক্ষার্থী তালিকা" />
            <div className="card overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="bg-brand text-brand-cream">
                        <tr><th className="px-4 py-3">নাম</th><th className="px-4 py-3">ইমেইল</th><th className="px-4 py-3">অগ্রগতি</th><th className="px-4 py-3">অবস্থা</th></tr>
                    </thead>
                    <tbody className="divide-y divide-brand/5">
                        {enrollments.data.map((en) => (
                            <tr key={en.id}>
                                <td className="px-4 py-3 font-medium">{en.user?.name}</td>
                                <td className="px-4 py-3 text-brand-text/70">{en.user?.email}</td>
                                <td className="px-4 py-3">{en.progress}%</td>
                                <td className="px-4 py-3">{en.status === 'completed' ? '✅ সম্পন্ন' : 'চলমান'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={enrollments.links} />
        </DashboardLayout>
    );
}
