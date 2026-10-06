import { Head, Link } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import StatCard from '../../Components/StatCard';
import CourseCard from '../../Components/CourseCard';

export default function StudentDashboard({
    enrollments = [],
    certificates = [],
    quizAttempts = [],
    submissions = [],
    recommendedCourses = [],
    learningPathEnrollments = []
}) {
    const enrolledCount = enrollments.length;
    const completedCount = enrollments.filter((e) => Number(e.progress) === 100).length;
    const quizAttemptsCount = quizAttempts.length;
    const certificatesCount = certificates.length;

    const continueLearning = enrollments.find((e) => Number(e.progress) > 0 && Number(e.progress) < 100);

    const icons = {
        courses: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.243.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        ),
        completed: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
        quizzes: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
            </svg>
        ),
        certificates: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        ),
    };

    return (
        <DashboardLayout title="আমার ড্যাশবোর্ড">
            <Head title="ড্যাশবোর্ড" />

            {/* Quick Stats Grid */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <StatCard value={enrolledCount} label="ভর্তি হওয়া কোর্স" icon={icons.courses} />
                <StatCard value={completedCount} label="সম্পন্ন কোর্স" icon={icons.completed} />
                <StatCard value={quizAttemptsCount} label="কুইজ অংশগ্রহণ" icon={icons.quizzes} />
                <StatCard value={certificatesCount} label="অর্জনকৃত সার্টিফিকেট" icon={icons.certificates} />
            </div>

            {/* Continue Learning Highlight */}
            {continueLearning && (
                <div className="card p-6 bg-brand-deep text-white mb-8 relative overflow-hidden hero-pattern">
                    <div className="absolute inset-0 bg-brand-deep/80 z-0"></div>
                    <div className="relative z-10">
                        <span className="bg-brand-cream text-brand-deep text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">চলতি কোর্স</span>
                        <h2 className="text-2xl font-bold mt-3 text-white">{continueLearning.course?.title}</h2>
                        <p className="text-sm text-white/70 mt-1">{continueLearning.course?.instructor?.name}</p>
                        
                        <div className="mt-6 flex flex-wrap items-center gap-6">
                            <div className="flex-1 min-w-[200px]">
                                <div className="h-2 w-full rounded-full bg-white/20 overflow-hidden">
                                    <div className="h-full bg-brand-cream rounded-full" style={{ width: `${continueLearning.progress}%` }}></div>
                                </div>
                                <span className="text-xs text-white/80 mt-1 block">{continueLearning.progress}% সম্পন্ন</span>
                            </div>
                            <Link href={`/dashboard/courses/${continueLearning.course?.slug}`} className="btn-accent !px-6 !py-2.5 text-sm shrink-0">
                                পড়া চালিয়ে যান →
                            </Link>
                        </div>
                    </div>
                </div>
            )}

            <h2 className="mb-4 text-lg font-bold text-brand-deep">আমার কোর্সসমূহ</h2>
            {enrollments.length ? (
                <div className="grid gap-4 md:grid-cols-2">
                    {enrollments.map((en) => (
                        <div key={en.id} className="card p-5 bg-white border border-brand/5 shadow-card hover:shadow-cardHover flex flex-col justify-between">
                            <div>
                                <h3 className="font-bold text-brand-deep text-lg">{en.course?.title}</h3>
                                <p className="mt-1 text-sm text-brand-text/60">{en.course?.instructor?.name} • {en.course?.lessons_count} টি পাঠ</p>
                            </div>
                            <div className="mt-4">
                                <div className="mt-3 h-2 overflow-hidden rounded-full bg-brand-light">
                                    <div className="h-full rounded-full bg-brand" style={{ width: `${en.progress}%` }} />
                                </div>
                                <div className="mt-3 flex items-center justify-between text-sm">
                                    <span className="text-brand-text/60 font-semibold">{en.progress}% সম্পন্ন</span>
                                    <Link href={`/dashboard/courses/${en.course?.slug}`} className="font-bold text-brand hover:underline flex items-center gap-1">
                                        {en.progress > 0 ? 'চালিয়ে যান →' : 'শুরু করুন →'}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="card p-8 text-center bg-white border border-brand/5 shadow-card">
                    <p className="text-brand-text/60">আপনি এখনো কোনো কোর্সে ভর্তি হননি।</p>
                    <Link href="/courses" className="btn-primary mt-4">কোর্স দেখুন</Link>
                </div>
            )}

            {/* Active Learning Paths */}
            {learningPathEnrollments && learningPathEnrollments.length > 0 && (
                <div className="mt-8 card p-6 bg-white border border-brand/10 shadow-card">
                    <div className="flex items-center justify-between border-b border-brand/5 pb-4 mb-4">
                        <div>
                            <h2 className="text-lg font-bold text-brand-deep">আমার সক্রিয় লার্নিং পাথ</h2>
                            <p className="text-xs text-brand-text/60 mt-0.5">ধারাবাহিক রোডম্যাপ ও বিশেষায়িত সার্টিফিকেট অগ্রগতি</p>
                        </div>
                        <Link href="/learning-paths" className="text-xs font-bold text-brand hover:underline">
                            সকল পাথ &rarr;
                        </Link>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        {learningPathEnrollments.map((lpe) => (
                            <div key={lpe.id} className="p-4 rounded-xl bg-brand-light/60 border border-brand/10 flex flex-col justify-between">
                                <div>
                                    <div className="flex items-center justify-between mb-2">
                                        <h3 className="font-bold text-brand-deep text-sm">
                                            {lpe.learning_path?.title}
                                        </h3>
                                        <span className="text-[11px] font-bold px-2 py-0.5 rounded-full bg-brand-deep/10 text-brand-deep">
                                            {lpe.status === 'completed' ? 'সম্পন্ন' : `${lpe.progress_percentage}%`}
                                        </span>
                                    </div>
                                    <div className="w-full h-2 rounded-full bg-slate-200 overflow-hidden mb-3">
                                        <div
                                            className="h-full bg-emerald-600 transition-all"
                                            style={{ width: `${lpe.progress_percentage}%` }}
                                        />
                                    </div>
                                </div>
                                <div className="flex items-center justify-between text-xs pt-2">
                                    <span className="text-brand-text/60">{lpe.completed_courses_count || 0} / {lpe.total_courses_count || 0} কোর্স সম্পন্ন</span>
                                    <Link
                                        href={`/learning-paths/${lpe.learning_path?.slug}`}
                                        className="font-bold text-brand hover:underline"
                                    >
                                        রোডম্যাপ দেখুন &rarr;
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="mt-8 grid gap-6 lg:grid-cols-3">
                <div className="card p-5 bg-white border border-brand/5 shadow-card">
                    <h2 className="font-bold text-brand-deep border-b border-brand/5 pb-3">সাম্প্রতিক কুইজ ফলাফল</h2>
                    {quizAttempts.length ? (
                        <ul className="mt-3 space-y-2 text-sm">
                            {quizAttempts.map((a) => (
                                <li key={a.id} className="flex justify-between items-center rounded-xl bg-brand-light px-4 py-3 border border-brand/5">
                                    <span className="font-semibold text-brand-deep">{a.quiz?.title}</span>
                                    <span className={`font-bold px-2.5 py-1 rounded-full text-xs ${a.status === 'passed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}`}>
                                        {a.score}/{a.quiz?.total_marks} — {a.status === 'passed' ? 'পাস' : 'ফেল'}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    ) : <p className="mt-3 text-sm text-brand-text/50 p-4 text-center">এখনো কোনো কুইজ দেননি।</p>}
                </div>

                <div className="card p-5 bg-white border border-brand/5 shadow-card">
                    <h2 className="font-bold text-brand-deep border-b border-brand/5 pb-3">অ্যাসাইনমেন্ট মূল্যায়ন</h2>
                    {submissions.length ? (
                        <ul className="mt-3 space-y-2 text-sm">
                            {submissions.map((s) => (
                                <li key={s.id} className="flex justify-between items-center rounded-xl bg-brand-light px-4 py-3 border border-brand/5">
                                    <div className="min-w-0 flex-1">
                                        <p className="font-semibold text-brand-deep truncate">{s.assignment?.title}</p>
                                        <span className="text-[10px] text-brand-text/50">প্রাপ্ত নম্বর: {s.marks !== null ? `${s.marks}/${s.assignment?.total_marks}` : 'মূল্যায়ন চলছে'}</span>
                                    </div>
                                    <span className={`font-bold px-2 py-0.5 rounded text-[10px] shrink-0 ${s.status === 'graded' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800'}`}>
                                        {s.status === 'graded' ? 'মূল্যায়িত' : 'জমা দেওয়া'}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    ) : <p className="mt-3 text-sm text-brand-text/50 p-4 text-center">এখনো কোনো অ্যাসাইনমেন্ট জমা দেননি।</p>}
                </div>

                <div className="card p-5 bg-white border border-brand/5 shadow-card">
                    <h2 className="font-bold text-brand-deep border-b border-brand/5 pb-3">অর্জনকৃত সার্টিফিকেট</h2>
                    {certificates.length ? (
                        <ul className="mt-3 space-y-2 text-sm">
                            {certificates.map((c) => (
                                <li key={c.id} className="flex justify-between items-center rounded-xl bg-brand-light px-4 py-3 border border-brand/5">
                                    <span className="font-semibold text-brand-deep">{c.course?.title || c.learning_path?.title || 'সার্টিফিকেট'}</span>
                                    <Link href={`/certificates/${c.id}`} className="font-bold text-brand hover:underline flex items-center gap-1">দেখুন →</Link>
                                </li>
                            ))}
                        </ul>
                    ) : <p className="mt-3 text-sm text-brand-text/50 p-4 text-center">কোর্স সম্পন্ন করলে সার্টিফিকেট এখানে দেখা যাবে।</p>}
                </div>
            </div>

            {/* Recommended Courses ("আপনার জন্য") */}
            {recommendedCourses && recommendedCourses.length > 0 && (
                <div className="mt-10">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h2 className="text-xl font-bold text-brand-deep">আপনার জন্য প্রস্তাবিত কোর্স</h2>
                            <p className="text-xs text-brand-text/60 mt-0.5">আপনার বিষয়ভিত্তিক আগ্রহ ও অন্যান্য শিক্ষার্থীদের পছন্দ অনুযায়ী</p>
                        </div>
                        <Link href="/courses" className="text-xs font-bold text-brand hover:underline">
                            সকল কোর্স &rarr;
                        </Link>
                    </div>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {recommendedCourses.map((course) => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                </div>
            )}
        </DashboardLayout>
    );
}
