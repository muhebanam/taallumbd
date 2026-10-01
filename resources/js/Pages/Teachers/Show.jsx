import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import TeacherProfileHeader from '../../Components/TeacherProfileHeader';
import TeacherTabs from '../../Components/TeacherTabs';
import TeacherExpertiseMap from '../../Components/TeacherExpertiseMap';
import TeacherTimeline from '../../Components/TeacherTimeline';
import TeacherQuestionForm from '../../Components/TeacherQuestionForm';
import TeacherReviewForm from '../../Components/TeacherReviewForm';

export default function ScholarShow({ teacher, courses = [], articles = [], fatawa = [], publications = [], reviews = [], questions = [], fatwaCategories = [], studentsCount = 0, isFollowing = false }) {
    const [activeTab, setActiveTab] = useState('পরিচিতি');

    // Tab change scrolling / setting
    const handleTabChange = (tabName) => {
        setActiveTab(tabName);
        const element = document.getElementById('profile-content-section');
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <AppLayout>
            <Head title={`${teacher.name} — শিক্ষকের প্রোফাইল`} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:py-12">
                {/* Profile Header */}
                <TeacherProfileHeader 
                    teacher={teacher} 
                    isFollowing={isFollowing} 
                    onTabChange={handleTabChange}
                    studentsCount={studentsCount}
                />

                {/* Tabs & Content Grid */}
                <div className="mt-8 grid gap-8 lg:grid-cols-3">
                    
                    {/* Main Content Column (Left on Desktop, Full width on Mobile) */}
                    <div className="lg:col-span-2 space-y-6">
                        
                        {/* Sticky Navigation Tabs */}
                        <div id="profile-content-section" className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                            <TeacherTabs 
                                activeTab={activeTab} 
                                setActiveTab={setActiveTab} 
                                teacher={teacher}
                            />
                            
                            {/* Tab Content Panels */}
                            <div className="p-6">
                                
                                {/* Tab 1: পরিচিতি */}
                                {activeTab === 'পরিচিতি' && (
                                    <div className="space-y-8">
                                        {/* Full Bio */}
                                        <div>
                                            <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">বিস্তারিত পরিচিতি</h3>
                                            {teacher.bio ? (
                                                <div className="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600 whitespace-pre-line">
                                                    {teacher.bio}
                                                </div>
                                            ) : (
                                                <p className="text-sm text-slate-400 italic">এই শিক্ষকের বিস্তারিত পরিচিতি শীঘ্রই যুক্ত হবে।</p>
                                            )}
                                        </div>

                                        {/* Knowledge Path */}
                                        {Array.isArray(teacher.knowledge_path) && teacher.knowledge_path.length > 0 && (
                                            <div>
                                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">এই শিক্ষকের মাধ্যমে আপনি শিখতে পারবেন</h3>
                                                <div className="grid gap-3 sm:grid-cols-2">
                                                    {teacher.knowledge_path.map((point, idx) => (
                                                        <div key={idx} className="flex items-center gap-3 rounded-xl border border-slate-100 p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                                            <span className="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 text-sm font-bold shrink-0">
                                                                {idx + 1}
                                                            </span>
                                                            <span className="text-sm font-bold text-slate-700">{point}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Expertise Map */}
                                        <TeacherExpertiseMap 
                                            expertiseMap={teacher.expertise_map} 
                                            specialties={teacher.specialties}
                                        />

                                        {/* Education Timeline */}
                                        <TeacherTimeline 
                                            items={teacher.qualifications} 
                                            type="education"
                                        />

                                        {/* Experience Timeline */}
                                        <TeacherTimeline 
                                            items={teacher.experiences} 
                                            type="experience"
                                        />
                                    </div>
                                )}

                                {/* Tab 2: কোর্সসমূহ */}
                                {activeTab === 'কোর্সসমূহ' && (
                                    <div>
                                        {courses.length > 0 ? (
                                            <div className="grid gap-6 sm:grid-cols-2">
                                                {courses.map((course) => (
                                                    <div key={course.id} className="group overflow-hidden rounded-xl border border-slate-150 bg-white hover:shadow transition-all duration-300">
                                                        <img 
                                                            src={course.thumbnail ? (course.thumbnail.startsWith('http') ? course.thumbnail : `/storage/${course.thumbnail}`) : '/images/course-default.jpg'} 
                                                            alt={course.title}
                                                            className="h-40 w-full object-cover group-hover:scale-102 transition-transform duration-300"
                                                        />
                                                        <div className="p-4">
                                                            <span className="inline-block rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{course.category?.name}</span>
                                                            <h4 className="mt-2 text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-emerald-700 transition-colors">{course.title}</h4>
                                                            <p className="mt-1 text-xs text-slate-500 line-clamp-2 leading-relaxed">{course.short_description}</p>
                                                            <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 font-semibold">
                                                                <span>{course.lessons_count ?? 0} লেসন</span>
                                                                <span className="text-emerald-800 text-sm font-extrabold">{course.is_free ? 'ফ্রি' : `৳${parseInt(course.price)}`}</span>
                                                            </div>
                                                            <Link 
                                                                href={`/courses/${course.slug}`}
                                                                className="mt-3 block w-full text-center rounded-lg bg-[#102526] hover:bg-[#1A2E2F] text-white text-xs font-bold py-2 transition-colors"
                                                            >
                                                                কোর্স দেখুন
                                                            </Link>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="py-12 text-center text-slate-400">
                                                <svg className="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                <p className="mt-3 text-sm font-semibold">এই শিক্ষকের কোনো কোর্স এখনো প্রকাশিত হয়নি।</p>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Tab 3: প্রবন্ধ */}
                                {activeTab === 'প্রবন্ধ' && (
                                    <div className="space-y-4">
                                        {articles.length > 0 ? (
                                            articles.map((article) => (
                                                <div key={article.id} className="rounded-xl border border-slate-100 p-4 hover:bg-slate-50/50 transition-colors">
                                                    <div className="flex items-center gap-2">
                                                        <span className="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">{article.category?.name}</span>
                                                        <span className="text-[10px] text-slate-400">{new Date(article.published_at ?? article.created_at).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                                    </div>
                                                    <h4 className="mt-2 text-sm font-bold text-slate-800 hover:text-emerald-700"><Link href={`/articles/${article.slug}`}>{article.title}</Link></h4>
                                                    <p className="mt-1 text-xs text-slate-500 leading-relaxed line-clamp-2">{article.excerpt}</p>
                                                    <Link 
                                                        href={`/articles/${article.slug}`}
                                                        className="mt-3 inline-flex items-center text-xs font-bold text-brand-deep hover:underline"
                                                    >
                                                        <span>পড়ুন</span>
                                                        <svg className="ml-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </Link>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="py-12 text-center text-slate-400">
                                                <svg className="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 4a2 2 0 012 2v8a2 2 0 01-2 2h-3l-1 1-1-1m-3-12h.01M16 16h.01M21 12h.01M12 12h.01M12 16h.01M8 8h.01M8 12h.01M8 16h.01" />
                                                </svg>
                                                <p className="mt-3 text-sm font-semibold">এই শিক্ষকের কোনো প্রবন্ধ এখনো প্রকাশিত হয়নি।</p>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Tab 4: ফাতাওয়া */}
                                {activeTab === 'ফাতাওয়া' && (
                                    <div className="space-y-4">
                                        {fatawa.length > 0 ? (
                                            fatawa.map((fatwa) => (
                                                <div key={fatwa.id} className="rounded-xl border border-slate-100 p-5 bg-amber-50/10 hover:bg-amber-50/20 transition-all">
                                                    <div className="flex items-center gap-2">
                                                        <span className="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">{fatwa.category?.name}</span>
                                                        <span className="text-[10px] text-slate-400">{new Date(fatwa.published_at ?? fatwa.created_at).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                                    </div>
                                                    <h4 className="mt-2 text-sm font-bold text-slate-800"><Link href={`/fatawa/${fatwa.id}`}>{fatwa.question_title}</Link></h4>
                                                    <p className="mt-1 text-xs text-slate-500 line-clamp-2 leading-relaxed">প্রশ্ন: {fatwa.question_body}</p>
                                                    <div className="mt-3 border-t border-slate-100 pt-3">
                                                        <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wide">উত্তর সংক্ষেপ:</span>
                                                        <p className="mt-1 text-xs text-slate-600 line-clamp-3 leading-relaxed">{fatwa.answer_body}</p>
                                                    </div>
                                                    <Link 
                                                        href={`/fatawa/${fatwa.id}`}
                                                        className="mt-3 inline-flex items-center text-xs font-bold text-[#102526] hover:underline"
                                                    >
                                                        <span>বিস্তারিত পড়ুন</span>
                                                        <svg className="ml-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </Link>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="py-12 text-center text-slate-400">
                                                <svg className="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p className="mt-3 text-sm font-semibold">এই শিক্ষকের কোনো ফাতাওয়া এখনো প্রকাশিত হয়নি।</p>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Tab 5: প্রকাশনা / বই */}
                                {activeTab === 'প্রকাশনা / বই' && (
                                    <div>
                                        {publications.length > 0 ? (
                                            <div className="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                                                {publications.map((pub) => (
                                                    <div key={pub.id} className="group flex flex-col justify-between rounded-xl border border-slate-100 bg-white p-4 shadow-sm hover:shadow transition-all duration-300">
                                                        <div>
                                                            <div className="relative aspect-[3/4] w-full overflow-hidden rounded-lg bg-slate-50 border border-slate-100">
                                                                <img 
                                                                    src={pub.thumbnail ? (pub.thumbnail.startsWith('http') ? pub.thumbnail : `/storage/${pub.thumbnail}`) : '/images/book-default.jpg'} 
                                                                    alt={pub.title}
                                                                    className="h-full w-full object-contain p-2 group-hover:scale-102 transition-transform duration-300"
                                                                />
                                                                <span className="absolute top-2 left-2 rounded-md bg-[#102526] px-2 py-0.5 text-[9px] font-bold text-white uppercase">{pub.type}</span>
                                                            </div>
                                                            <h4 className="mt-3 text-xs font-bold text-slate-800 line-clamp-1">{pub.title}</h4>
                                                            <p className="mt-1 text-[10px] text-slate-400 line-clamp-2 leading-relaxed">{pub.description}</p>
                                                        </div>
                                                        <div className="mt-4 pt-3 border-t border-slate-100">
                                                            <a 
                                                                href={pub.file_url ?? pub.external_url ?? '#'}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="block w-full text-center rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-[10px] font-bold py-2 transition-colors"
                                                            >
                                                                {pub.type === 'book' || pub.type === 'ebook' ? 'ডাউনলোড / পড়ুন' : 'বিস্তারিত দেখুন'}
                                                            </a>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="py-12 text-center text-slate-400">
                                                <svg className="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                <p className="mt-3 text-sm font-semibold">এই শিক্ষকের কোনো প্রকাশনা এখনো যুক্ত হয়নি।</p>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Tab 6: রিভিউ */}
                                {activeTab === 'রিভিউ' && (
                                    <div className="space-y-6">
                                        {/* Statistics header */}
                                        <div className="rounded-xl bg-slate-50 border border-slate-100 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div>
                                                <span className="text-3xl font-extrabold text-slate-800">{teacher.average_rating}</span>
                                                <span className="text-sm font-bold text-slate-500"> / ৫.০</span>
                                                <p className="text-xs text-slate-400 mt-1">গড় রেটিং (মোট {reviews.length} মতামত)</p>
                                            </div>
                                            <div className="flex items-center text-amber-400">
                                                {[...Array(5)].map((_, i) => (
                                                    <svg 
                                                        key={i} 
                                                        className={`h-5 w-5 ${i < Math.round(parseFloat(teacher.average_rating) || 0) ? 'fill-current' : 'text-slate-200'}`} 
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                ))}
                                            </div>
                                        </div>

                                        {reviews.length > 0 ? (
                                            <div className="space-y-4">
                                                {reviews.map((rev) => (
                                                    <div key={rev.id} className="rounded-xl border border-slate-100 p-4">
                                                        <div className="flex items-center justify-between gap-4">
                                                            <div>
                                                                <h5 className="font-bold text-slate-800 text-sm">{rev.user?.name}</h5>
                                                                {rev.course && (
                                                                    <p className="text-[10px] text-slate-400 font-semibold mt-0.5">{rev.course.title}</p>
                                                                )}
                                                            </div>
                                                            <div className="flex items-center text-amber-500 shrink-0">
                                                                {[...Array(5)].map((_, i) => (
                                                                    <svg 
                                                                        key={i} 
                                                                        className={`h-3 w-3 ${i < rev.rating ? 'fill-current' : 'text-slate-200'}`} 
                                                                        viewBox="0 0 20 20"
                                                                    >
                                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                    </svg>
                                                                ))}
                                                            </div>
                                                        </div>
                                                        {rev.review && (
                                                            <p className="mt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50/50 border border-slate-100 rounded-lg p-3">{rev.review}</p>
                                                        )}
                                                        <span className="text-[9px] text-slate-400 block mt-2 text-right">{new Date(rev.created_at).toLocaleDateString('bn-BD')}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="py-8 text-center text-slate-400 italic text-xs">
                                                এখনো কোনো মতামত পাওয়া যায়নি।
                                            </div>
                                        )}

                                        {/* Add Review Form (conditional rendering) */}
                                        <div className="border-t border-slate-100 pt-6">
                                            <TeacherReviewForm teacher={teacher} courses={courses} />
                                        </div>
                                    </div>
                                )}

                                {/* Tab 7: প্রশ্ন করুন */}
                                {activeTab === 'প্রশ্ন করুন' && (
                                    <div className="space-y-6">
                                        <TeacherQuestionForm teacher={teacher} categories={fatwaCategories} />

                                        {/* List answered public questions */}
                                        <div className="mt-8">
                                            <h4 className="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">ইতিপূর্বে উত্তর দেওয়া প্রশ্নসমূহ ({questions.length})</h4>
                                            {questions.length > 0 ? (
                                                <div className="space-y-4">
                                                    {questions.map((q) => (
                                                        <div key={q.id} className="rounded-xl border border-slate-100 p-4 bg-slate-50/30">
                                                            <h5 className="font-bold text-slate-800 text-sm">বিষয়: {q.subject}</h5>
                                                            <p className="text-xs text-slate-500 mt-2 whitespace-pre-line leading-relaxed">প্রশ্ন: {q.question_body}</p>
                                                            <div className="mt-3 border-t border-slate-100 pt-3 bg-emerald-50/20 rounded-lg p-3 border border-emerald-50">
                                                                <span className="text-[10px] font-bold text-emerald-800 uppercase tracking-wide block">শিক্ষকের উত্তর:</span>
                                                                <p className="mt-1 text-xs text-slate-700 leading-relaxed whitespace-pre-line">{q.answer_body}</p>
                                                            </div>
                                                            <div className="mt-2.5 flex items-center justify-between text-[9px] text-slate-400 font-semibold px-1">
                                                                <span>প্রশ্নকর্তা: {q.is_private ? 'গোপন' : (q.user?.name ?? 'জনৈক')}</span>
                                                                <span>উত্তর প্রদানের তারিখ: {new Date(q.answered_at ?? q.updated_at).toLocaleDateString('bn-BD')}</span>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p className="text-xs text-slate-400 italic text-center py-4">এখনো কোনো পাবলিক প্রশ্ন ও উত্তর পাওয়া যায়নি।</p>
                                            )}
                                        </div>
                                    </div>
                                )}

                            </div>
                        </div>

                    </div>

                    {/* Right Sidebar Column (Sticky on Desktop) */}
                    <div className="space-y-6">
                        
                        {/* Office Hours / Consultation time */}
                        {teacher.consultation_enabled && (
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                                    <svg className="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeD="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>প্রশ্নোত্তর সময়</span>
                                </h3>
                                
                                {teacher.consultation_note && (
                                    <p className="text-xs text-slate-500 leading-relaxed mt-3">{teacher.consultation_note}</p>
                                )}

                                {Array.isArray(teacher.office_hours) && teacher.office_hours.length > 0 && (
                                    <div className="mt-4 space-y-3">
                                        {teacher.office_hours.map((hour, idx) => (
                                            <div key={idx} className="rounded-xl border border-slate-100 bg-slate-50/50 p-3 text-xs">
                                                <div className="flex items-center justify-between font-bold text-slate-700">
                                                    <span>{hour.day}</span>
                                                    <span className="text-emerald-700">{hour.time}</span>
                                                </div>
                                                {hour.method && (
                                                    <div className="mt-1.5 flex items-center justify-between text-slate-400 font-semibold">
                                                        <span>পদ্ধতি:</span>
                                                        <span>{hour.method}</span>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Scholar Location / Visibility Card */}
                        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h3 className="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">যোগাযোগ ও তথ্য</h3>
                            
                            <div className="space-y-3 text-xs text-slate-600">
                                {teacher.show_email && teacher.email && (
                                    <div className="flex items-center gap-2">
                                        <svg className="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span className="font-semibold text-slate-700">{teacher.email}</span>
                                    </div>
                                )}

                                {teacher.show_phone && teacher.phone && (
                                    <div className="flex items-center gap-2">
                                        <svg className="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span className="font-semibold text-slate-700">{teacher.phone}</span>
                                    </div>
                                )}

                                {!teacher.show_email && !teacher.show_phone && (
                                    <p className="text-slate-400 italic">যোগাযোগের তথ্য হাইড করা রয়েছে।</p>
                                )}
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </AppLayout>
    );
}
