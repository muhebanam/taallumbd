import { Head, Link } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function Certificates({ certificates }) {
    return (
        <DashboardLayout title="আমার সার্টিফিকেট">
            <Head title="সার্টিফিকেট" />
            {certificates.length ? (
                <div className="grid gap-4 md:grid-cols-2">
                    {certificates.map((c) => (
                        <div key={c.id} className="card p-5">
                            <h2 className="font-bold text-brand-deep">{c.course?.title}</h2>
                            <p className="mt-1 text-sm text-brand-text/60">নং: {c.certificate_no} • {new Date(c.issued_at).toLocaleDateString('bn-BD')}</p>
                            <Link href={`/certificates/${c.id}`} className="btn-primary mt-4 !py-2 text-sm">সার্টিফিকেট দেখুন</Link>
                        </div>
                    ))}
                </div>
            ) : <p className="card p-8 text-center text-brand-text/60">কোর্স ১০০% সম্পন্ন করে সব কুইজে পাস করলে সার্টিফিকেট পাবেন।</p>}
        </DashboardLayout>
    );
}
