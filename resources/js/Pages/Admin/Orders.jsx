import { Head } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function Orders({ orders }) {
    return (
        <DashboardLayout title="অর্ডার ও পেমেন্ট">
            <Head title="অর্ডার" />
            <div className="card overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="bg-brand text-brand-cream">
                        <tr><th className="px-4 py-3">#</th><th className="px-4 py-3">ইউজার</th><th className="px-4 py-3">কোর্স</th><th className="px-4 py-3">পরিমাণ</th><th className="px-4 py-3">পদ্ধতি</th><th className="px-4 py-3">TXN</th><th className="px-4 py-3">অবস্থা</th></tr>
                    </thead>
                    <tbody className="divide-y divide-brand/5">
                        {orders.data.map((o) => (
                            <tr key={o.id}>
                                <td className="px-4 py-3">{o.id}</td>
                                <td className="px-4 py-3">{o.user?.name}</td>
                                <td className="px-4 py-3">{o.course?.title}</td>
                                <td className="px-4 py-3">৳ {Number(o.amount).toFixed(0)}</td>
                                <td className="px-4 py-3">{o.payment_method ?? '-'}</td>
                                <td className="px-4 py-3 text-xs">{o.payments?.[0]?.transaction_id ?? '-'}</td>
                                <td className="px-4 py-3">{o.status === 'paid' ? '✅ পরিশোধিত' : o.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={orders.links} />
        </DashboardLayout>
    );
}
