import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

export default function ContactMessages({ messages }) {
    return (
        <DashboardLayout title="যোগাযোগ বার্তাসমূহ">
            <Head title="যোগাযোগ বার্তা" />
            <div className="card divide-y divide-brand/5">
                {messages.data.map((m) => (
                    <div key={m.id} className={`p-4 ${m.status === 'unread' ? 'bg-brand-cream/20' : ''}`}>
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <p className="font-semibold text-brand-deep">{m.subject}</p>
                            {m.status === 'unread' && (
                                <button onClick={() => router.put(`/admin/contact-messages/${m.id}/read`)} className="text-sm font-semibold text-brand hover:underline">পঠিত চিহ্নিত করুন</button>
                            )}
                        </div>
                        <p className="text-sm text-brand-text/60">{m.name} • {m.email}{m.phone ? ` • ${m.phone}` : ''}</p>
                        <p className="mt-2 whitespace-pre-line text-sm">{m.message}</p>
                    </div>
                ))}
                {!messages.data.length && <p className="p-8 text-center text-brand-text/60">কোনো বার্তা নেই।</p>}
            </div>
            <Pagination links={messages.links} />
        </DashboardLayout>
    );
}
