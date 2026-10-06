import { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function CommunityShow({ post, isLiked = false, userVote = null, relatedPosts = [] }) {
    const { auth } = usePage().props;
    const user = auth.user;

    const [copied, setCopied] = useState(false);
    const [replyingTo, setReplyingTo] = useState(null); // comment id being replied to
    const [replyBody, setReplyBody] = useState('');
    const [reportModal, setReportModal] = useState(null); // { type: 'post'|'comment', id }
    const [reportReason, setReportReason] = useState('inappropriate');
    const [reportDetails, setReportDetails] = useState('');

    const { data, setData, post: submitComment, processing, reset, errors } = useForm({
        body: '',
    });

    const isAuthor = user && user.id === post.user_id;
    const isTeacher = user && user.role === 'instructor';
    const isAdmin = user && user.role === 'admin';
    const canManage = isAuthor || isTeacher || isAdmin;
    const canVerify = isTeacher || isAdmin;

    const handleCopy = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    const handleVotePost = (voteType) => {
        if (!user) {
            router.get('/login');
            return;
        }
        router.post(`/community/${post.id}/vote`, { vote_type: voteType }, {
            preserveScroll: true,
        });
    };

    const handleVoteComment = (commentId, voteType) => {
        if (!user) {
            router.get('/login');
            return;
        }
        router.post(`/community/comments/${commentId}/vote`, { vote_type: voteType }, {
            preserveScroll: true,
        });
    };

    const handleVerifyComment = (commentId) => {
        if (!canVerify) return;
        router.post(`/community/comments/${commentId}/verify`, {}, {
            preserveScroll: true,
        });
    };

    const handleMarkSolved = () => {
        router.post(`/community/${post.id}/solved`, {}, {
            preserveScroll: true,
        });
    };

    const handleCommentSubmit = (e) => {
        e.preventDefault();
        submitComment(`/community/${post.id}/comment`, {
            onSuccess: () => reset(),
            preserveScroll: true,
        });
    };

    const handleReplySubmit = (parentId, e) => {
        e.preventDefault();
        if (!replyBody.trim()) return;

        router.post(`/community/${post.id}/comment`, {
            body: replyBody,
            parent_id: parentId,
        }, {
            onSuccess: () => {
                setReplyingTo(null);
                setReplyBody('');
            },
            preserveScroll: true,
        });
    };

    const handleReportSubmit = (e) => {
        e.preventDefault();
        if (!reportModal) return;

        router.post('/community/report', {
            type: reportModal.type,
            id: reportModal.id,
            reason: reportReason,
            details: reportDetails,
        }, {
            onSuccess: () => {
                setReportModal(null);
                setReportDetails('');
            },
            preserveScroll: true,
        });
    };

    return (
        <MainLayout>
            <Head title={`${post.title} — আত-তাআল্লুম ফোরাম`} />

            {/* Breadcrumb Header */}
            <div className="bg-[#102526] py-8 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-2 text-xs text-white/60">
                        <Link href="/community" className="text-[#FFF99A] hover:underline">
                            কমিউনিটি ফোরাম
                        </Link>
                        <span>/</span>
                        <span className="capitalize">{post.topic}</span>
                        <span>/</span>
                        <span className="truncate text-white/90">আলোচনা #{post.id}</span>
                    </div>
                </div>
            </div>

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="gap-8 lg:flex">
                    {/* Main Content Area */}
                    <div className="min-w-0 flex-1">
                        <article className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm">
                            {/* Badges & Meta */}
                            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-4">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900">
                                        {post.category?.name || 'সাধারণ দ্বীনি মতবিনিময়'}
                                    </span>
                                    {post.is_solved && (
                                        <span className="rounded-full bg-emerald-700 px-3 py-1 text-xs font-bold text-white shadow-sm flex items-center gap-1">
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
                                    <button
                                        onClick={() => setReportModal({ type: 'post', id: post.id })}
                                        className="rounded-xl border border-rose-100 bg-rose-50 px-2.5 py-1 text-xs text-rose-700 hover:bg-rose-100 transition"
                                    >
                                        রিপোর্ট
                                    </button>
                                </div>
                            </div>

                            {/* Title */}
                            <h1 className="mt-4 text-2xl font-bold leading-snug text-[#102526] sm:text-3xl">
                                {post.title}
                            </h1>

                            {/* Tags */}
                            {post.tags && post.tags.length > 0 && (
                                <div className="mt-3 flex flex-wrap gap-1.5">
                                    {post.tags.map((tag, idx) => (
                                        <span key={idx} className="rounded-lg bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                                            #{tag}
                                        </span>
                                    ))}
                                </div>
                            )}

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
                                            {post.user?.reputation_level || 'নবিশ তালিবুল ইলম'} • {new Date(post.created_at).toLocaleDateString('bn-BD')}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    {/* Upvote Button */}
                                    <button
                                        onClick={() => handleVotePost('upvote')}
                                        className={`inline-flex items-center gap-1 rounded-xl px-3 py-1.5 font-bold transition ${
                                            userVote === 'upvote'
                                                ? 'bg-emerald-100 text-emerald-900 border border-emerald-300'
                                                : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100'
                                        }`}
                                    >
                                        ▲ <span>{post.upvotes_count || 0}</span>
                                    </button>

                                    {/* Downvote Button */}
                                    <button
                                        onClick={() => handleVotePost('downvote')}
                                        className={`inline-flex items-center gap-1 rounded-xl px-2.5 py-1.5 font-bold transition ${
                                            userVote === 'downvote'
                                                ? 'bg-rose-100 text-rose-900 border border-rose-300'
                                                : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-100'
                                        }`}
                                    >
                                        ▼ <span>{post.downvotes_count || 0}</span>
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
                        </article>

                        {/* Comments / Answers Section */}
                        <section className="mt-10">
                            <h2 className="text-xl font-bold text-gray-900 flex items-center justify-between">
                                <span>ইলমী মতামত ও উত্তরসমূহ</span>
                                <span className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                                    {post.comments ? post.comments.length : 0} টি মন্তব্য
                                </span>
                            </h2>

                            {/* Comment Form */}
                            {user ? (
                                <form onSubmit={handleCommentSubmit} className="mt-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                    <label className="block text-xs font-bold text-gray-700 mb-2">
                                        আপনার ইলমী মন্তব্য বা উত্তর যুক্ত করুন (@mention সমর্থনসহ):
                                    </label>
                                    <textarea
                                        rows={4}
                                        required
                                        className="w-full rounded-2xl border border-gray-300 p-4 text-sm text-gray-900 placeholder-gray-400 focus:border-[#102526] focus:outline-none focus:ring-2 focus:ring-[#102526]/20 transition"
                                        placeholder="দলীলের সাথে আদব রক্ষা করে আপনার গঠনমূলক মতামত লিখুন... (যেমন: @উস্তায আহমদ)"
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

                            {/* Comments List (Threaded) */}
                            <div className="mt-6 space-y-4">
                                {post.comments && post.comments.length > 0 ? (
                                    post.comments.map((comment) => (
                                        <div
                                            key={comment.id}
                                            className={`rounded-2xl border p-5 shadow-sm transition ${
                                                comment.is_scholar_verified
                                                    ? 'border-emerald-600 bg-emerald-50/40 ring-1 ring-emerald-500'
                                                    : comment.user?.role === 'instructor'
                                                    ? 'border-emerald-700/40 bg-emerald-50/20'
                                                    : 'border-gray-200 bg-white'
                                            }`}
                                        >
                                            {/* Scholar Verified Seal Badge */}
                                            {comment.is_scholar_verified && (
                                                <div className="mb-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-3 py-1 text-xs font-bold text-white shadow-sm">
                                                    ✓ স্কলার-যাচাইকৃত উত্তর
                                                    {comment.verified_by_scholar && (
                                                        <span className="text-emerald-200 font-normal">
                                                            (যাচাই করেছেন: {comment.verified_by_scholar.name})
                                                        </span>
                                                    )}
                                                </div>
                                            )}

                                            <div className="flex items-center justify-between text-xs text-gray-500">
                                                <div className="flex items-center gap-2">
                                                    <div className="flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs bg-[#102526] text-white">
                                                        {comment.user?.name ? comment.user.name.charAt(0) : 'ম'}
                                                    </div>
                                                    <div>
                                                        <span className="font-bold text-gray-900">{comment.user?.name}</span>
                                                        <span className="text-[10px] text-gray-400 ml-1.5">
                                                            ({comment.user?.reputation_level || 'তালিবুল ইলম'})
                                                        </span>
                                                    </div>
                                                </div>
                                                <span>{new Date(comment.created_at).toLocaleDateString('bn-BD')}</span>
                                            </div>

                                            <p className="mt-3 text-sm leading-relaxed text-gray-800 whitespace-pre-line">
                                                {comment.body}
                                            </p>

                                            {/* Comment Actions & Vote */}
                                            <div className="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                                <div className="flex items-center gap-3">
                                                    <button
                                                        onClick={() => handleVoteComment(comment.id, 'upvote')}
                                                        className="font-bold text-emerald-800 hover:text-emerald-900 flex items-center gap-1"
                                                    >
                                                        ▲ {comment.upvotes_count || 0}
                                                    </button>
                                                    <button
                                                        onClick={() => setReplyingTo(replyingTo === comment.id ? null : comment.id)}
                                                        className="font-semibold text-gray-600 hover:text-gray-900"
                                                    >
                                                        রিপ্লাই
                                                    </button>
                                                    <button
                                                        onClick={() => setReportModal({ type: 'comment', id: comment.id })}
                                                        className="text-rose-600 hover:text-rose-800"
                                                    >
                                                        রিপোর্ট
                                                    </button>
                                                </div>

                                                {/* Scholar Verify Button */}
                                                {canVerify && (
                                                    <button
                                                        onClick={() => handleVerifyComment(comment.id)}
                                                        className={`rounded-lg px-2.5 py-1 text-xs font-bold transition ${
                                                            comment.is_scholar_verified
                                                                ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                                : 'bg-emerald-700 text-white hover:bg-emerald-800'
                                                        }`}
                                                    >
                                                        {comment.is_scholar_verified ? 'সিলমোহর বাতিল' : '★ স্কলার সিলমোহর দিন'}
                                                    </button>
                                                )}
                                            </div>

                                            {/* Reply Input Form */}
                                            {replyingTo === comment.id && (
                                                <form onSubmit={(e) => handleReplySubmit(comment.id, e)} className="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                                                    <input
                                                        type="text"
                                                        value={replyBody}
                                                        onChange={(e) => setReplyBody(e.target.value)}
                                                        placeholder="আপনার উত্তর লিখুন..."
                                                        className="flex-1 rounded-xl border-gray-200 text-xs focus:border-[#102526] focus:ring-[#102526]"
                                                    />
                                                    <button
                                                        type="submit"
                                                        className="rounded-xl bg-[#102526] px-4 py-1.5 text-xs font-bold text-white"
                                                    >
                                                        পাঠান
                                                    </button>
                                                </form>
                                            )}

                                            {/* Nested Replies */}
                                            {comment.replies && comment.replies.length > 0 && (
                                                <div className="mt-4 pl-4 border-l-2 border-gray-200 space-y-3">
                                                    {comment.replies.map((reply) => (
                                                        <div key={reply.id} className="rounded-xl bg-gray-50 p-3 text-xs">
                                                            <div className="font-bold text-gray-900">{reply.user?.name}</div>
                                                            <p className="mt-1 text-gray-800">{reply.body}</p>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    ))
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
                        <Link
                            href="/community/create"
                            className="block w-full rounded-2xl bg-[#102526] py-3.5 text-center text-xs font-bold text-[#FFF99A] shadow hover:bg-[#1A2E2F]"
                        >
                            + নতুন পোস্ট বা প্রশ্ন লিখুন
                        </Link>

                        {/* Quick links */}
                        <div className="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm space-y-2 text-xs">
                            <h4 className="font-bold text-gray-900 mb-2">কমিউনিটি হাব</h4>
                            <Link href="/community/groups" className="block text-[#102526] font-semibold hover:underline">
                                👥 ইসলামিক স্টাডি গ্রুপসমূহ →
                            </Link>
                            <Link href="/scholar-sessions" className="block text-emerald-800 font-semibold hover:underline">
                                🎙️ লাইভ স্কলার সেশন ও ওয়েবিনার →
                            </Link>
                        </div>

                        {/* Related Discussions */}
                        {relatedPosts.length > 0 && (
                            <div className="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 className="font-bold text-sm text-[#102526] mb-3">সম্পর্কিত আলোচনা</h4>
                                <ul className="space-y-3 text-xs">
                                    {relatedPosts.map((r) => (
                                        <li key={r.id}>
                                            <Link href={`/community/${r.id}`} className="font-medium text-gray-800 hover:text-[#102526] line-clamp-2">
                                                {r.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </aside>
                </div>
            </div>

            {/* Report Modal */}
            {reportModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-gray-900">মডারেশন রিপোর্ট দাখিল</h3>
                        <p className="mt-1 text-xs text-gray-500">অনুপযুক্ত ভাষা, বিভ্রান্তিকর তথ্য বা ফিতনামূলক আচরণের বিরুদ্ধে রিপোর্ট করুন</p>

                        <form onSubmit={handleReportSubmit} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-gray-700">রিপোর্টের কারণ:</label>
                                <select
                                    value={reportReason}
                                    onChange={(e) => setReportReason(e.target.value)}
                                    className="mt-1 w-full rounded-xl border-gray-300 text-xs"
                                >
                                    <option value="inappropriate">অনুপযুক্ত বা অশালীন ভাষা</option>
                                    <option value="false_information">বিভ্রান্তিকর বা ভুল ধর্মীয় তথ্য</option>
                                    <option value="harassment">ব্যক্তিগত আক্রমণ বা কটূক্তি</option>
                                    <option value="spam">স্প্যামিং বা বাণিজ্যিক লিংক</option>
                                    <option value="heresy_or_misguidance">আকীদাগত বিচ্যুতি বা ফিতনা</option>
                                    <option value="other">অন্যান্য কারণ</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-700">বিস্তারিত বিবরণ (ঐচ্ছিক):</label>
                                <textarea
                                    rows={3}
                                    value={reportDetails}
                                    onChange={(e) => setReportDetails(e.target.value)}
                                    placeholder="কেন আপনি এটিকে আপত্তিকর মনে করছেন..."
                                    className="mt-1 w-full rounded-xl border-gray-300 text-xs"
                                />
                            </div>

                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button
                                    type="button"
                                    onClick={() => setReportModal(null)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="submit"
                                    className="rounded-xl bg-rose-700 px-4 py-2 text-xs font-bold text-white hover:bg-rose-800"
                                >
                                    রিপোর্ট জমা দিন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </MainLayout>
    );
}
