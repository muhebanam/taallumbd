import { Head, Link } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';
import StatCard from '../../Components/StatCard';

export default function AdminDashboard({ stats, recentOrders }) {
    const icons = {
        courses: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.243.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        ),
        enrollments: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        ),
        students: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        ),
        instructors: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
        earnings: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        ),
        messages: (
            <svg className="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        ),
    };

    const pendingActions = [
        { label: 'শিক্ষক আবেদন', value: stats.pending_instructor_applications, href: '/admin/instructor-applications', color: 'border-l-4 border-amber-500 text-amber-700 bg-amber-50' },
        { label: 'কোর্স অনুমোদন', value: stats.pending_course_approvals, href: '/admin/courses?status=pending', color: 'border-l-4 border-orange-500 text-orange-700 bg-orange-50' },
        { label: 'রিভিউ অনুমোদন', value: stats.pending_reviews, href: '#', color: 'border-l-4 border-blue-500 text-blue-700 bg-blue-50', disabled: true },
        { label: 'প্রবন্ধ অনুমোদন', value: stats.pending_articles, href: '/admin/articles?status=pending', color: 'border-l-4 border-emerald-500 text-emerald-700 bg-emerald-50' },
        { label: 'উত্তরের অপেক্ষায় ফাতাওয়া', value: stats.pending_fatawa, href: '/admin/fatawa?status=pending', color: 'border-l-4 border-purple-500 text-purple-700 bg-purple-50' },
    ];

    return (
        <DashboardLayout title="অ্যাডমিন ড্যাশবোর্ড">
            <Head title="অ্যাডমিন ড্যাশবোর্ড" />

            {/* Pending actions list */}
            {pendingActions.some((p) => p.value > 0) && (
                <div className="mb-8">
                    <h2 className="mb-4 text-lg font-bold text-brand-deep flex items-center gap-2">
                        <span className="flex h-2 w-2 rounded-full bg-amber-500 animate-ping" />
                        অনুমোদন ও অ্যাকশন প্রয়োজন
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        {pendingActions.map((item) => {
                            if (item.value === 0) return null;
                            const CardContent = (
                                <div className={`card p-4 transition duration-300 hover:-translate-y-0.5 ${item.color}`}>
                                    <p className="text-2xl font-extrabold">{item.value}</p>
                                    <p className="text-xs font-semibold mt-1 opacity-80">{item.label}</p>
                                </div>
                            );
                            if (item.disabled) {
                                return <div key={item.label} className="cursor-not-allowed select-none">{CardContent}</div>;
                            }
                            return (
                                <Link key={item.label} href={item.href}>
                                    {CardContent}
                                </Link>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Stats metrics grid */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <StatCard value={stats.total_courses} label="সর্বমোট কোর্স" icon={icons.courses} />
                <StatCard value={stats.total_enrollments} label="সর্বমোট ভর্তি" icon={icons.enrollments} />
                <StatCard value={stats.total_students} label="সর্বমোট শিক্ষার্থী" icon={icons.students} />
                <StatCard value={stats.total_instructors} label="সর্বমোট শিক্ষক" icon={icons.instructors} />
                <StatCard value={`৳ ${Number(stats.total_earnings).toLocaleString('bn-BD', { maximumFractionDigits: 0 })}`} label="মোট আয়" icon={icons.earnings} />
                <StatCard value={stats.unread_messages} label="অপঠিত বার্তা" icon={icons.messages} />
            </div>

            {/* Visual Charts section */}
            <div className="mt-8 grid gap-6 md:grid-cols-2">
                <div className="card p-6 bg-white border border-brand/5 shadow-card">
                    <h3 className="font-bold text-brand-deep mb-4">কোর্স বিক্রি ওভারভিউ</h3>
                    <div className="h-64 flex flex-col justify-between pt-4">
                        <div className="flex-1 flex items-end gap-3 px-2 border-b border-brand/10 pb-2">
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[40%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">৳৪০কে</span>
                            </div>
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[55%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">৳৫৫কে</span>
                            </div>
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[75%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">৳৭৫কে</span>
                            </div>
                            <div className="flex-1 bg-brand hover:bg-brand-deep transition rounded-t-lg h-[95%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">৳৯৫কে</span>
                            </div>
                        </div>
                        <div className="flex justify-between text-xs text-brand-text/50 mt-2 px-2">
                            <span>এপ্রিল</span>
                            <span>মে</span>
                            <span>জুন</span>
                            <span>জুলাই</span>
                        </div>
                    </div>
                </div>

                <div className="card p-6 bg-white border border-brand/5 shadow-card">
                    <h3 className="font-bold text-brand-deep mb-4">শিক্ষার্থী বৃদ্ধির গ্রাফ</h3>
                    <div className="h-64 flex flex-col justify-between pt-4">
                        <div className="flex-1 flex items-end gap-3 px-2 border-b border-brand/10 pb-2">
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[25%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">১২০ জন</span>
                            </div>
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[45%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">১৮০ জন</span>
                            </div>
                            <div className="flex-1 bg-brand-light hover:bg-brand/20 transition rounded-t-lg h-[65%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">২৪০ জন</span>
                            </div>
                            <div className="flex-1 bg-brand hover:bg-brand-deep transition rounded-t-lg h-[85%] relative group">
                                <span className="absolute -top-7 left-1/2 -translate-x-1/2 bg-brand text-brand-cream text-[10px] font-bold px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">৩২০ জন</span>
                            </div>
                        </div>
                        <div className="flex justify-between text-xs text-brand-text/50 mt-2 px-2">
                            <span>এপ্রিল</span>
                            <span>মে</span>
                            <span>জুন</span>
                            <span>জুলাই</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Recent Orders table */}
            <h2 className="mb-4 mt-8 text-lg font-bold text-brand-deep">সাম্প্রতিক পেমেন্টসমূহ</h2>
            <div className="card overflow-hidden bg-white border border-brand/5 shadow-card">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-brand text-brand-cream border-b border-brand-deep">
                            <tr>
                                <th className="px-6 py-4 font-bold">আইডি</th>
                                <th className="px-6 py-4 font-bold">ইউজার</th>
                                <th className="px-6 py-4 font-bold">কোর্স</th>
                                <th className="px-6 py-4 font-bold">পরিমাণ</th>
                                <th className="px-6 py-4 font-bold">অবস্থা</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-brand/5">
                            {recentOrders.length ? (
                                recentOrders.map((o) => (
                                    <tr key={o.id} className="hover:bg-brand-light transition">
                                        <td className="px-6 py-4 font-mono font-bold text-brand">#{o.id}</td>
                                        <td className="px-6 py-4 font-semibold text-brand-deep">{o.user?.name}</td>
                                        <td className="px-6 py-4 text-brand-text/80">{o.course?.title}</td>
                                        <td className="px-6 py-4 font-bold text-brand">৳ {Number(o.amount).toFixed(0)}</td>
                                        <td className="px-6 py-4">
                                            <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${o.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800'}`}>
                                                {o.status === 'paid' ? 'পরিশোধিত' : o.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="5" className="px-6 py-8 text-center text-brand-text/50">এখনো কোনো পেমেন্টের তথ্য পাওয়া যায়নি।</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </DashboardLayout>
    );
}
