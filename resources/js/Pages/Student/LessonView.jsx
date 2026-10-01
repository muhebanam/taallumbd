import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

function toEmbed(url) {
    if (!url) return null;
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/);
    return m ? `https://www.youtube.com/embed/${m[1]}?enablejsapi=1` : url;
}

export default function LessonView({ lesson, isCompleted, prevLesson, nextLesson }) {
    const embed = toEmbed(lesson.video_url);
    const [activeTab, setActiveTab] = useState('overview'); // overview | notes | bookmarks | comments

    // Note State
    const [noteText, setNoteText] = useState('');
    const [noteSaving, setNoteSaving] = useState(false);
    const [noteSavedMsg, setNoteSavedMsg] = useState('');

    // Bookmarks State
    const [bookmarks, setBookmarks] = useState([]);
    const [bmTitle, setBmTitle] = useState('');
    const [bmTime, setBmTime] = useState('');

    // Comments State
    const [comments, setComments] = useState([]);
    const [newComment, setNewComment] = useState('');
    const [commentPosting, setCommentPosting] = useState(false);

    // Load initial note & comments
    useEffect(() => {
        // Fetch Note
        fetch(`/dashboard/lessons/${lesson.id}/note`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.note) setNoteText(data.note);
        })
        .catch(() => {});

        // Fetch Comments
        fetch(`/dashboard/lessons/${lesson.id}/comments`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.comments) setComments(data.comments);
        })
        .catch(() => {});
    }, [lesson.id]);

    // Save Note Handler
    const handleSaveNote = async () => {
        setNoteSaving(true);
        setNoteSavedMsg('');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/dashboard/lessons/${lesson.id}/note`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ note: noteText })
            });
            const data = await res.json();
            if (data.success) {
                setNoteSavedMsg('নোট সফলভাবে সংরক্ষিত হয়েছে!');
                setTimeout(() => setNoteSavedMsg(''), 4000);
            }
        } catch (e) {
            setNoteSavedMsg('সংরক্ষণে ত্রুটি হয়েছে।');
        } finally {
            setNoteSaving(false);
        }
    };

    // Save Bookmark Handler
    const handleSaveBookmark = async (e) => {
        e.preventDefault();
        const seconds = parseInt(bmTime) || 0;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/dashboard/lessons/${lesson.id}/bookmarks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    timestamp_seconds: seconds,
                    title: bmTitle,
                })
            });
            const data = await res.json();
            if (data.success && data.bookmark) {
                setBookmarks([data.bookmark, ...bookmarks]);
                setBmTitle('');
                setBmTime('');
            }
        } catch (e) {}
    };

    // Delete Bookmark Handler
    const handleDeleteBookmark = async (bmId) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch(`/dashboard/bookmarks/${bmId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                }
            });
            setBookmarks(bookmarks.filter(b => b.id !== bmId));
        } catch (e) {}
    };

    // Post Comment Handler
    const handlePostComment = async (e) => {
        e.preventDefault();
        if (!newComment.trim()) return;
        setCommentPosting(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/dashboard/lessons/${lesson.id}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ body: newComment })
            });
            const data = await res.json();
            if (data.success && data.comment) {
                setComments([data.comment, ...comments]);
                setNewComment('');
            }
        } catch (e) {} finally {
            setCommentPosting(false);
        }
    };

    return (
        <DashboardLayout title={lesson.title}>
            <Head title={`${lesson.title} — আত-তাআল্লুম`} />

            {/* Breadcrumb Header */}
            <div className="flex items-center justify-between gap-4">
                <Link 
                    href={`/dashboard/courses/${lesson.course?.slug}`} 
                    className="inline-flex items-center gap-1.5 text-sm font-bold text-[#1A2E2F] hover:text-emerald-700 transition"
                >
                    <span>←</span>
                    <span>{lesson.course?.title}</span>
                </Link>

                {isCompleted ? (
                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                        <span>✔</span> সম্পন্ন হয়েছে
                    </span>
                ) : (
                    <button 
                        onClick={() => router.post(`/dashboard/lessons/${lesson.id}/complete`)} 
                        className="btn-primary !py-1.5 !px-3.5 text-xs"
                    >
                        পাঠ সম্পন্ন চিহ্নিত করুন
                    </button>
                )}
            </div>

            {/* Video Player */}
            {embed ? (
                <div className="card mt-4 aspect-video overflow-hidden shadow-lg border border-slate-200/80 bg-black">
                    <iframe 
                        src={embed} 
                        title={lesson.title} 
                        className="h-full w-full" 
                        allowFullScreen 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    />
                </div>
            ) : (
                <div className="card mt-4 p-8 text-center bg-emerald-950 text-white rounded-2xl">
                    <span className="text-3xl">📖</span>
                    <h3 className="mt-2 text-lg font-bold font-bangla">{lesson.title}</h3>
                    <p className="text-xs text-emerald-200 mt-1">টেক্সট ও রিসোর্স ভিত্তিক পাঠ</p>
                </div>
            )}

            {/* Navigation Buttons (Prev / Next) */}
            <div className="mt-4 flex items-center justify-between gap-2">
                <div>
                    {prevLesson && (
                        <Link 
                            href={`/dashboard/lessons/${prevLesson.id}`} 
                            className="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition"
                        >
                            <span>←</span> পূর্ববর্তী পাঠ
                        </Link>
                    )}
                </div>

                <div>
                    {nextLesson && (
                        <Link 
                            href={`/dashboard/lessons/${nextLesson.id}`} 
                            className="inline-flex items-center gap-1 rounded-xl bg-[#1A2E2F] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#102526] transition"
                        >
                            পরবর্তী পাঠ <span>→</span>
                        </Link>
                    )}
                </div>
            </div>

            {/* Interactive Lesson Tabs */}
            <div className="mt-8">
                <div className="flex border-b border-slate-200 gap-2 overflow-x-auto text-sm font-semibold">
                    <button
                        type="button"
                        onClick={() => setActiveTab('overview')}
                        className={`pb-3 px-3 transition-colors border-b-2 flex items-center gap-1.5 ${
                            activeTab === 'overview'
                                ? 'border-[#1A2E2F] text-[#1A2E2F] font-bold'
                                : 'border-transparent text-slate-500 hover:text-slate-800'
                        }`}
                    >
                        <span>📄</span>
                        <span>বিবরণ ও রিসোর্স</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('notes')}
                        className={`pb-3 px-3 transition-colors border-b-2 flex items-center gap-1.5 ${
                            activeTab === 'notes'
                                ? 'border-[#1A2E2F] text-[#1A2E2F] font-bold'
                                : 'border-transparent text-slate-500 hover:text-slate-800'
                        }`}
                    >
                        <span>📝</span>
                        <span>আমার নোটস</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('bookmarks')}
                        className={`pb-3 px-3 transition-colors border-b-2 flex items-center gap-1.5 ${
                            activeTab === 'bookmarks'
                                ? 'border-[#1A2E2F] text-[#1A2E2F] font-bold'
                                : 'border-transparent text-slate-500 hover:text-slate-800'
                        }`}
                    >
                        <span>🔖</span>
                        <span>বুকমার্কস</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('comments')}
                        className={`pb-3 px-3 transition-colors border-b-2 flex items-center gap-1.5 ${
                            activeTab === 'comments'
                                ? 'border-[#1A2E2F] text-[#1A2E2F] font-bold'
                                : 'border-transparent text-slate-500 hover:text-slate-800'
                        }`}
                    >
                        <span>💬</span>
                        <span>প্রশ্নোত্তর ও আলোচনা</span>
                    </button>
                </div>

                {/* Tab 1: Overview & Resources */}
                {activeTab === 'overview' && (
                    <div className="card mt-6 p-6 space-y-6">
                        <div>
                            <h2 className="text-lg font-bold text-[#142425] font-bangla">পাঠের সারসংক্ষেপ</h2>
                            <p className="mt-3 whitespace-pre-line leading-relaxed text-slate-700 text-sm">
                                {lesson.content || 'এই পাঠের জন্য কোনো অতিরিক্ত টেক্সট বিবরণ নেই।'}
                            </p>
                        </div>

                        {lesson.lecture_sheet && (
                            <div className="pt-4 border-t border-slate-100">
                                <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider">সংযুক্তি ফাইল</h4>
                                <a 
                                    href={`/storage/${lesson.lecture_sheet}`} 
                                    target="_blank" 
                                    rel="noreferrer" 
                                    className="mt-2 inline-flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/80 px-4 py-2.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition"
                                >
                                    <span>📥</span>
                                    <span>লেকচার শীট / পাঠ্য উপকরণ ডাউনলোড করুন (PDF)</span>
                                </a>
                            </div>
                        )}

                        {lesson.quizzes?.length > 0 && (
                            <div className="pt-4 border-t border-slate-100">
                                <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">এই পাঠের মূল্যায়ন কুইজ</h4>
                                <div className="space-y-2">
                                    {lesson.quizzes.map((q) => (
                                        <Link 
                                            key={q.id} 
                                            href={`/dashboard/quizzes/${q.id}`} 
                                            className="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs font-bold text-[#1A2E2F] hover:bg-[#1A2E2F] hover:text-white transition"
                                        >
                                            <span>📝 {q.title}</span>
                                            <span>কুইজে অংশ নিন →</span>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Tab 2: Personal Lesson Notes */}
                {activeTab === 'notes' && (
                    <div className="card mt-6 p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-base font-bold text-[#142425]">ব্যক্তিগত স্টাডি নোট</h3>
                                <p className="text-xs text-slate-500 mt-0.5">এই পাঠ সংক্রান্ত গুরুত্বপূর্ণ পয়েন্টগুলো লিখে রাখুন। এটি সম্পূর্ণ গোপনীয়।</p>
                            </div>
                            {noteSavedMsg && (
                                <span className="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                    {noteSavedMsg}
                                </span>
                            )}
                        </div>

                        <textarea
                            rows={8}
                            value={noteText}
                            onChange={(e) => setNoteText(e.target.value)}
                            placeholder="এখানে আপনার ব্যক্তিগত নোট লিখুন..."
                            className="mt-4 w-full rounded-2xl border border-slate-200 p-4 text-sm focus:border-[#1A2E2F] focus:ring-1 focus:ring-[#1A2E2F] leading-relaxed"
                        />

                        <div className="mt-4 flex justify-end">
                            <button
                                type="button"
                                onClick={handleSaveNote}
                                disabled={noteSaving}
                                className="btn-primary text-xs !py-2.5"
                            >
                                {noteSaving ? 'সংরক্ষণ হচ্ছে...' : 'নোট সংরক্ষণ করুন'}
                            </button>
                        </div>
                    </div>
                )}

                {/* Tab 3: Bookmarks */}
                {activeTab === 'bookmarks' && (
                    <div className="card mt-6 p-6 space-y-6">
                        <div>
                            <h3 className="text-base font-bold text-[#142425]">টাইমস্ট্যাম্প বুকমার্ক</h3>
                            <p className="text-xs text-slate-500 mt-0.5">ভিডিওর কোনো গুরুত্বপূর্ণ অংশ পরে পুনরায় দেখার জন্য বুকমার্ক যোগ করুন।</p>
                        </div>

                        {/* Add Bookmark Form */}
                        <form onSubmit={handleSaveBookmark} className="flex flex-col sm:flex-row gap-3">
                            <input
                                type="number"
                                min="0"
                                placeholder="সেকেন্ড (যেমন: 120)"
                                value={bmTime}
                                onChange={(e) => setBmTime(e.target.value)}
                                className="w-full sm:w-44 rounded-xl border border-slate-200 text-xs py-2.5 px-3"
                                required
                            />
                            <input
                                type="text"
                                placeholder="বুকমার্কের শিরোনাম (ঐচ্ছিক)"
                                value={bmTitle}
                                onChange={(e) => setBmTitle(e.target.value)}
                                className="flex-1 rounded-xl border border-slate-200 text-xs py-2.5 px-3"
                            />
                            <button type="submit" className="btn-primary text-xs !py-2.5">
                                + বুকমার্ক যোগ করুন
                            </button>
                        </form>

                        {/* Bookmark List */}
                        <div className="space-y-2 pt-2 border-t border-slate-100">
                            {bookmarks.length > 0 ? (
                                bookmarks.map((b) => (
                                    <div key={b.id} className="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs">
                                        <div className="flex items-center gap-2">
                                            <span className="rounded bg-[#1A2E2F] text-[#FFF99A] font-mono px-2 py-0.5 text-[11px] font-bold">
                                                ⏱ {Math.floor(b.timestamp_seconds / 60)}:{(b.timestamp_seconds % 60).toString().padStart(2, '0')}
                                            </span>
                                            <span className="font-semibold text-slate-800">{b.title}</span>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => handleDeleteBookmark(b.id)}
                                            className="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2"
                                        >
                                            মুছুন
                                        </button>
                                    </div>
                                ))
                            ) : (
                                <p className="text-xs text-slate-500 py-4 text-center">এখনও কোনো বুকমার্ক যুক্ত করা হয়নি।</p>
                            )}
                        </div>
                    </div>
                )}

                {/* Tab 4: Comments & Q&A */}
                {activeTab === 'comments' && (
                    <div className="card mt-6 p-6 space-y-6">
                        <div>
                            <h3 className="text-base font-bold text-[#142425]">পাঠভিত্তিক প্রশ্নোত্তর ও আলোচনা</h3>
                            <p className="text-xs text-slate-500 mt-0.5">এই পাঠের কোনো বিষয় বুঝতে অসুবিধা হলে সরাসরি উস্তায ও সহপাঠীদের প্রশ্ন করুন।</p>
                        </div>

                        {/* Post Comment Form */}
                        <form onSubmit={handlePostComment} className="space-y-3">
                            <textarea
                                rows={3}
                                value={newComment}
                                onChange={(e) => setNewComment(e.target.value)}
                                placeholder="আপনার প্রশ্ন বা মতামত এখানে লিখুন..."
                                className="w-full rounded-2xl border border-slate-200 p-3 text-xs focus:border-[#1A2E2F] focus:ring-1 focus:ring-[#1A2E2F]"
                                required
                            />
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={commentPosting}
                                    className="btn-primary text-xs !py-2"
                                >
                                    {commentPosting ? 'পোস্ট হচ্ছে...' : 'প্রশ্ন / মন্তব্য পোস্ট করুন'}
                                </button>
                            </div>
                        </form>

                        {/* Comments List */}
                        <div className="space-y-4 pt-4 border-t border-slate-100">
                            {comments.length > 0 ? (
                                comments.map((c) => (
                                    <div key={c.id} className="rounded-2xl bg-slate-50 border border-slate-200 p-4 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div className="h-6 w-6 rounded-full bg-[#1A2E2F] text-white text-[10px] font-bold flex items-center justify-center">
                                                    {c.user?.name ? c.user.name.charAt(0) : 'উ'}
                                                </div>
                                                <span className="text-xs font-bold text-slate-800">{c.user?.name}</span>
                                                {c.user?.role === 'instructor' && (
                                                    <span className="rounded bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.2 font-semibold">
                                                        উস্তায
                                                    </span>
                                                )}
                                            </div>
                                            <span className="text-[10px] text-slate-400">
                                                {new Date(c.created_at).toLocaleDateString('bn-BD')}
                                            </span>
                                        </div>
                                        <p className="text-xs text-slate-700 leading-relaxed pl-8">
                                            {c.body}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <p className="text-xs text-slate-500 py-4 text-center">এখনও কোনো প্রশ্ন করা হয়নি। প্রথম প্রশ্নটি করুন!</p>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
