import { Head, Link } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function TeacherAnalytics({ overview, courses = [], drop_off_lessons = [], recent_reviews = [] }) {
    return (
        <DashboardLayout title="শিক্ষক অ্যানালিটিক্স">
            <Head title="শিক্ষক অ্যানালিটিক্স ও ড্রপ-অফ রিপোর্ট - Taallum BD" />

            {/* Header */}
            <div className="mb-8">
                <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                    কোর্স ও শিক্ষার্থী এনগেজমেন্ট অ্যানালিটিক্স
                </h1>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    আপনার কোর্সের এনরোলমেন্ট, সমাপন হার, ড্রপ-অফ লেসন এবং রেভিনিউ শেয়ার পর্যালোচনা করুন।
                </p>
            </div>

            {/* KPI Overview Grid */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">মোট শিক্ষার্থী</p>
                    <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                        {overview.total_students}
                    </h3>
                    <p className="text-xs text-slate-500 mt-2">মোট এনরোলমেন্ট: {overview.total_enrollments}টি</p>
                </div>

                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-wider text-emerald-600">গড় সমাপন হার (Completion)</p>
                    <h3 className="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                        {overview.completion_rate}%
                    </h3>
                    <p className="text-xs text-slate-500 mt-2">কোর্স সম্পন্নকারী শিক্ষার্থী অনুপাত</p>
                </div>

                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-wider text-blue-600">সম্ভাব্য শিক্ষক আয় (৭০%)</p>
                    <h3 className="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                        ৳{overview.estimated_earnings?.toLocaleString()}
                    </h3>
                    <p className="text-xs text-slate-500 mt-2">মোট বিক্রয়: ৳{overview.gross_revenue?.toLocaleString()}</p>
                </div>

                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-wider text-amber-600">শিক্ষক গড় রেটিং</p>
                    <h3 className="text-3xl font-extrabold text-amber-500 mt-1 flex items-center gap-1.5">
                        ★ {overview.avg_rating}
                    </h3>
                    <p className="text-xs text-slate-500 mt-2">শিক্ষার্থীদের মতামতের গড়</p>
                </div>
            </div>

            {/* Drop-off Lessons Section (Critical for Teachers) */}
            <div className="mb-8 p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>ড্রপ-অফ লেসন অ্যানালাইসিস</span>
                            <span className="px-2 py-0.5 text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 rounded-md">
                                অপটিমাইজেশন প্রয়োজন
                            </span>
                        </h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            যেসব লেসনে শিক্ষার্থীরা শুরু করার পর সবচেয়ে বেশি ড্রপ করে (সম্পূর্ণ না করে চলে যায়)।
                        </p>
                    </div>
                </div>

                {drop_off_lessons.length === 0 ? (
                    <div className="p-6 text-center text-slate-400 text-sm">
                        এখনও ড্রপ-অফ সংক্রান্ত কোনো লেসন চিহ্নিত হয়নি।
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 font-semibold">
                                <tr>
                                    <th className="py-3 px-4 rounded-l-lg">লেসনের নাম</th>
                                    <th className="py-3 px-4">কোর্স</th>
                                    <th className="py-3 px-4 text-center">শুরু (Starts)</th>
                                    <th className="py-3 px-4 text-center">সম্পন্ন (Completes)</th>
                                    <th className="py-3 px-4 text-right rounded-r-lg">ড্রপ-অফ হার</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                                {drop_off_lessons.map((lesson) => {
                                    const isHighDrop = lesson.drop_off_rate > 50;
                                    return (
                                        <tr key={lesson.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                            <td className="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                                {lesson.title}
                                            </td>
                                            <td className="py-3 px-4 text-xs text-slate-500">
                                                {lesson.course_title}
                                            </td>
                                            <td className="py-3 px-4 text-center text-slate-600 dark:text-slate-300">
                                                {lesson.starts}
                                            </td>
                                            <td className="py-3 px-4 text-center text-slate-600 dark:text-slate-300">
                                                {lesson.completes}
                                            </td>
                                            <td className="py-3 px-4 text-right">
                                                <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${
                                                    isHighDrop
                                                        ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300'
                                                        : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'
                                                }`}>
                                                    {lesson.drop_off_rate}%
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Courses Breakdown Table */}
            <div className="mb-8 p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <h2 className="text-lg font-bold text-slate-900 dark:text-white mb-4">
                    কোর্স ভিত্তিক পারফরম্যান্স
                </h2>
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 font-semibold">
                            <tr>
                                <th className="py-3 px-4 rounded-l-lg">কোর্সের নাম</th>
                                <th className="py-3 px-4 text-center">এনরোলমেন্ট</th>
                                <th className="py-3 px-4 text-center">সমাপন হার</th>
                                <th className="py-3 px-4 text-right">মোট বিক্রয়</th>
                                <th className="py-3 px-4 text-center rounded-r-lg">রেটিং</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                            {courses.map((course) => (
                                <tr key={course.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td className="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-3">
                                        {course.thumbnail && (
                                            <img src={course.thumbnail} alt="" className="w-10 h-7 object-cover rounded shadow-sm" />
                                        )}
                                        <span>{course.title}</span>
                                    </td>
                                    <td className="py-3 px-4 text-center text-slate-600 dark:text-slate-300">
                                        {course.enrollments} জন
                                    </td>
                                    <td className="py-3 px-4 text-center">
                                        <div className="inline-flex items-center gap-2">
                                            <div className="w-16 h-2 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                                                <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${course.completion_rate}%` }} />
                                            </div>
                                            <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">{course.completion_rate}%</span>
                                        </div>
                                    </td>
                                    <td className="py-3 px-4 text-right font-medium text-slate-900 dark:text-white">
                                        ৳{course.revenue.toLocaleString()}
                                    </td>
                                    <td className="py-3 px-4 text-center text-amber-500 font-bold">
                                        ★ {course.rating} <span className="text-xs font-normal text-slate-400">({course.reviews_count})</span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Recent Student Feedback */}
            {recent_reviews.length > 0 && (
                <div className="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h2 className="text-lg font-bold text-slate-900 dark:text-white mb-4">
                        সাম্প্রতিক শিক্ষার্থীদের মতামত ও রিভিউ
                    </h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        {recent_reviews.map((rev) => (
                            <div key={rev.id} className="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
                                <div className="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 className="font-semibold text-slate-900 dark:text-white text-sm">{rev.user?.name || 'শিক্ষার্থী'}</h4>
                                        <p className="text-xs text-slate-500">{rev.course?.title}</p>
                                    </div>
                                    <span className="text-amber-500 text-sm font-bold">★ {rev.rating}</span>
                                </div>
                                <p className="text-xs text-slate-600 dark:text-slate-300 italic">
                                    "{rev.comment || 'কোনো মন্তব্য নেই'}"
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </DashboardLayout>
    );
}
