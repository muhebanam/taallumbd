import { Head, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import Pagination from '../../Components/Pagination';

const STATUSES = [['', 'সব'], ['pending', 'অপেক্ষমাণ'], ['published', 'প্রকাশিত'], ['draft', 'খসড়া'], ['rejected', 'প্রত্যাখ্যাত']];

export default function AdminCourses({ courses, filters }) {
    return (
        <DashboardLayout title="কোর্স অনুমোদন ও ব্যবস্থাপনা">
            <Head title="কোর্স ব্যবস্থাপনা" />
            <div className="mb-4 flex flex-wrap gap-2">
                {STATUSES.map(([s, label]) => (
                    <button key={s} onClick={() => router.get('/admin/courses', s ? { status: s } : {})} className={`rounded-full px-4 py-1.5 text-sm font-medium ${(filters.status ?? '') === s ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>{label}</button>
                ))}
            </div>
            <div className="card divide-y divide-brand/5">
                {courses.data.map((c) => (
                    <div key={c.id} className="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div>
                            <p className="font-semibold text-brand-deep">{c.title}</p>
                            <p className="text-sm text-brand-text/60">উস্তায: {c.instructor?.name} • {c.enrollments_count} শিক্ষার্থী • ৳{Number(c.price).toFixed(0)}</p>
                        </div>
                        <select
                            value={c.status}
                            onChange={(e) => router.put(`/admin/courses/${c.slug}/status`, { status: e.target.value })}
                            className="rounded-lg border-brand/20 text-sm focus:border-brand focus:ring-brand"
                        >
                            <option value="draft">খসড়া</option>
                            <option value="pending">অপেক্ষমাণ</option>
                            <option value="published">✅ প্রকাশ করুন</option>
                            <option value="rejected">❌ প্রত্যাখ্যান</option>
                        </select>
                    </div>
                ))}
            </div>
            <Pagination links={courses.links} />
        </DashboardLayout>
    );
}
