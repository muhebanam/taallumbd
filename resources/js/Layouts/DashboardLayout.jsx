import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import AppLayout from './AppLayout';

const MENUS = {
    admin: [
        { label: 'ড্যাশবোর্ড', href: '/admin/dashboard', enabled: true },
        { label: 'অ্যানালিটিক্স', href: '/admin/analytics', enabled: true },
        { label: 'এনরোলমেন্ট', href: '/admin/enrollments', enabled: true },
        { label: 'সেলস রিপোর্ট', href: '/admin/sales-reports', enabled: false, badge: 'শীঘ্রই' },
        { label: 'লেনদেন', href: '/admin/transactions', enabled: false, badge: 'শীঘ্রই' },
        { label: 'ওয়ালেট ও পেআউট', href: '/admin/payouts', enabled: true },
        { label: 'কোর্সসমূহ', href: '/admin/courses', enabled: true },
        { label: 'কোর্স বিল্ডার', href: '/admin/course-builder', enabled: false, badge: 'শীঘ্রই' },
        { label: 'কুইজ ও অ্যাসেসমেন্ট', href: '/admin/quizzes', enabled: false, badge: 'শীঘ্রই' },
        { label: 'অ্যাসাইনমেন্ট', href: '/admin/assignments', enabled: false, badge: 'শীঘ্রই' },
        { label: 'শিক্ষকমণ্ডলী', href: '/admin/teachers', enabled: true },
        { label: 'শিক্ষক আবেদন', href: '/admin/instructor-applications', enabled: true },
        { label: 'শিক্ষার্থী', href: '/admin/students', enabled: true },
        { label: 'সার্টিফিকেট বিল্ডার', href: '/admin/certificates', enabled: false, badge: 'শীঘ্রই' },
        { label: 'কুপন', href: '/admin/coupons', enabled: false, badge: 'শীঘ্রই' },
        { label: 'রিভিউ ও রেটিং', href: '/admin/reviews', enabled: false, badge: 'শীঘ্রই' },
        { label: 'মেসেজ', href: '/admin/contact-messages', enabled: false, badge: 'শীঘ্রই' },
        { label: 'নোটিশবোর্ড', href: '/admin/notices', enabled: false, badge: 'শীঘ্রই' },
        { label: 'ফাতাওয়া', href: '/admin/fatawa', enabled: true },
        { label: 'প্রবন্ধ', href: '/admin/articles', enabled: true },
        { label: 'প্রকাশনা', href: '/admin/publications', enabled: false, badge: 'শীঘ্রই' },
        { label: 'ওয়েবসাইট CMS', href: '/admin/cms', enabled: false, badge: 'শীঘ্রই' },
        { label: 'সাপোর্ট', href: '/admin/support', enabled: false, badge: 'শীঘ্রই' },
        { label: 'সেটিংস', href: '/admin/settings', enabled: false, badge: 'শীঘ্রই' },
    ],
    instructor: [
        { label: 'ড্যাশবোর্ড', href: '/instructor/dashboard', enabled: true },
        { label: 'অ্যানালিটিক্স', href: '/instructor/analytics', enabled: true },
        { label: 'আমার কোর্স', href: '/instructor/dashboard', enabled: true },
        { label: 'কোর্স বিল্ডার', href: '/instructor/courses/create', enabled: true },
        { label: 'এনরোলমেন্ট', href: '/instructor/enrollments', enabled: false, badge: 'শীঘ্রই' },
        { label: 'কুইজ ও অ্যাসাইনমেন্ট', href: '/instructor/quizzes', enabled: false, badge: 'শীঘ্রই' },
        { label: 'শিক্ষার্থী', href: '/instructor/students', enabled: false, badge: 'শীঘ্রই' },
        { label: 'আয় ও ওয়ালেট', href: '/instructor/earnings', enabled: true },
        { label: 'মেসেজ', href: '/instructor/messages', enabled: false, badge: 'শীঘ্রই' },
        { label: 'নোটিশ', href: '/instructor/notices', enabled: false, badge: 'শীঘ্রই' },
        { label: 'শিক্ষক প্রোফাইল', href: '/instructor/profile/teacher/edit', enabled: true },
        { label: 'সেটিংস', href: '/instructor/settings', enabled: false, badge: 'শীঘ্রই' },
    ],
    student: [
        { label: 'ড্যাশবোর্ড', href: '/dashboard', enabled: true },
        { label: 'অ্যানালিটিক্স', href: '/dashboard/analytics', enabled: true },
        { label: 'আমার কোর্স', href: '/dashboard/my-courses', enabled: true },
        { label: 'লেসন', href: '/dashboard/lessons', enabled: false, badge: 'শীঘ্রই' },
        { label: 'কুইজ', href: '/dashboard/quizzes', enabled: false, badge: 'শীঘ্রই' },
        { label: 'অ্যাসাইনমেন্ট', href: '/dashboard/assignments', enabled: false, badge: 'শীঘ্রই' },
        { label: 'সার্টিফিকেট', href: '/dashboard/certificates', enabled: true },
        { label: 'অর্ডার', href: '/dashboard/orders', enabled: true },
        { label: 'নোটিশ', href: '/dashboard/notices', enabled: false, badge: 'শীঘ্রই' },
        { label: 'সাপোর্ট', href: '/dashboard/support', enabled: false, badge: 'শীঘ্রই' },
        { label: 'প্রোফাইল', href: '/dashboard/profile', enabled: false, badge: 'শীঘ্রই' },
    ],
};

function MenuIcon({ label }) {
    const size = "h-5 w-5 shrink-0";
    if (label.includes('ড্যাশবোর্ড') || label.includes('ওভারভিউ')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        );
    }
    if (label.includes('অ্যানালিটিক্স') || label.includes('রিপোর্ট')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        );
    }
    if (label.includes('কোর্স')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.243.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        );
    }
    if (label.includes('এনরোলমেন্ট') || label.includes('ভর্তি')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        );
    }
    if (label.includes('শিক্ষার্থী')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        );
    }
    if (label.includes('শিক্ষক')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        );
    }
    if (label.includes('প্রবন্ধ')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l5 5v11a2 2 0 01-2 2z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 3v5h5M7 11h10M7 15h10" />
            </svg>
        );
    }
    if (label.includes('ফাতাওয়া') || label.includes('প্রশ্নোত্তর')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        );
    }
    if (label.includes('প্রকাশনা')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
        );
    }
    if (label.includes('রিপোর্ট') || label.includes('সেলস')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        );
    }
    if (label.includes('লেনদেন') || label.includes('অর্ডার')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        );
    }
    if (label.includes('ওয়ালেট') || label.includes('আয়')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        );
    }
    if (label.includes('কুইজ') || label.includes('অ্যাসেসমেন্ট')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        );
    }
    if (label.includes('অ্যাসাইনমেন্ট')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        );
    }
    if (label.includes('সার্টিফিকেট')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        );
    }
    if (label.includes('কুপন')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        );
    }
    if (label.includes('রিভিউ')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 9.72c-.773-.57-.375-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
            </svg>
        );
    }
    if (label.includes('মেসেজ') || label.includes('যোগাযোগ')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        );
    }
    if (label.includes('নোটিশ')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        );
    }
    if (label.includes('CMS')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
        );
    }
    if (label.includes('সাপোর্ট')) {
        return (
            <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        );
    }
    return (
        <svg className={size} fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    );
}

export default function DashboardLayout({ title, children }) {
    const { auth } = usePage().props;
    const menu = MENUS[auth?.user?.role] ?? MENUS.student;
    const current = typeof window !== 'undefined' ? window.location.pathname : '';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);

    const profileHref = auth?.user?.role === 'instructor' 
        ? '/instructor/profile/teacher/edit' 
        : auth?.user?.role === 'student'
            ? '/dashboard'
            : '#';

    const roleNames = {
        admin: 'অ্যাডমিন',
        instructor: 'শিক্ষক',
        student: 'শিক্ষার্থী',
    };

    const renderMenuItem = (item) => {
        const isActive = current === item.href || (item.href !== '/dashboard' && current.startsWith(item.href));
        if (item.enabled) {
            return (
                <Link
                    key={item.label}
                    href={item.href}
                    onClick={() => setSidebarOpen(false)}
                    className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300 ${
                        isActive
                            ? 'bg-brand-deep text-brand-cream shadow-md border-r-4 border-brand-cream font-bold'
                            : 'text-white/80 hover:bg-white/10 hover:text-white'
                    }`}
                >
                    <MenuIcon label={item.label} />
                    <span className="flex-1">{item.label}</span>
                </Link>
            );
        } else {
            return (
                <div
                    key={item.label}
                    className="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/40 cursor-not-allowed select-none"
                    title="শীঘ্রই আসছে"
                >
                    <MenuIcon label={item.label} />
                    <span className="flex-1 text-white/30">{item.label}</span>
                    {item.badge && (
                        <span className="rounded bg-brand-deep/50 px-1.5 py-0.5 text-[9px] font-bold text-brand-cream uppercase tracking-wide">
                            {item.badge}
                        </span>
                    )}
                </div>
            );
        }
    };

    return (
        <AppLayout>
            <div className="min-h-screen bg-brand-light flex">
                {/* Desktop Sidebar */}
                <aside className="hidden w-64 shrink-0 bg-brand text-white border-r border-brand-deep flex-col p-4 md:flex min-h-screen shadow-lg">
                    <div className="sticky top-20">
                        <div className="mb-6 px-4 py-3 bg-brand-deep/50 rounded-2xl border border-white/5">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-brand-cream/60">
                                কন্ট্রোল প্যানেল
                            </p>
                            <h3 className="text-md font-bold text-white mt-1">
                                {roleNames[auth?.user?.role] || 'শিক্ষার্থী'} প্যানেল
                            </h3>
                        </div>
                        <nav className="space-y-1">
                            {menu.map(renderMenuItem)}
                        </nav>
                    </div>
                </aside>

                {/* Mobile Drawer Sidebar */}
                {sidebarOpen && (
                    <div className="fixed inset-0 z-50 flex md:hidden">
                        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onClick={() => setSidebarOpen(false)} />
                        <aside className="relative flex w-64 max-w-xs flex-col bg-brand text-white p-4 shadow-2xl z-10 transition-all duration-300">
                            <div className="flex items-center justify-between mb-6 px-2">
                                <span className="text-lg font-bold text-brand-cream">আত-তাআল্লুম</span>
                                <button type="button" onClick={() => setSidebarOpen(false)} className="rounded-lg p-1 text-white/80 hover:bg-white/10">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <nav className="space-y-1 overflow-y-auto flex-1 pb-10">
                                {menu.map(renderMenuItem)}
                            </nav>
                        </aside>
                    </div>
                )}

                {/* Main Content Area */}
                <div className="flex-1 flex flex-col min-w-0">
                    {/* Topbar */}
                    <header className="bg-white border-b border-brand/5 shadow-sm px-6 py-4 flex items-center justify-between gap-4 sticky top-0 z-20">
                        {/* Mobile Hamburger */}
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(true)}
                            className="p-2 rounded-xl border border-brand/10 text-brand-deep md:hidden hover:bg-brand-light transition"
                        >
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {/* Search Box */}
                        <div className="relative max-w-xs w-full hidden sm:block">
                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg className="h-5 w-5 text-brand-text/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                placeholder="অনুসন্ধান করুন..."
                                className="w-full pl-10 pr-4 py-2 text-sm rounded-xl border-brand/10 bg-brand-light focus:border-brand focus:ring-brand"
                            />
                        </div>

                        {/* Right Area: Notification + Profile Dropdown */}
                        <div className="flex items-center gap-4 ml-auto sm:ml-0">
                            {/* Notification Icon */}
                            <button type="button" className="p-2 text-brand-text/70 hover:text-brand hover:bg-brand-light rounded-xl transition relative">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span className="absolute top-1 right-1 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                            </button>

                            {/* User Profile Dropdown */}
                            <div className="relative">
                                <button
                                    type="button"
                                    onClick={() => setProfileDropdownOpen((v) => !v)}
                                    className="flex items-center gap-2.5 rounded-xl border border-brand/5 p-1.5 hover:bg-brand-light transition focus:outline-none"
                                >
                                    <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand font-bold text-brand-cream text-sm">
                                        {auth?.user?.name?.charAt(0)}
                                    </span>
                                    <div className="hidden sm:block text-right">
                                        <p className="text-xs font-bold text-brand-deep leading-3">{auth?.user?.name}</p>
                                        <span className="inline-block mt-0.5 rounded bg-brand-light px-1 text-[9px] font-semibold text-brand">
                                            {roleNames[auth?.user?.role] || 'শিক্ষার্থী'}
                                        </span>
                                    </div>
                                    <svg className={`h-4 w-4 text-brand-text/60 transition ${profileDropdownOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                {profileDropdownOpen && (
                                    <>
                                        <div className="fixed inset-0 z-30" onClick={() => setProfileDropdownOpen(false)} />
                                        <div className="absolute right-0 top-full z-40 mt-2 w-48 rounded-xl border border-brand/10 bg-white py-1.5 shadow-lg">
                                            {auth?.user?.role !== 'admin' && profileHref !== '#' ? (
                                                <Link
                                                    href={profileHref}
                                                    onClick={() => setProfileDropdownOpen(false)}
                                                    className="block px-4 py-2 text-sm text-brand-text hover:bg-brand-light font-medium"
                                                >
                                                    আমার প্রোফাইল
                                                </Link>
                                            ) : (
                                                <div className="block px-4 py-2 text-sm text-white/40 cursor-not-allowed select-none font-medium">
                                                    আমার প্রোফাইল <span className="text-[10px] text-brand/50">(শীঘ্রই)</span>
                                                </div>
                                            )}
                                            <div className="block px-4 py-2 text-sm text-white/40 cursor-not-allowed select-none font-medium">
                                                সেটিংস <span className="text-[10px] text-brand/50">(শীঘ্রই)</span>
                                            </div>
                                            <hr className="my-1 border-brand/5" />
                                            <Link
                                                href="/logout"
                                                method="post"
                                                as="button"
                                                onClick={() => setProfileDropdownOpen(false)}
                                                className="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50 font-medium"
                                            >
                                                লগআউট
                                            </Link>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </header>

                    {/* Main Content Body */}
                    <main className="flex-1 p-6 md:p-8">
                        {title && (
                            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                                <h1 className="text-2xl font-bold text-brand-deep tracking-tight">{title}</h1>
                            </div>
                        )}
                        {children}
                    </main>
                </div>
            </div>
        </AppLayout>
    );
}
