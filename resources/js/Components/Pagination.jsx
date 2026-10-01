import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;
    return (
        <nav className="mt-8 flex flex-wrap justify-center gap-1" aria-label="পেজিনেশন">
            {links.map((link, i) => link.url ? (
                <Link
                    key={i}
                    href={link.url}
                    className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-brand text-brand-cream' : 'bg-white text-brand-text hover:bg-brand-light'}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ) : (
                <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-brand-text/40" dangerouslySetInnerHTML={{ __html: link.label }} />
            ))}
        </nav>
    );
}
