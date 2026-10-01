import { useState, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { coursesByCategory } from '../data/menu';

/** Multi-level cascading dropdown menu: hover on category to show sub-courses on the right. */
export default function CourseMegaMenu() {
    const { navCategories } = usePage().props;
    const [open, setOpen] = useState(false);
    const closeTimer = useRef(null);

    const show = () => { clearTimeout(closeTimer.current); setOpen(true); };
    const hide = () => { closeTimer.current = setTimeout(() => setOpen(false), 150); };

    let categories = Array.isArray(navCategories?.course) ? navCategories.course : [];
    if (categories.length === 0) {
        const categoryNames = {
            'aqeedah': 'আক্বাঈদ',
            'quran': 'কুরআন',
            'tafsir': 'তাফসীর',
            'seerah': 'সীরাত',
            'hadith': 'হাদীস',
            'fiqh': 'ফিকহ',
            'arabic-language': 'আরবী ভাষা',
            'miscellaneous': 'বিবিধ'
        };
        categories = Object.keys(coursesByCategory).map((slug, index) => ({
            id: index + 1,
            slug,
            name: categoryNames[slug] || slug
        }));
    }

    return (
        <div className="relative" onMouseEnter={show} onMouseLeave={hide}>
            {/* Main Menu Button */}
            <button
                type="button"
                className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-white transition hover:text-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-cream"
                aria-haspopup="true" aria-expanded={open}
                onClick={() => setOpen((v) => !v)}
                onKeyDown={(e) => e.key === 'Escape' && setOpen(false)}
            >
                কোর্সসমূহ
                <svg className={`h-3.5 w-3.5 transition ${open ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {/* First Dropdown: বিভাগসমূহ */}
            {open && (
                <ul className="absolute left-0 top-full z-50 mt-1 w-56 rounded-xl border border-brand/10 bg-white py-1.5 shadow-cardHover">
                    {categories.map((cat) => {
                        const catCourses = coursesByCategory[cat.slug] || [];
                        return (
                            <li key={cat.id} className="group/cat relative">
                                <Link
                                    href={`/courses/category/${cat.slug}`}
                                    className="flex items-center justify-between px-4 py-2 text-sm font-medium text-brand-text transition hover:bg-brand hover:text-brand-cream"
                                >
                                    <span>{cat.name}</span>
                                    {catCourses.length > 0 && (
                                        <svg className="h-3 w-3 text-brand-text/40 transition group-hover/cat:text-brand-cream" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7-7" />
                                        </svg>
                                    )}
                                </Link>

                                {/* Second Dropdown: উপবিভাগসমূহ (ডান পাশে) */}
                                {catCourses.length > 0 && (
                                    <ul className="absolute left-full top-0 z-50 ml-0.5 hidden w-64 rounded-xl border border-brand/10 bg-white py-1.5 shadow-cardHover group-hover/cat:block">
                                        {catCourses.map((label) => (
                                            <li key={label}>
                                                <Link
                                                    href="/courses"
                                                    className="block px-4 py-1.5 text-xs text-brand-text transition hover:bg-brand-light hover:text-brand"
                                                >
                                                    {label}
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
}
