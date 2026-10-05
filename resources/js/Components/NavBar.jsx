import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import UserMenu from './UserMenu';
import CourseMegaMenu from './CourseMegaMenu';
import NotificationBell from './NotificationBell';

export default function NavBar() {
    const { auth } = usePage().props;
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <nav className="sticky top-0 z-50 bg-[#1A2E2F] border-b border-[#254244] shadow-md">
            <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                {/* Logo & Brand Name */}
                <div className="flex items-center gap-3">
                    <Link href="/" className="flex items-center gap-2.5 transition-transform hover:scale-105">
                        <img 
                            src="/images/logo.svg" 
                            alt="আত-তাআল্লুম" 
                            onError={(e) => {
                                e.target.onerror = null;
                                e.target.src = '/images/logo.png';
                            }}
                            className="h-10 w-10 rounded-full bg-white/10 p-0.5 object-contain" 
                        />
                        <div className="flex flex-col">
                            <span className="text-xl font-bold tracking-tight text-[#FFF99A] font-bangla">আত-তাআল্লুম</span>
                            <span className="text-[10px] text-white/70 -mt-1 tracking-wider uppercase">TaallumBD Digital Academy</span>
                        </div>
                    </Link>
                </div>

                {/* Desktop Navigation Links */}
                <div className="hidden lg:flex items-center gap-1 xl:gap-2">
                    <Link 
                        href="/" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        হোম
                    </Link>

                    <CourseMegaMenu />

                    <Link 
                        href="/about/teachers" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        শিক্ষকমণ্ডলী
                    </Link>

                    <Link 
                        href="/quran" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        কুরআন
                    </Link>

                    <Link 
                        href="/hadith" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        হাদীস
                    </Link>

                    <Link 
                        href="/fatawa" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        ফাতাওয়া
                    </Link>

                    <Link 
                        href="/articles" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        প্রবন্ধ
                    </Link>

                    <Link 
                        href="/publications" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        প্রকাশনা
                    </Link>

                    <Link 
                        href="/community" 
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        কমিউনিটি
                    </Link>
                </div>

                {/* Right Side: Auth / CTA */}
                <div className="hidden sm:flex items-center gap-3">
                    {auth?.user ? (
                        <>
                            <NotificationBell />
                            <UserMenu user={auth.user} />
                        </>
                    ) : (
                        <div className="flex items-center gap-2.5">
                            <Link 
                                href="/login" 
                                className="rounded-xl border border-[#FFF99A]/40 px-4 py-2 text-xs font-bold text-[#FFF99A] transition hover:bg-[#FFF99A] hover:text-[#102526]"
                            >
                                লগইন
                            </Link>
                            <Link 
                                href="/register" 
                                className="rounded-xl bg-[#FFF99A] px-4 py-2 text-xs font-bold text-[#102526] shadow-sm transition hover:bg-[#fff780] hover:shadow-md"
                            >
                                যোগ দিন
                            </Link>
                        </div>
                    )}
                </div>

                {/* Mobile Menu Toggle Button */}
                <div className="flex lg:hidden items-center gap-2">
                    {auth?.user && <div className="sm:hidden flex items-center gap-1"><NotificationBell /><UserMenu user={auth.user} /></div>}
                    <button
                        type="button"
                        onClick={() => setMobileOpen(!mobileOpen)}
                        className="rounded-xl p-2 text-white/90 hover:bg-white/10 transition"
                        aria-label="মেনু টগল"
                    >
                        {mobileOpen ? (
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        ) : (
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        )}
                    </button>
                </div>
            </div>

            {/* Mobile Menu Slide-over */}
            {mobileOpen && (
                <div className="lg:hidden border-t border-[#254244] bg-[#102526] px-4 pt-3 pb-6 space-y-1">
                    <Link
                        href="/"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        হোম
                    </Link>
                    <Link
                        href="/courses"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        কোর্সসমূহ
                    </Link>
                    <Link
                        href="/about/teachers"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        শিক্ষকমণ্ডলী
                    </Link>
                    <Link
                        href="/fatawa"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        ফাতাওয়া ও প্রশ্নোত্তর
                    </Link>
                    <Link
                        href="/quran"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        কুরআনুল কারীম
                    </Link>
                    <Link
                        href="/hadith"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        হাদীস সম্ভার
                    </Link>
                    <Link
                        href="/articles"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        প্রবন্ধ সম্ভার
                    </Link>
                    <Link
                        href="/publications"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        প্রকাশনা (বই ও অডিও)
                    </Link>
                    <Link
                        href="/community"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10 hover:text-[#FFF99A]"
                    >
                        কমিউনিটি ফোরাম
                    </Link>
                    <Link
                        href="/become-instructor"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-amber-300 hover:bg-white/10"
                    >
                        শিক্ষক হিসেবে আবেদন
                    </Link>
                    <Link
                        href="/contact"
                        onClick={() => setMobileOpen(false)}
                        className="block rounded-lg px-3 py-2 text-base font-medium text-white hover:bg-white/10"
                    >
                        যোগাযোগ
                    </Link>

                    {!auth?.user && (
                        <div className="pt-4 border-t border-white/10 flex gap-3">
                            <Link
                                href="/login"
                                onClick={() => setMobileOpen(false)}
                                className="flex-1 text-center rounded-xl border border-[#FFF99A] py-2 text-sm font-semibold text-[#FFF99A]"
                            >
                                লগইন
                            </Link>
                            <Link
                                href="/register"
                                onClick={() => setMobileOpen(false)}
                                className="flex-1 text-center rounded-xl bg-[#FFF99A] py-2 text-sm font-bold text-[#102526]"
                            >
                                রেজিস্টার
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </nav>
    );
}
