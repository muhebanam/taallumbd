import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

export default function ArticlesIndex({ articles, categories, activeCategory }) {
    return (
        <AppLayout>
            <Head title={activeCategory ? activeCategory.name : 'প্রবন্ধ'} />
            <div className="mx-auto max-w-7xl px-4 py-10">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-3xl font-bold text-brand-deep">{activeCategory ? `${activeCategory.name} — প্রবন্ধ` : 'প্রবন্ধসমূহ'}</h1>
                    <Link href="/dashboard/articles/create" className="btn-primary !py-2 text-sm">প্রবন্ধ লিখুন</Link>
                </div>
                <div className="mt-6 flex flex-wrap gap-2">
                    <Link href="/articles" className={`rounded-full px-4 py-1.5 text-sm font-medium ${!activeCategory ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>সব</Link>
                    {categories.map((c) => (
                        <Link key={c.id} href={`/articles/category/${c.slug}`} className={`rounded-full px-4 py-1.5 text-sm font-medium ${activeCategory?.id === c.id ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>{c.name}</Link>
                    ))}
                </div>
                {articles.data.length ? (
                    <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {articles.data.map((a) => (
                            <Link key={a.id} href={`/articles/${a.slug}`} className="card block p-6">
                                <span className="rounded-full bg-brand-light px-3 py-0.5 text-xs font-medium text-brand">{a.category?.name}</span>
                                <h2 className="mt-3 text-lg font-bold text-brand-deep">{a.title}</h2>
                                <p className="mt-2 line-clamp-3 text-sm text-brand-text/75">{a.excerpt}</p>
                                <p className="mt-3 text-xs text-brand-text/50">✍️ {a.author?.name}</p>
                            </Link>
                        ))}
                    </div>
                ) : <p className="mt-12 text-center text-brand-text/60">এই বিভাগে এখনো কোনো প্রবন্ধ প্রকাশিত হয়নি।</p>}
                <Pagination links={articles.links} />
            </div>
        </AppLayout>
    );
}
