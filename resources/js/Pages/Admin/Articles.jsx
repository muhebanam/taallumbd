import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

const STATUSES = [['', 'সব'], ['pending', 'অপেক্ষমাণ'], ['published', 'প্রকাশিত'], ['rejected', 'প্রত্যাখ্যাত']];

export default function AdminArticles({ articles, filters }) {
    return (
        <DashboardLayout title="প্রবন্ধ অনুমোদন">
            <Head title="প্রবন্ধ ব্যবস্থাপনা" />
            <div className="mb-4 flex flex-wrap gap-2">
                {STATUSES.map(([s, label]) => (
                    <button key={s} onClick={() => router.get('/admin/articles', s ? { status: s } : {})} className={`rounded-full px-4 py-1.5 text-sm font-medium ${(filters.status ?? '') === s ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>{label}</button>
                ))}
            </div>
            <div className="card divide-y divide-brand/5">
                {articles.data.map((a) => (
                    <div key={a.id} className="p-4">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p className="font-semibold text-brand-deep">{a.title}</p>
                                <p className="text-sm text-brand-text/60">✍️ {a.author?.name} • {a.category?.name}</p>
                            </div>
                            <select
                                value={a.status}
                                onChange={(e) => router.put(`/admin/articles/${a.slug}/status`, { status: e.target.value })}
                                className="rounded-lg border-brand/20 text-sm focus:border-brand focus:ring-brand"
                            >
                                <option value="pending">অপেক্ষমাণ</option>
                                <option value="published">✅ প্রকাশ করুন</option>
                                <option value="rejected">❌ প্রত্যাখ্যান</option>
                                <option value="draft">খসড়া</option>
                            </select>
                        </div>
                        <p className="mt-2 line-clamp-2 text-sm text-brand-text/70">{a.excerpt}</p>
                    </div>
                ))}
            </div>
            <Pagination links={articles.links} />
        </DashboardLayout>
    );
}
