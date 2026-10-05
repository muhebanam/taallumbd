import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function Index({ notifications }) {
    return (
        <MainLayout>
            <Head title="নোটিফিকেশন" />
            <section className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
                <div className="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <p className="text-sm font-semibold text-emerald-700">আপডেট ও বার্তা</p>
                        <h1 className="mt-1 text-3xl font-black text-slate-900">নোটিফিকেশন</h1>
                    </div>
                    <button onClick={() => router.post('/dashboard/notifications/read-all')} className="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        সব পড়া হয়েছে
                    </button>
                </div>
                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    {notifications.data?.length ? notifications.data.map((item) => (
                        <div key={item.id} className={`flex gap-4 border-b border-slate-100 p-4 last:border-0 ${item.read_at ? '' : 'bg-amber-50/60'}`}>
                            <div className="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-600" />
                            <div className="min-w-0 flex-1">
                                <Link href={item.url || '#'} className="font-bold text-slate-900 hover:text-emerald-700">{item.title}</Link>
                                <p className="mt-1 text-sm leading-6 text-slate-600">{item.message}</p>
                                <p className="mt-1 text-xs text-slate-400">{item.created_at}</p>
                            </div>
                            {!item.read_at && <button onClick={() => router.post(`/dashboard/notifications/${item.id}/read`)} className="self-center text-xs font-semibold text-emerald-700">পড়া হয়েছে</button>}
                        </div>
                    )) : <p className="px-6 py-16 text-center text-slate-500">আপনার জন্য নতুন কোনো বার্তা নেই।</p>}
                </div>
                {notifications.links && <div className="mt-5 flex flex-wrap justify-center gap-2">{notifications.links.map((link, index) => link.url ? <Link key={index} href={link.url} className={`rounded-lg px-3 py-2 text-sm ${link.active ? 'bg-emerald-700 text-white' : 'border border-slate-200'}`} dangerouslySetInnerHTML={{ __html: link.label }} /> : null)}</div>}
            </section>
        </MainLayout>
    );
}
