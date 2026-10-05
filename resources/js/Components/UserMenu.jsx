import { useState } from 'react';
import { Link } from '@inertiajs/react';

export default function UserMenu({ user }) {
    const [open, setOpen] = useState(false);
    const dashboardHref = user.role === 'admin' ? '/admin/dashboard' : user.role === 'instructor' ? '/instructor/dashboard' : '/dashboard';

    return (
        <div className="relative">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="flex items-center gap-2 rounded-full bg-brand-deep px-3 py-1.5 text-sm text-white transition hover:bg-black/30 focus:outline-none focus:ring-2 focus:ring-brand-cream"
            >
                <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-cream font-bold text-brand-deep">
                    {user.name?.charAt(0)}
                </span>
                <span className="hidden sm:block">{user.name}</span>
            </button>
            {open && (
                <div className="absolute right-0 top-full z-50 mt-2 w-52 rounded-xl border border-brand/10 bg-white py-2 shadow-cardHover">
                    <Link href={dashboardHref} className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">ড্যাশবোর্ড</Link>
                    <Link href="/dashboard/my-courses" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">আমার কোর্সসমূহ</Link>
                    <Link href="/dashboard/certificates" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">আমার সার্টিফিকেট</Link>
                    <Link href="/dashboard/my-questions" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">আমার দ্বীনি প্রশ্নসমূহ</Link>
                    <Link href="/dashboard/orders" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">অর্ডার ও রসিদ</Link>
                    <Link href="/dashboard/notifications" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">নোটিফিকেশন</Link>
                    <Link href="/profile/notifications" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">নোটিফিকেশন পছন্দ</Link>
                    <Link href="/profile" className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light">প্রোফাইল</Link>
                    <Link href="/logout" method="post" as="button" className="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50 border-t border-gray-100 mt-1">লগআউট</Link>
                </div>
            )}
        </div>
    );
}
