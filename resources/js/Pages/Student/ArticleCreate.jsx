import { Head, useForm } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function ArticleCreate({ categories }) {
    const { data, setData, post, processing, errors } = useForm({ title: '', category_id: '', excerpt: '', body: '' });
    const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand';
    const submit = (e) => { e.preventDefault(); post('/dashboard/articles'); };

    return (
        <DashboardLayout title="নতুন প্রবন্ধ লিখুন">
            <Head title="প্রবন্ধ লিখুন" />
            <p className="mb-4 rounded-xl bg-brand-cream/40 p-3 text-sm text-brand-deep">প্রবন্ধ জমা দিলে তা অ্যাডমিনের অনুমোদনের পর প্রকাশিত হবে।</p>
            <form onSubmit={submit} className="card space-y-4 p-6">
                <div>
                    <label className="text-sm font-medium">শিরোনাম *</label>
                    <input className={field} value={data.title} onChange={(e) => setData('title', e.target.value)} />
                    {errors.title && <p className="mt-1 text-xs text-red-600">{errors.title}</p>}
                </div>
                <div>
                    <label className="text-sm font-medium">বিভাগ *</label>
                    <select className={field} value={data.category_id} onChange={(e) => setData('category_id', e.target.value)}>
                        <option value="">নির্বাচন করুন</option>
                        {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    {errors.category_id && <p className="mt-1 text-xs text-red-600">{errors.category_id}</p>}
                </div>
                <div>
                    <label className="text-sm font-medium">সারসংক্ষেপ *</label>
                    <textarea rows={2} className={field} value={data.excerpt} onChange={(e) => setData('excerpt', e.target.value)} />
                    {errors.excerpt && <p className="mt-1 text-xs text-red-600">{errors.excerpt}</p>}
                </div>
                <div>
                    <label className="text-sm font-medium">মূল লেখা *</label>
                    <textarea rows={12} className={field} value={data.body} onChange={(e) => setData('body', e.target.value)} />
                    {errors.body && <p className="mt-1 text-xs text-red-600">{errors.body}</p>}
                </div>
                <button type="submit" disabled={processing} className="btn-primary">অনুমোদনের জন্য জমা দিন</button>
            </form>
        </DashboardLayout>
    );
}
