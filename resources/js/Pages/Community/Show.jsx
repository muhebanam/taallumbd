import { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function CommunityShow({ post, isLiked = false, relatedPosts = [] }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;
    const [copied, setCopied] = useState(false);

    const { data, setData, post: postComment, processing, reset, errors } = useForm({
        body: '',
    });

    const handleCommentSubmit = (e) => {
        e.preventDefault();
        postComment(`/community/${post.id}/comment`, {
            preserveScroll: true,
            onSuccess: () => reset('body'),
        });
    };

    const handleLike = () => {
        if (!user) {
            router.get('/login');
            return;
        }
        router.post(`/community/${post.id}/like`, {}, {
            preserveScroll: true,
        });
    };

    const handleMarkSolved = () => {
        router.post(`/community/${post.id}/solved`, {}, {
            preserveScroll: true,
        });
    };

    const handleCopy = () => {
        navigator.clipboard.writeText(window.location.href);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const isAuthor = user && user.id === post.user_id;
    const canManage = isAuthor || (user && ['admin', 'instructor'].includes(user.role));

    const topicLabels = {
        'quran-hadith': 'কুরআন ও হাদিস গবেষণা',
        'fiqh-masala': 'ফিকহ ও আহকাম জিজ্ঞাসা',
        'arabic-lang': 'আরবি ভাষা ও ব্যাকরণ',
        'course-qa': 'কোর্স সংক্রান্ত প্রশ্নোত্তর',
        'general': 'সাধারণ দ্বীনি আলোচনা',
    };

    return (
        <MainLayout>
            <Head title={`${post.title} — আত-তাআল্লুম কমিউনিটি`} />

            {/* Breadcrumb Header */}
            <div className="border-b border-gray-200 bg-gray-50/80 py-4">
                <div className="mx-auto flex max-w-5xl items-center gap-2 px-4 text-xs text-gray-500 sm:px-6">
                    <Link href="/" className="hover:text-gray-800">হোম</Link>
                    <span>/</span>
                    <Link href="/community" className="hover:text-gray-800">কমিউনিটি ফোরাম</Link>
                    <span>/</span>
                    <span className="font-medium text-[#102526] line-clamp-1">{post.title}</span>
                </div>
            </div>

            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                {flash?.success && (
                    <div className="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900">
                        {flash.success}
                    </div>
                )}

                <div className="gap-8 lg:flex">
                    {/* Main Discussion Thread */}
                    <div className="min-w-0 flex-1">
                        <article className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm">
                            {/* Badges & Meta */}
                            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-4">
                                <div className="flex items-center gap-2">
                                    <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {topicLabels[post.topic] || 'সাধারণ আলোচনা'}
                                    </span>
                                    {post.is_solved && (
                                        <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 flex items-center gap-1">
                                            ✓ সমাধানকৃত
                                        </span>
                                    )}
                                    {post.is_pinned && (
                                        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                            📌 পিনকৃত
                                        </span>
                                    )}
                                </div>

                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={handleCopy}
                                        className="rounded-xl border border-gray-200 px-3 py-1 text-xs text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        {copied ? 'কপি হয়েছে!' : 'শেয়ার করুন'}
                                    </button>
                                </div>
                            </div>

                            {/* Title */}
                            <h1 className="mt-4 text-2xl font-bold leading-snug text-[#102526] sm:text-3xl">
                                {post.title}
                            </h1>

                            {/* Author Info & Actions */}
                            <div className="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gray-50/80 p-3 text-xs">
                                <div className="flex items-center gap-2.5">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#102526] text-[#FFF99A] font-bold text-sm">
                                        {post.user?.name ? post.user.name.charAt(0) : 'স'}
                                    </div>
                                    <div>
                                        <p className="font-bold text-[#102526] flex items-center gap-1.5">
                                            {post.user?.name}
                                            {post.user?.role === 'instructor' && (
                                                <span className="rounded-full bg-emerald-100 px-2 py-0.2 text-[10px] font-bold text-emerald-800">উস্তায</span>
                                            )}
                                            {post.user?.role === 'admin' && (
                                                <span className="rounded-full bg-purple-100 px-2 py-0.2 text-[10px] font-bold text-purple-800">অ্যাডমিন</span>
                                            )}
                                        </p>
                                        <p className="text-[11px] text-gray-500">
                                            পোস্টের সময়: {new Date(post.created_at).toLocaleDateString('bn-BD', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            })}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    {/* Like Button */}
                                    <button
                                        onClick={handleLike}
                                        className={`inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 font-bold transition ${
                                            isLiked
                                                ? 'bg-emerald-100 text-emerald-900 border border-emerald-300'
                                                : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100'
                                        }`}
                                    >
                                        <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
                                        </svg>
                                        <span>{post.upvotes_count || 0} ভোট</span>
                                    </button>

                                    {/* Mark Solved Toggle */}
                                    {canManage && (
                                        <button
                                            onClick={handleMarkSolved}
                                            className={`rounded-xl px-3 py-1.5 font-bold transition ${
                                                post.is_solved
                                                    ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                    : 'bg-emerald-800 text-white hover:bg-emerald-900 shadow'
                                            }`}
                                        >
                                            {post.is_solved ? 'সমাধান বাতিল' : '✓ সমাধান চিহ্নিত করুন'}
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Body */}
                            <div className="mt-6 text-base leading-relaxed text-gray-800 whitespace-pre-line space-y-4">
                                {post.body}
                            </div>

                            {/* Linked Course Card */}
                            {post.course && (
                                <div className="mt-8 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 flex items-center justify-between gap-4">
                                    <div>
                                        <span className="text-[11px] font-bold text-blue-800 uppercase tracking-wider">সম্পর্কিত কোর্স</span>
                                        <h4 className="font-bold text-sm text-blue-950 mt-0.5">{post.course.title}</h4>
                                    </div>
                                    <Link
                                        href={`/courses/${post.course.slug}`}
                                        className="shrink-0 rounded-xl bg-blue-700 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-blue-800"
                                    >
                                        কোর্স দেখুন
                                    </Link>
                                </div>
                            )}
                        </article>

                        {/* Comments & Answers Section */}
                        <section className="mt-8">
                            <h2 className="text-xl font-bold text-[#102526] flex items-center gap-2">
                                <span>মন্তব্য ও আলোচনা</span>
                                <span className="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-800">
                                    {post.comments ? post.comments.length : 0}
                                </span>
                            </h2>

                            {/* Comment Form */}
                            {user ? (
                                <form onSubmit={handleCommentSubmit} className="mt-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                    <label className="block text-xs font-bold text-gray-700 mb-2">
                                        আপনার ইলমী মন্তব্য বা উত্তর যুক্ত করুন:
                                    </label>
                                    <textarea
                                        rows={4}
                                        required
                                        className="w-full rounded-2xl border border-gray-300 p-4 text-sm text-gray-900 placeholder-gray-400 focus:border-[#102526] focus:outline-none focus:ring-2 focus:ring-[#102526]/20 transition"
                                        placeholder="দলীলের সাথে আদব রক্ষা করে আপনার গঠনমূলক মতামত লিখুন..."
                                        value={data.body}
                                        onChange={(e) => setData('body', e.target.value)}
                                    />
                                    {errors.body && <p className="mt-1 text-xs text-rose-600">{errors.body}</p>}
                                    <div className="mt-3 flex justify-end">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="rounded-xl bg-[#102526] px-6 py-2.5 text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F] disabled:opacity-50"
                                        >
                                            {processing ? 'যুক্ত হচ্ছে...' : 'মন্তব্য প্রকাশ করুন'}
                                        </button>
                                    </div>
                                </form>
                            ) : (
                                <div className="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-xs text-gray-600">
                                    মন্তব্য বা আলোচনায় অংশ নিতে অনুগ্রহ করে{' '}
                                    <Link href="/login" className="font-bold text-emerald-800 underline">লগইন করুন</Link>।
                                </div>
                            )}

                            {/* Comments List */}
                            <div className="mt-6 space-y-4">
                                {post.comments && post.comments.length > 0 ? (
                                    post.comments.map((comment) => {
                                        const isTeacher = comment.user?.role === 'instructor';
                                        const isAdmin = comment.user?.role === 'admin';
                                        return (
                                            <div
                                                key={comment.id}
                                                className={`rounded-2xl border p-5 shadow-sm transition ${
                                                    isTeacher
                                                        ? 'border-emerald-700/40 bg-emerald-50/30'
                                                        : 'border-gray-200 bg-white'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between text-xs text-gray-500">
                                                    <div className="flex items-center gap-2">
                                                        <div className={`flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs ${
                                                            isTeacher ? 'bg-emerald-800 text-white' : 'bg-gray-200 text-gray-800'
                                                        }`}>
                                                            {comment.user?.name ? comment.user.name.charAt(0) : 'ম'}
                                                        </div>
                                                        <span className="font-bold text-gray-900">{comment.user?.name}</span>
                                                        {isTeacher && (
                                                            <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                                                উস্তায
                                                            </span>
                                                        )}
                                                        {isAdmin && (
                                                            <span className="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800">
                                                                অ্যাডমিন
                                                            </span>
                                                        )}
                                                    </div>
                                                    <span>{new Date(comment.created_at).toLocaleDateString('bn-BD')}</span>
                                                </div>

                                                <p className="mt-3 text-sm leading-relaxed text-gray-800 whitespace-pre-line">
                                                    {comment.body}
                                                </p>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <p className="text-center text-xs text-gray-400 py-6">
                                        এখনো কোনো মন্তব্য যুক্ত হয়নি। আপনি প্রথম মন্তব্য করতে পারেন।
                                    </p>
                                )}
                            </div>
                        </section>
                    </div>

                    {/* Sidebar */}
                    <aside className="mt-10 lg:mt-0 w-full shrink-0 lg:w-72 space-y-6">
                        {/* Ask or Post Button */}
                        <Link
                            href="/community/create"
                            className="block w-full rounded-2xl bg-[#102526] py-3.5 text-center text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                        >
                            + নতুন পোস্ট বা প্রশ্ন লিখুন
                        </Link>

                        {/* Related Discussions */}
                        {relatedPosts.length > 0 && (
                            <div className="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 className="font-bold text-sm text-[#102526]">
                                    সম্পর্কিত অন্যান্য আলোচনা
                                </h4>
                                <div className="mt-3 space-y-2.5">
                                    {relatedPosts.map((rp) => (
                                        <Link
                                            key={rp.id}
                                            href={`/community/${rp.id}`}
                                            className="block rounded-xl p-2 text-xs hover:bg-gray-50 transition"
                                        >
                                            <p className="font-semibold text-gray-900 line-clamp-2 hover:text-emerald-800">
                                                {rp.title}
                                            </p>
                                            <span className="mt-1 text-[10px] text-gray-400">
                                                {rp.comments_count || 0} টি মন্তব্য
                                            </span>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </aside>
                </div>
            </div>
        </MainLayout>
    );
}
