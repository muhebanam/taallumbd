import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function CourseView({ course, completedLessonIds, enrollment }) {
    const done = new Set(completedLessonIds);
    
    // Count total lessons from curriculum items
    const totalLessons = course.sections.reduce((n, s) => {
        const lessonsCount = s.curriculum_items?.filter(item => item.item_type === 'lesson').length ?? 0;
        return n + lessonsCount;
    }, 0);

    return (
        <DashboardLayout title={course.title}>
            <Head title={course.title} />

            <div className="card mb-6 flex flex-wrap items-center justify-between gap-4 p-5">
                <div>
                    <p className="text-sm text-brand-text/60">অগ্রগতি: {enrollment?.progress ?? 0}% • {done.size}/{totalLessons} পাঠ সম্পন্ন</p>
                    <div className="mt-2 h-2 w-64 overflow-hidden rounded-full bg-brand-light">
                        <div className="h-full rounded-full bg-brand" style={{ width: `${enrollment?.progress ?? 0}%` }} />
                    </div>
                </div>
                {(enrollment?.progress ?? 0) === 100 && (
                    <button onClick={() => router.post(`/dashboard/certificates/${course.slug}/generate`)} className="btn-accent !py-2 text-sm">
                        🎓 সার্টিফিকেট নিন
                    </button>
                )}
            </div>

            {course.sections.map((section, si) => (
                <div key={section.id} className="card mb-4 overflow-hidden">
                    <div className="bg-brand px-5 py-3 font-semibold text-brand-cream">অধ্যায় {si + 1}: {section.title}</div>
                    <ul className="divide-y divide-brand/5">
                        {section.curriculum_items?.map((item) => {
                            const isLesson = item.item_type === 'lesson';
                            const isQuiz = item.item_type === 'quiz';
                            const isAssignment = item.item_type === 'assignment';
                            const isResource = item.item_type === 'resource';
                            const isLive = item.item_type === 'live_class';

                            let href = '#';
                            let icon = '🎬';
                            let rightLabel = '';
                            let isCompleted = false;

                            if (isLesson) {
                                href = `/dashboard/lessons/${item.itemable_id}`;
                                icon = '🎬';
                                isCompleted = done.has(item.itemable_id);
                            } else if (isQuiz) {
                                href = `/dashboard/quizzes/${item.itemable_id}`;
                                icon = '📝';
                                rightLabel = item.itemable ? `পাস মার্ক: ${item.itemable.pass_marks}/${item.itemable.total_marks}` : '';
                            } else if (isAssignment) {
                                href = `/dashboard/assignments/${item.itemable_id}`;
                                icon = '📋';
                                rightLabel = item.itemable?.deadline ? `শেষ তারিখ: ${new Date(item.itemable.deadline).toLocaleDateString('bn-BD')}` : '';
                            } else if (isResource) {
                                href = item.itemable?.url || (item.itemable?.file_path ? `/storage/${item.itemable.file_path}` : '#');
                                icon = '📄';
                                rightLabel = 'ডাউনলোড';
                            } else if (isLive) {
                                href = item.itemable?.meeting_url || '#';
                                icon = '🎥';
                                rightLabel = item.itemable?.start_time ? `লাইভ: ${new Date(item.itemable.start_time).toLocaleString('bn-BD')}` : 'লাইভ ক্লাস';
                            }

                            const linkContent = (
                                <span className="flex items-center gap-2">
                                    {isLesson && (
                                        <span className={isCompleted ? 'text-green-600 font-bold' : 'text-brand-text/30'}>
                                            {isCompleted ? '✔' : '○'}
                                        </span>
                                    )}
                                    <span>{icon} {item.title_snapshot}</span>
                                </span>
                            );

                            const rightContent = rightLabel && (
                                <span className="text-xs text-brand-text/50">{rightLabel}</span>
                            );

                            return (
                                <li key={item.id}>
                                    {isResource || isLive ? (
                                        <a href={href} target="_blank" rel="noopener noreferrer" className="flex items-center justify-between px-5 py-3 text-sm transition hover:bg-brand-light">
                                            {linkContent}
                                            {rightContent}
                                        </a>
                                    ) : (
                                        <Link href={href} className="flex items-center justify-between px-5 py-3 text-sm transition hover:bg-brand-light">
                                            {linkContent}
                                            {rightContent}
                                        </Link>
                                    )}
                                </li>
                            );
                        })}
                        {(!section.curriculum_items || section.curriculum_items.length === 0) && (
                            <li className="px-5 py-3 text-sm text-brand-text/40 italic text-center">কোনো কারিকুলাম আইটেম যোগ করা হয়নি</li>
                        )}
                    </ul>
                </div>
            ))}
        </DashboardLayout>
    );
}
