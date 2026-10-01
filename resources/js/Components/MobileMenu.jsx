import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { publicationsMenu, aboutMenu } from '../data/menu';

function Expandable({ label, children }) {
    const [open, setOpen] = useState(false);
    return (
        <div className="border-b border-white/10">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="flex w-full items-center justify-between px-4 py-3 text-left font-medium text-white"
                aria-expanded={open}
            >
                {label}
                <svg className={`h-4 w-4 transition ${open ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
            </button>
            {open && <div className="bg-black/20 pb-2">{children}</div>}
        </div>
    );
}

const MobileLink = ({ href, children, indent = false }) => (
    <Link href={href} className={`block py-2 text-sm text-white/85 hover:text-brand-cream ${indent ? 'pl-10' : 'pl-6'}`}>
        {children}
    </Link>
);

export default function MobileMenu({ user, onClose }) {
    const { navCategories } = usePage().props;

    return (
        <nav className="border-t border-white/10 bg-brand-deep lg:hidden" aria-label="মোবাইল মেনু">
            <Link href="/" className="block border-b border-white/10 px-4 py-3 font-medium text-white" onClick={onClose}>হোম</Link>

            <Expandable label="কোর্সসমূহ">
                {(Array.isArray(navCategories?.course) ? navCategories.course : []).map((c) => <MobileLink key={c.id} href={`/courses/category/${c.slug}`}>{c.name}</MobileLink>)}
                <MobileLink href="/courses">সব কোর্স দেখুন</MobileLink>
            </Expandable>

            <Expandable label="প্রবন্ধ">
                {(Array.isArray(navCategories?.article) ? navCategories.article : []).map((c) => <MobileLink key={c.id} href={`/articles/category/${c.slug}`}>{c.name}</MobileLink>)}
            </Expandable>

            <Expandable label="ফাতাওয়া">
                {(Array.isArray(navCategories?.fatwa) ? navCategories.fatwa : []).map((c) => (
                    <div key={c.id}>
                        <MobileLink href={`/fatawa/category/${c.slug}`}>{c.name}</MobileLink>
                        {c.children?.map((child) => <MobileLink key={child.id} href={`/fatawa/category/${child.slug}`} indent>{child.name}</MobileLink>)}
                    </div>
                ))}
                <MobileLink href="/fatawa/ask">প্রশ্ন করুন</MobileLink>
            </Expandable>

            <Expandable label="প্রকাশনা">
                {publicationsMenu.map((item) => (
                    <div key={item.label}>
                        <MobileLink href={item.href}>{item.label}</MobileLink>
                        {item.children?.map((child) => <MobileLink key={child.label} href={child.href} indent>{child.label}</MobileLink>)}
                    </div>
                ))}
            </Expandable>

            <Expandable label="পরিচিতি">
                {aboutMenu.map((item) => <MobileLink key={item.label} href={item.href}>{item.label}</MobileLink>)}
            </Expandable>

            <Link href="/contact" className="block border-b border-white/10 px-4 py-3 font-medium text-white" onClick={onClose}>যোগাযোগ</Link>

            <div className="flex gap-3 p-4">
                {user ? (
                    <Link href="/logout" method="post" as="button" className="btn-accent flex-1 !py-2 text-sm">লগআউট</Link>
                ) : (
                    <>
                        <Link href="/login" className="flex-1 rounded-xl border border-brand-cream py-2 text-center text-sm font-semibold text-brand-cream">লগইন</Link>
                        <Link href="/register" className="btn-accent flex-1 !py-2 text-sm">রেজিস্ট্রেশন</Link>
                    </>
                )}
            </div>
        </nav>
    );
}
