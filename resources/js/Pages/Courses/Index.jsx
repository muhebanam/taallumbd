import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import CourseCard from '../../Components/CourseCard';
import Pagination from '../../Components/Pagination';

export default function CoursesIndex({ courses, categories, activeCategory }) {
    return (
        <AppLayout>
            <Head title={activeCategory ? activeCategory.name : 'কোর্সসমূহ'} />
            <div className="mx-auto max-w-7xl px-4 py-10">
                <h1 className="text-3xl font-bold text-brand-deep">{activeCategory ? `${activeCategory.name} — কোর্সসমূহ` : 'সকল কোর্স'}</h1>

                <div className="mt-6 flex flex-wrap gap-2">
                    <Link href="/courses" className={`rounded-full px-4 py-1.5 text-sm font-medium ${!activeCategory ? 'bg-brand text-brand-cream' : 'bg-white text-brand-text hover:bg-brand-light'}`}>সব</Link>
                    {categories.map((c) => (
                        <Link key={c.id} href={`/courses/category/${c.slug}`} className={`rounded-full px-4 py-1.5 text-sm font-medium ${activeCategory?.id === c.id ? 'bg-brand text-brand-cream' : 'bg-white text-brand-text hover:bg-brand-light'}`}>
                            {c.name}
                        </Link>
                    ))}
                </div>

                {courses.data.length ? (
                    <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {courses.data.map((c) => <CourseCard key={c.id} course={c} />)}
                    </div>
                ) : (
                    <p className="mt-12 text-center text-brand-text/60">এই ক্যাটাগরিতে এখনো কোনো কোর্স প্রকাশিত হয়নি। শীঘ্রই আসছে, ইনশাআল্লাহ।</p>
                )}
                <Pagination links={courses.links} />
            </div>
        </AppLayout>
    );
}
