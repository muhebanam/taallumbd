import { useState, useRef } from 'react';
import { Link } from '@inertiajs/react';

/**
 * Hover-open (desktop) dropdown with optional one-level nested submenu.
 * items: [{ label, href, children?: [{label, href}] }]
 */
export default function DropdownMenu({ label, items, extraItem = null }) {
    const [open, setOpen] = useState(false);
    const closeTimer = useRef(null);

    const show = () => { clearTimeout(closeTimer.current); setOpen(true); };
    const hide = () => { closeTimer.current = setTimeout(() => setOpen(false), 150); };

    return (
        <div className="relative" onMouseEnter={show} onMouseLeave={hide}>
            <button
                type="button"
                className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-white transition hover:text-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-cream"
                aria-haspopup="true" aria-expanded={open}
                onClick={() => setOpen((v) => !v)}
                onKeyDown={(e) => e.key === 'Escape' && setOpen(false)}
            >
                {label}
                <svg className={`h-3.5 w-3.5 transition ${open ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
            </button>

            {open && (
                <div className="absolute left-0 top-full z-50 w-64 rounded-xl border border-brand/10 bg-white py-2 shadow-cardHover">
                    {items.map((item) => (
                        <div key={item.label} className="group/sub relative">
                            <Link
                                href={item.href}
                                className="flex items-center justify-between px-4 py-2 text-sm text-brand-text transition hover:bg-brand-light hover:text-brand"
                            >
                                {item.label}
                                {item.children && (
                                    <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                                )}
                            </Link>
                            {item.children && (
                                <div className="invisible absolute left-full top-0 w-60 rounded-xl border border-brand/10 bg-white py-2 opacity-0 shadow-cardHover transition group-hover/sub:visible group-hover/sub:opacity-100">
                                    {item.children.map((child) => (
                                        <Link key={child.label} href={child.href} className="block px-4 py-2 text-sm text-brand-text transition hover:bg-brand-light hover:text-brand">
                                            {child.label}
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}
                    {extraItem}
                </div>
            )}
        </div>
    );
}
