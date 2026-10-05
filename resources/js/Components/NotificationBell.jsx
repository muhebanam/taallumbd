import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

export default function NotificationBell() {
    const { notifications = { unread_count: 0, latest: [] } } = usePage().props;
    const [open, setOpen] = useState(false);
    const unread = Number(notifications.unread_count || 0);

    return (
        <div className="relative">
            <button
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-label="নোটিফিকেশন খুলুন"
                aria-expanded={open}
                className="relative rounded-xl p-2 text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-[#FFF99A]"
            >
                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                </svg>
                {unread > 0 && (
                    <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#FFF99A] px-1 text-[10px] font-bold text-[#102526]">
                        {unread > 99 ? '99+' : unread}
                    </span>
                )}
            </button>
            {open && (
                <div className="fixed right-2 top-[4.25rem] z-50 w-[min(22rem,calc(100vw-1rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-800 shadow-2xl sm:absolute sm:right-0 sm:top-full sm:mt-2">
                    <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <h3 className="font-bold">নোটিফিকেশন</h3>
                        {unread > 0 && (
                            <button onClick={() => router.post('/dashboard/notifications/read-all')} className="text-xs font-semibold text-emerald-700">
                                সব পড়া হয়েছে
                            </button>
                        )}
                    </div>
                    <div className="max-h-80 overflow-y-auto">
                        {notifications.latest?.length ? notifications.latest.map((item) => (
                            <Link
                                key={item.id}
                                href={item.url || '/dashboard/notifications'}
                                className={`block border-b border-slate-100 px-4 py-3 hover:bg-emerald-50 ${item.read_at ? '' : 'bg-amber-50/60'}`}
                            >
                                <p className="text-sm font-semibold">{item.title}</p>
                                <p className="mt-1 text-xs leading-5 text-slate-600">{item.message}</p>
                                <p className="mt-1 text-[10px] text-slate-400">{item.created_at}</p>
                            </Link>
                        )) : <p className="px-4 py-8 text-center text-sm text-slate-500">এখনো কোনো নোটিফিকেশন নেই</p>}
                    </div>
                    <Link href="/dashboard/notifications" className="block bg-slate-50 px-4 py-3 text-center text-sm font-bold text-emerald-800 hover:bg-emerald-50">
                        সব নোটিফিকেশন দেখুন
                    </Link>
                </div>
            )}
        </div>
    );
}
