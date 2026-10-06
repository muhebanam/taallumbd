import React from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function OrgLayout({ children, title }) {
    const { tenant, org_member, auth, flash } = usePage().props;
    const org = tenant || {};
    const subdomain = org.subdomain || 'org';

    const navItems = [
        { name: 'ড্যাশবোর্ড', route: 'dashboard', href: `/org/${subdomain}/dashboard`, icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { name: 'সদস্য ও সিট', route: 'members', href: `/org/${subdomain}/members`, icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
        { name: 'শ্রেণি ও হালাকা', route: 'cohorts', href: `/org/${subdomain}/cohorts`, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
        { name: 'দৈনিক হাজিরা', route: 'attendance', href: `/org/${subdomain}/attendance`, icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
        { name: 'পরীক্ষা ও মূল্যায়ন', route: 'exams', href: `/org/${subdomain}/exams`, icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
        { name: 'রিপোর্ট কার্ড ও গ্রেড', route: 'gradebook', href: `/org/${subdomain}/gradebook`, icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
        { name: 'সনদপত্র (সার্টিফিকেট)', route: 'certificates', href: `/org/${subdomain}/certificates`, icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
        { name: 'অভিভাবক পোর্টাল', route: 'guardian', href: `/org/${subdomain}/guardian`, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    ];

    const currentUrl = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900 font-sans antialiased">
            {/* Top Brand Bar */}
            <header className="bg-white border-b border-emerald-100 sticky top-0 z-40 shadow-xs">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-16">
                        {/* Org Identity */}
                        <div className="flex items-center space-x-3 rtl:space-x-reverse">
                            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-700 to-teal-800 text-white flex items-center justify-center font-bold text-lg shadow-sm border border-emerald-600">
                                {org.branding?.logo_url ? (
                                    <img src={org.branding.logo_url} alt={org.name} className="w-10 h-10 rounded-xl object-contain" />
                                ) : (
                                    <span>{org.name ? org.name.charAt(0) : 'ম'}</span>
                                )}
                            </div>
                            <div>
                                <div className="flex items-center space-x-2 rtl:space-x-reverse">
                                    <h1 className="text-base font-bold text-slate-800 leading-tight">
                                        {org.name || 'প্রতিষ্ঠানের নাম'}
                                    </h1>
                                    <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {org.type === 'madrasah' ? 'মাদ্রাসা' : (org.type === 'mosque_maktab' ? 'মসজিদ মক্তব' : 'ইনস্টিটিউট')}
                                    </span>
                                </div>
                                <div className="text-xs text-slate-700 flex items-center space-x-2 rtl:space-x-reverse">
                                    <span>{org.subdomain}.taallumbd.com</span>
                                    <span>•</span>
                                    <span className="font-medium text-emerald-800">
                                        সিট ব্যবহার: {org.used_seats || 0}/{org.seat_limit || 50}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Right Quick Actions & Profile */}
                        <div className="flex items-center space-x-3 rtl:space-x-reverse">
                            <Link
                                href="/dashboard"
                                className="text-xs font-medium text-slate-700 hover:text-emerald-800 transition px-2.5 py-1.5 rounded-md hover:bg-slate-100"
                            >
                                মূল প্ল্যাটফর্মে ফিরুন →
                            </Link>

                            <div className="border-l border-slate-200 h-6"></div>

                            <div className="flex items-center space-x-2 rtl:space-x-reverse">
                                <div className="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">
                                    {auth?.user?.name ? auth.user.name.charAt(0) : 'U'}
                                </div>
                                <div className="hidden sm:block text-right">
                                    <div className="text-xs font-semibold text-slate-800">{auth?.user?.name}</div>
                                    <div className="text-[11px] text-slate-700 capitalize">
                                        {org_member?.role === 'org_admin' ? 'এডমিন' : (org_member?.role === 'teacher' ? 'মুদাররিস' : (org_member?.role === 'guardian' ? 'অভিভাবক' : 'শিক্ষার্থী'))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Navigation Bar */}
                <div className="bg-emerald-900 border-t border-emerald-800 text-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <nav className="flex space-x-1 sm:space-x-4 rtl:space-x-reverse overflow-x-auto py-1 scrollbar-none">
                            {navItems.map((item) => {
                                const isActive = currentUrl.includes(item.route);
                                return (
                                    <Link
                                        key={item.route}
                                        href={item.href}
                                        className={`inline-flex items-center px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap ${
                                            isActive
                                                ? 'bg-emerald-800 text-amber-300 font-semibold shadow-inner'
                                                : 'text-emerald-100 hover:bg-emerald-800/60 hover:text-white'
                                        }`}
                                    >
                                        <svg className="w-4 h-4 mr-1.5 rtl:ml-1.5 rtl:mr-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={item.icon} />
                                        </svg>
                                        {item.name}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                </div>
            </header>

            {/* Flash Messages */}
            {flash?.success && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div className="p-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
                        <div className="flex items-center space-x-2 rtl:space-x-reverse">
                            <svg className="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{flash.success}</span>
                        </div>
                    </div>
                </div>
            )}
            {flash?.error && (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div className="p-3 bg-red-50 border border-red-300 text-red-800 rounded-lg text-sm flex items-center">
                        <span>{flash.error}</span>
                    </div>
                </div>
            )}

            {/* Main Page Content */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {children}
            </main>
        </div>
    );
}
