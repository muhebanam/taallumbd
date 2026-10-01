import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

export default function PublicationsIndex({ publications, categories, activeCategory }) {
    return (
        <AppLayout>
            <Head title={activeCategory ? activeCategory.name : 'প্রকাশনা'} />
            <div className="mx-auto max-w-7xl px-4 py-10">
                <h1 className="text-3xl font-bold text-brand-deep">{activeCategory ? activeCategory.name : 'প্রকাশনাসমূহ'}</h1>
                <div className="mt-6 flex flex-wrap gap-2">
                    <Link href="/publications" className={`rounded-full px-4 py-1.5 text-sm font-medium ${!activeCategory ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>সব</Link>
                    {categories.map((c) => (
                        <span key={c.id} className="flex flex-wrap gap-2">
                            <Link href={`/publications/${c.slug}`} className={`rounded-full px-4 py-1.5 text-sm font-medium ${activeCategory?.id === c.id ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>{c.name}</Link>
                            {c.children?.map((ch) => (
                                <Link key={ch.id} href={`/publications/${c.slug}/${ch.slug}`} className={`rounded-full px-3 py-1.5 text-xs font-medium ${activeCategory?.id === ch.id ? 'bg-brand text-brand-cream' : 'bg-brand-light text-brand hover:bg-brand hover:text-brand-cream'}`}>{ch.name}</Link>
                            ))}
                        </span>
                    ))}
                </div>
                {publications.data.length ? (
                    <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {publications.data.map((p) => (
                            <div key={p.id} className="card p-6">
                                <span className="rounded-full bg-brand-light px-3 py-0.5 text-xs font-medium text-brand">{p.category?.name}</span>
                                <h2 className="mt-3 font-bold text-brand-deep">{p.title}</h2>
                                <p className="mt-2 line-clamp-3 text-sm text-brand-text/75">{p.description}</p>
                                {(p.external_url || p.file_url) && (
                                    <a href={p.external_url || `/storage/${p.file_url}`} target="_blank" rel="noreferrer" className="mt-3 inline-block text-sm font-semibold text-brand hover:underline">দেখুন / ডাউনলোড →</a>
                                )}
                            </div>
                        ))}
                    </div>
                ) : <p className="mt-12 text-center text-brand-text/60">এই বিভাগে এখনো কোনো প্রকাশনা যুক্ত হয়নি।</p>}
                <Pagination links={publications.links} />
            </div>
        </AppLayout>
    );
}
