import { Head, Link, useForm } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import StatCard from '../../Components/StatCard';

const STATUS_BN = { draft: 'খসড়া', pending: 'অনুমোদনের অপেক্ষায়', published: 'প্রকাশিত', rejected: 'প্রত্যাখ্যাত' };
const STATUS_CLS = {
    draft: 'bg-gray-100 text-gray-700', pending: 'bg-amber-100 text-amber-800',
    published: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700',
};

function ReviewForm({ submission }) {
    const { data, setData, post, processing } = useForm({ marks: '', feedback: '' });
    const submit = (e) => { e.preventDefault(); post(`/instructor/submissions/${submission.id}/review`); };
    return (
        <form onSubmit={submit} className="mt-2 flex flex-wrap items-center gap-2">
            <input type="number" min="0" placeholder="নম্বর" className="w-24 rounded-lg border-brand/20 text-sm focus:border-brand focus:ring-brand" value={data.marks} onChange={(e) => setData('marks', e.target.value)} />
            <input placeholder="ফিডব্যাক" className="min-w-0 flex-1 rounded-lg border-brand/20 text-sm focus:border-brand focus:ring-brand" value={data.feedback} onChange={(e) => setData('feedback', e.target.value)} />
            <button type="submit" disabled={processing} className="btn-primary !px-4 !py-1.5 text-sm">মূল্যায়ন</button>
        </form>
    );
}

export default function InstructorDashboard({ courses, stats, pendingSubmissions }) {
    const icons = {
        courses: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.243.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        ),
        students: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        ),
        assignments: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        ),
        earnings: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        ),
        quizzes: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
        rating: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 9.72c-.773-.57-.375-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
            </svg>
        ),
    };

    return (
        <DashboardLayout title="ইন্সট্রাক্টর ড্যাশবোর্ড">
            <Head title="ইন্সট্রাক্টর ড্যাশবোর্ড" />

            <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <StatCard value={stats.my_courses_count} label="আমার কোর্স" icon={icons.courses} />
                <StatCard value={stats.my_students_count} label="মোট শিক্ষার্থী" icon={icons.students} />
                <StatCard value={`৳ ${Number(stats.course_earnings).toLocaleString('bn-BD', { maximumFractionDigits: 0 })}`} label="কোর্স আয়" icon={icons.earnings} />
                <StatCard value={stats.quiz_attempts_count} label="কুইজ অংশগ্রহণ" icon={icons.quizzes} />
                <StatCard value={stats.average_rating ? stats.average_rating.toFixed(1) : '০.০'} label="গড় রেটিং" icon={icons.rating} />
                <StatCard value={stats.pending_assignments_count} label="অপেক্ষমাণ অ্যাসাইনমেন্ট" icon={icons.assignments} />
            </div>

            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-lg font-bold text-brand-deep">আমার কোর্সসমূহ</h2>
                <Link href="/instructor/courses/create" className="btn-accent !py-2 text-sm">+ নতুন কোর্স</Link>
            </div>
            <div className="card divide-y divide-brand/5">
                {courses.length ? courses.map((c) => (
                    <div key={c.id} className="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div>
                            <p className="font-semibold text-brand-deep">{c.title}</p>
                            <p className="text-sm text-brand-text/60">{c.lessons_count} পাঠ • {c.enrollments_count} শিক্ষার্থী</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${STATUS_CLS[c.status]}`}>{STATUS_BN[c.status]}</span>
                            <Link href={`/instructor/courses/${c.slug}/students`} className="rounded-lg bg-brand-light px-3 py-1.5 text-sm font-medium hover:bg-brand hover:text-brand-cream">শিক্ষার্থী</Link>
                            <Link href={`/instructor/courses/${c.slug}/edit`} className="rounded-lg bg-brand px-3 py-1.5 text-sm font-medium text-brand-cream hover:bg-brand-deep">সম্পাদনা</Link>
                        </div>
                    </div>
                )) : <p className="p-8 text-center text-brand-text/60">এখনো কোনো কোর্স তৈরি করেননি।</p>}
            </div>

            {pendingSubmissions.length > 0 && (
                <>
                    <h2 className="mb-4 mt-8 text-lg font-bold text-brand-deep">মূল্যায়নের অপেক্ষায় থাকা অ্যাসাইনমেন্ট</h2>
                    <div className="card divide-y divide-brand/5">
                        {pendingSubmissions.map((s) => (
                            <div key={s.id} className="p-4">
                                <p className="text-sm"><span className="font-semibold">{s.user?.name}</span> — {s.assignment?.title}</p>
                                {s.answer_text && <p className="mt-1 line-clamp-2 text-sm text-brand-text/70">{s.answer_text}</p>}
                                {s.file_path && <a href={`/storage/${s.file_path}`} target="_blank" rel="noreferrer" className="text-sm font-semibold text-brand hover:underline">📎 সংযুক্ত ফাইল</a>}
                                <ReviewForm submission={s} />
                            </div>
                        ))}
                    </div>
                </>
            )}
        </DashboardLayout>
    );
}
