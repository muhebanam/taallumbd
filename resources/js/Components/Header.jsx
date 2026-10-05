import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import CourseMegaMenu from './CourseMegaMenu';
import DropdownMenu from './DropdownMenu';
import MobileMenu from './MobileMenu';
import UserMenu from './UserMenu';
import { publicationsMenu, aboutMenu } from '../data/menu';

export default function Header() {
    const { auth, navCategories } = usePage().props;
    const [mobileOpen, setMobileOpen] = useState(false);

    const articleItems = (Array.isArray(navCategories?.article) ? navCategories.article : []).map((c) => ({ label: c.name, href: `/articles/category/${c.slug}` }));
    const fatwaItems = (Array.isArray(navCategories?.fatwa) ? navCategories.fatwa : []).map((c) => ({
        label: c.name,
        href: `/fatawa/category/${c.slug}`,
        children: Array.isArray(c.children) && c.children.length ? c.children.map((ch) => ({ label: ch.name, href: `/fatawa/category/${ch.slug}` })) : undefined,
    }));

    return (
        <header className="sticky top-0 z-40 bg-brand shadow-lg">
            <div className="relative mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
                {/* Logo */}
                <Link href="/" className="flex shrink-0 items-center gap-2">
                    <img src="/images/logo.png" alt="আত-তাআল্লুম" className="h-11 w-11 rounded-full bg-white/10 object-contain" />
                    <span className="hidden text-lg font-bold text-brand-cream sm:block">আত-তাআল্লুম</span>
                </Link>

                {/* Desktop nav */}
                <nav className="hidden items-center gap-1 lg:flex" aria-label="প্রধান মেনু">
                    <Link href="/" className="rounded-lg px-3 py-2 text-sm font-medium text-white transition hover:text-brand-cream">হোম</Link>
                    <CourseMegaMenu />
                    <DropdownMenu label="প্রবন্ধ" items={articleItems} />
                    <DropdownMenu
                        label="ফাতাওয়া"
                        items={fatwaItems}
                        extraItem={
                            <Link href="/fatawa/ask" className="mt-1 block border-t border-brand/10 px-4 py-2 text-sm font-semibold text-brand hover:bg-brand-light">
                                প্রশ্ন করুন
                            </Link>
                        }
                    />
                    <DropdownMenu label="প্রকাশনা" items={publicationsMenu} />
                    <DropdownMenu label="পরিচিতি" items={aboutMenu} />
                    <Link href="/contact" className="rounded-lg px-3 py-2 text-sm font-medium text-white transition hover:text-brand-cream">যোগাযোগ</Link>
                </nav>

                {/* Right side */}
                <div className="flex items-center gap-2">
                    {auth?.user ? (
                        <UserMenu user={auth.user} />
                    ) : (
                        <div className="hidden items-center gap-2 lg:flex">
                            <Link href="/login" className="rounded-xl border border-brand-cream px-4 py-2 text-sm font-semibold text-brand-cream transition hover:bg-brand-cream hover:text-brand-deep">লগইন</Link>
                            <Link href="/register" className="btn-accent !px-4 !py-2 text-sm">রেজিস্ট্রেশন</Link>
                        </div>
                    )}
                    {/* Hamburger */}
                    <button
                        type="button"
                        className="rounded-lg p-2 text-white lg:hidden"
                        onClick={() => setMobileOpen((v) => !v)}
                        aria-label="মেনু খুলুন" aria-expanded={mobileOpen}
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {mobileOpen
                                ? <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                : <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />}
                        </svg>
                    </button>
                </div>
            </div>
            {mobileOpen && <MobileMenu user={auth?.user} onClose={() => setMobileOpen(false)} />}
        </header>
    );
}
