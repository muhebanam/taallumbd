import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import SeoHead from '../../Components/SeoHead';
import RecommendedSection from '../../Components/RecommendedSection';

export default function CourseShow({ course, isEnrolled, seo, learnersAlsoEnrolled = [], similarCourses = [] }) {
    const isFree = course.is_free || Number(course.price) === 0;
    return (
        <AppLayout>
            {seo ? (
                <SeoHead
                    title={seo.title}
                    description={seo.description}
                    canonical={seo.canonical}
                    ogImage={seo.ogImage}
                    type="website"
                    jsonLd={seo.jsonLd}
                />
            ) : (
                <Head title={course.title} />
            )}

            {/* Course hero */}
            <section className="hero-pattern text-white">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <h1 className="text-3xl font-bold text-brand-cream md:text-4xl">{course.title}</h1>
                        <p className="mt-3 text-white/85">{course.short_description}</p>
                        <p className="mt-4 text-sm text-white/70">উস্তায: <span className="font-semibold text-white">{course.instructor?.name}</span></p>
                        <div className="mt-4 flex flex-wrap gap-2 text-sm">
                            {course.is_certified && (
                                <span className="rounded-full bg-emerald-500/30 border border-emerald-400/50 px-3.5 py-1 text-[#FFF99A] font-bold flex items-center gap-1.5 shadow-sm">
                                    <span>★</span> স্কলার অনুমোদিত কোর্স
                                    {course.certified_by_scholar && (
                                        <span className="text-white/80 font-normal">({course.certified_by_scholar.name})</span>
                                    )}
                                </span>
                            )}
                            <span className="rounded-full bg-brand-cream/20 px-3 py-1 text-brand-cream">♾️ আজীবন প্রবেশাধিকার</span>
                            <span className="rounded-full bg-brand-cream/20 px-3 py-1 text-brand-cream">🎓 সার্টিফিকেট</span>
                            {course.level && <span className="rounded-full bg-white/10 px-3 py-1">📚 {course.level}</span>}
                            {course.duration && <span className="rounded-full bg-white/10 px-3 py-1">⏱ {course.duration}</span>}
                            <span className="rounded-full bg-white/10 px-3 py-1">🎬 {course.lessons_count} টি পাঠ</span>
                        </div>
                    </div>
                    <div className="card h-fit p-6 text-brand-text">
                        <div className="flex h-36 items-center justify-center rounded-xl bg-brand-deep hero-pattern">
                            <img src="/images/logo.png" alt="" className="h-16 w-16 opacity-80" />
                        </div>
                        {course.status === 'coming_soon' ? (
                            <>
                                <p className="mt-4 text-2xl font-bold text-amber-600">শীঘ্রই আসছে</p>
                                <button disabled className="btn-secondary mt-4 w-full cursor-not-allowed opacity-60">ভর্তি বন্ধ আছে</button>
                                <button type="button" className="btn-accent mt-2 w-full text-sm">জানতে চাই (Notify Me)</button>
                            </>
                        ) : (
                            <>
                                <p className="mt-4 text-2xl font-bold text-brand">{isFree ? 'ফ্রি কোর্স' : `৳ ${Number(course.price).toFixed(0)}`}</p>
                                {isEnrolled ? (
                                    <Link href={`/dashboard/courses/${course.slug}`} className="btn-primary mt-4 w-full">ক্লাসে প্রবেশ করুন</Link>
                                ) : (
                                    <Link href={`/checkout/${course.slug}`} className="btn-accent mt-4 w-full">
                                        {isFree ? 'ফ্রি ভর্তি হোন' : 'ভর্তি হোন'}
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </section>

            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 lg:grid-cols-3">
                <div className="space-y-10 lg:col-span-2">
                    {/* Scholar Certification Trust Card */}
                    {course.is_certified && (
                        <section className="rounded-2xl border border-amber-300 bg-amber-50/70 p-6 text-amber-950 shadow-sm">
                            <div className="flex items-start gap-4">
                                <span className="text-3xl">📜</span>
                                <div className="space-y-1.5 flex-1">
                                    <div className="flex items-center gap-2">
                                        <h3 className="font-bold font-bangla text-base text-amber-900">
                                            স্কলার ও শরিয়াহ প্রত্যয়নপত্র (Certified by Scholar)
                                        </h3>
                                        <span className="rounded-full bg-amber-200/80 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                                            সত্যায়িত
                                        </span>
                                    </div>
                                    <p className="text-xs text-amber-800 leading-relaxed font-bangla">
                                        এই কোর্সটির সিলেবাস ও বিষয়বস্তু সম্মানিত স্কলার{' '}
                                        <strong>{course.certified_by_scholar?.name || 'আত-তাআল্লুম শরিয়াহ বোর্ড'}</strong> ({course.certified_by_scholar?.designation || 'সিনিয়র ইসলামিক স্কলার'}) কর্তৃক পুঙ্খানুপুঙ্খভাবে পর্যালোচনা ও শরিয়াহ নীতিমালার আলোকে অনুমোদিত হয়েছে।
                                    </p>
                                    {course.certification_note && (
                                        <p className="mt-2 text-xs italic text-amber-900 bg-white/80 p-3 rounded-xl border border-amber-200">
                                            "{course.certification_note}"
                                        </p>
                                    )}
                                </div>
                            </div>
                        </section>
                    )}

                    {/* What you'll learn */}
                    {course.learn_points?.length > 0 && (
                        <section className="card p-6">
                            <h2 className="text-xl font-bold text-brand-deep">এই কোর্সে যা শিখবেন</h2>
                            <ul className="mt-4 grid gap-2 sm:grid-cols-2">
                                {course.learn_points.map((p) => (
                                    <li key={p} className="flex items-start gap-2 text-sm"><span className="text-brand">✔</span>{p}</li>
                                ))}
                            </ul>
                        </section>
                    )}

                    {/* Description */}
                    <section className="card p-6">
                        <h2 className="text-xl font-bold text-brand-deep">কোর্স পরিচিতি</h2>
                        <p className="mt-3 whitespace-pre-line leading-relaxed text-brand-text/85">{course.description}</p>
                    </section>

                    {/* Curriculum */}
                    <section className="card p-6">
                        <h2 className="text-xl font-bold text-brand-deep">কোর্স কারিকুলাম</h2>
                        <div className="mt-4 space-y-4">
                            {course.sections.map((section, si) => (
                                <div key={section.id} className="overflow-hidden rounded-xl border border-brand/10">
                                    <div className="bg-brand-light px-4 py-2.5 font-semibold text-brand-deep">অধ্যায় {si + 1}: {section.title}</div>
                                    <ul className="divide-y divide-brand/5">
                                        {section.curriculum_items?.map((item) => {
                                            const typeIcons = {
                                                lesson: '🎬',
                                                quiz: '📝',
                                                assignment: '📋',
                                                resource: '📄',
                                                live_class: '🎥'
                                            };
                                            const typeLabels = {
                                                lesson: 'পাঠ',
                                                quiz: 'কুইজ',
                                                assignment: 'অ্যাসাইনমেন্ট',
                                                resource: 'রিসোর্স',
                                                live_class: 'লাইভ ক্লাস'
                                            };
                                            return (
                                                <li key={item.id} className="flex items-center justify-between px-4 py-2.5 text-sm">
                                                    <span className="flex items-center gap-2">
                                                        <span>{typeIcons[item.item_type] ?? '🎬'}</span>
                                                        <span>{item.title_snapshot}</span>
                                                        <span className="text-xs text-brand-text/50 font-normal">({typeLabels[item.item_type]})</span>
                                                    </span>
                                                    {item.item_type === 'lesson' && item.is_preview ? (
                                                        <span className="font-semibold text-brand">ফ্রি প্রিভিউ</span>
                                                    ) : (
                                                        <span className="text-brand-text/40">🔒</span>
                                                    )}
                                                </li>
                                            );
                                        })}
                                        {(!section.curriculum_items || section.curriculum_items.length === 0) && (
                                            <li className="px-4 py-2.5 text-sm text-brand-text/40 italic text-center">কোনো পাঠ বা কুইজ যোগ করা হয়নি</li>
                                        )}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </section>
                </div>

                <aside>
                    {course.requirements?.length > 0 && (
                        <section className="card p-6">
                            <h2 className="text-lg font-bold text-brand-deep">যা প্রয়োজন হবে</h2>
                            <ul className="mt-3 space-y-2 text-sm">
                                {course.requirements.map((r) => <li key={r} className="flex items-start gap-2"><span className="text-brand">•</span>{r}</li>)}
                            </ul>
                        </section>
                    )}
                </aside>
            </div>

            {/* Recommendations: Learners Also Enrolled (Co-Enrollment) */}
            {learnersAlsoEnrolled && learnersAlsoEnrolled.length > 0 && (
                <RecommendedSection
                    title="যারা এই কোর্সটি নিয়েছেন তারা আরও নিয়েছেন"
                    subtitle="সহ-শিক্ষার্থীদের যৌথ পছন্দের পরিসংখ্যান অনুযায়ী"
                    badge="Co-Enrollment"
                    courses={learnersAlsoEnrolled}
                    theme="light"
                    className="py-12 bg-slate-50"
                />
            )}

            {/* Recommendations: Similar & Affinity Courses */}
            {similarCourses && similarCourses.length > 0 && (
                <RecommendedSection
                    title="সম্পর্কিত অন্যান্য কোর্স"
                    subtitle="একই বিষয় বা উস্তাযের অন্যান্য জনপ্রিয় কোর্স"
                    badge="Related Courses"
                    courses={similarCourses}
                    theme="light"
                    className="py-12 bg-white"
                />
            )}
        </AppLayout>
    );
}
