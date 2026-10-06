import { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function StudyGroupShow({
    group,
    isMember = false,
    userMembership = null,
    posts = { data: [] },
    activeMembers = [],
    inviteUrl = '',
}) {
    const { auth } = usePage().props;
    const [copied, setCopied] = useState(false);
    const [goalProgress, setGoalProgress] = useState(userMembership?.weekly_goal_progress || 0);

    const { data: postData, setData: setPostData, post: submitPost, processing: postProcessing, reset: resetPost } = useForm({
        body: '',
    });

    const [commentData, setCommentData] = useState({});

    const handleCopyInvite = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(inviteUrl);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    const handleJoin = (e) => {
        e.preventDefault();
        if (!auth.user) {
            router.get('/login');
            return;
        }
        router.post(`/community/groups/${group.slug}/join`);
    };

    const handlePostSubmit = (e) => {
        e.preventDefault();
        submitPost(`/community/groups/${group.slug}/posts`, {
            onSuccess: () => resetPost(),
        });
    };

    const handleCommentSubmit = (postId, e) => {
        e.preventDefault();
        const body = commentData[postId];
        if (!body || !body.trim()) return;

        router.post(`/community/groups/${group.slug}/posts/${postId}/comments`, { body }, {
            onSuccess: () => {
                setCommentData((prev) => ({ ...prev, [postId]: '' }));
            },
            preserveScroll: true,
        });
    };

    const handleUpdateGoal = (e) => {
        e.preventDefault();
        router.post(`/community/groups/${group.slug}/goal`, { progress: goalProgress }, {
            preserveScroll: true,
        });
    };

    return (
        <MainLayout>
            <Head title={`${group.name} — স্টাডি গ্রুপ`} />

            {/* Header Banner */}
            <div className="bg-[#102526] py-10 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <div className="flex items-center gap-2">
                                <Link href="/community/groups" className="text-xs text-[#FFF99A] hover:underline">
                                    ← সকল গ্রুপ
                                </Link>
                                <span className="text-white/40">•</span>
                                <span className="rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-white">
                                    {group.type === 'public' ? 'উন্মুক্ত' : group.type === 'private' ? 'প্রাইভেট' : 'কোর্স লিংকড'}
                                </span>
                            </div>
                            <h1 className="mt-2 text-2xl font-extrabold sm:text-3xl text-white">
                                {group.name}
                            </h1>
                            <p className="mt-2 max-w-2xl text-sm text-white/80">
                                {group.description || 'সহপাঠীদের সাথে নির্দিষ্ট দ্বীনি বিষয়ে নিয়মিত মুযাকারা ও অধ্যয়ন হালাকা।'}
                            </p>
                            <div className="mt-3 flex items-center gap-4 text-xs text-white/60">
                                <span>পরিচালক: <strong className="text-white">{group.creator?.name}</strong></span>
                                <span>সদস্য: <strong className="text-white">{group.members_count}</strong> জন</span>
                                {group.course && (
                                    <span>কোর্স: <strong className="text-[#FFF99A]">{group.course.title}</strong></span>
                                )}
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            {isMember ? (
                                <button
                                    onClick={handleCopyInvite}
                                    className="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/20 transition"
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    {copied ? 'লিংক কপি হয়েছে!' : 'ইনভাইট লিংক কপি'}
                                </button>
                            ) : (
                                <button
                                    onClick={handleJoin}
                                    className="rounded-xl bg-[#FFF99A] px-6 py-2.5 text-sm font-bold text-[#102526] shadow hover:bg-[#fff780] transition"
                                >
                                    গ্রুপে যুক্ত হোন
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Area */}
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left: Feed & Discussions (2 cols) */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Weekly Goal Card */}
                        {group.weekly_goal && (
                            <div className="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-6 shadow-sm">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">
                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 className="text-sm font-bold text-emerald-950">এই সপ্তাহের ইলমী লক্ষ্য</h3>
                                            <p className="text-xs text-emerald-800">{group.weekly_goal}</p>
                                        </div>
                                    </div>
                                    {userMembership?.weekly_goal_completed && (
                                        <span className="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">
                                            ✓ সম্পন্ন (+১০ পয়েন্ট)
                                        </span>
                                    )}
                                </div>

                                {isMember && (
                                    <form onSubmit={handleUpdateGoal} className="mt-4 pt-4 border-t border-emerald-200/60 flex items-center gap-4">
                                        <div className="flex-1">
                                            <div className="flex justify-between text-xs font-medium text-emerald-900 mb-1">
                                                <span>আপনার অগ্রগতি:</span>
                                                <span>{goalProgress}%</span>
                                            </div>
                                            <input
                                                type="range"
                                                min="0"
                                                max="100"
                                                step="10"
                                                value={goalProgress}
                                                onChange={(e) => setGoalProgress(parseInt(e.target.value))}
                                                className="w-full accent-emerald-600"
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            className="rounded-lg bg-emerald-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-900"
                                        >
                                            সংরক্ষণ
                                        </button>
                                    </form>
                                )}
                            </div>
                        )}

                        {/* Create Post in Group */}
                        {isMember ? (
                            <form onSubmit={handlePostSubmit} className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h3 className="text-sm font-bold text-gray-900 mb-2">গ্রুপে নতুন আলোচনা বা প্রশ্ন লিখুন</h3>
                                <textarea
                                    rows={3}
                                    value={postData.body}
                                    onChange={(e) => setPostData('body', e.target.value)}
                                    placeholder="আপনার চিন্তা, নোট বা প্রশ্ন সহপাঠীদের সাথে শেয়ার করুন..."
                                    className="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                                    required
                                />
                                <div className="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={postProcessing}
                                        className="rounded-xl bg-[#102526] px-5 py-2 text-xs font-bold text-white hover:bg-[#1A2E2F] disabled:opacity-50"
                                    >
                                        {postProcessing ? 'প্রকাশ হচ্ছে...' : 'পোস্ট করুন'}
                                    </button>
                                </div>
                            </form>
                        ) : (
                            <div className="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-center">
                                <p className="text-sm text-gray-600">আলোচনায় অংশ নিতে বা প্রশ্ন করতে গ্রুপে যুক্ত হোন</p>
                                <button
                                    onClick={handleJoin}
                                    className="mt-3 rounded-xl bg-[#102526] px-5 py-2 text-xs font-bold text-white hover:bg-[#1A2E2F]"
                                >
                                    গ্রুপে যুক্ত হোন
                                </button>
                            </div>
                        )}

                        {/* Posts Stream */}
                        <div className="space-y-4">
                            {posts.data && posts.data.length > 0 ? (
                                posts.data.map((post) => (
                                    <div key={post.id} className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                        <div className="flex items-center gap-3 mb-3">
                                            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#102526] text-xs font-bold text-white">
                                                {post.user?.name ? post.user.name.charAt(0) : 'U'}
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="text-sm font-bold text-gray-900">{post.user?.name}</span>
                                                    {post.user?.role === 'instructor' && (
                                                        <span className="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-800">উস্তায</span>
                                                    )}
                                                </div>
                                                <span className="text-xs text-gray-400">
                                                    {new Date(post.created_at).toLocaleDateString('bn-BD')}
                                                </span>
                                            </div>
                                        </div>

                                        <p className="text-sm text-gray-800 whitespace-pre-wrap">{post.body}</p>

                                        {/* Comments list */}
                                        {post.comments && post.comments.length > 0 && (
                                            <div className="mt-4 pt-3 border-t border-gray-100 space-y-2">
                                                {post.comments.map((comment) => (
                                                    <div key={comment.id} className="rounded-xl bg-gray-50 p-3 text-xs">
                                                        <div className="font-semibold text-gray-900">{comment.user?.name}</div>
                                                        <p className="mt-1 text-gray-700">{comment.body}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Add Comment */}
                                        {isMember && (
                                            <form
                                                onSubmit={(e) => handleCommentSubmit(post.id, e)}
                                                className="mt-3 flex gap-2"
                                            >
                                                <input
                                                    type="text"
                                                    value={commentData[post.id] || ''}
                                                    onChange={(e) => setCommentData({ ...commentData, [post.id]: e.target.value })}
                                                    placeholder="মন্তব্য লিখুন..."
                                                    className="flex-1 rounded-xl border-gray-200 bg-gray-50 text-xs focus:bg-white focus:border-[#102526] focus:ring-[#102526]"
                                                />
                                                <button
                                                    type="submit"
                                                    className="rounded-xl bg-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-300"
                                                >
                                                    উত্তর
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">
                                    এই স্টাডি গ্রুপে এখনো কোনো পোস্ট করা হয়নি। প্রথম আলোচনাটি শুরু করুন!
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: Members & Info (1 col) */}
                    <div className="space-y-6">
                        {/* Members Card */}
                        <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 className="text-sm font-bold text-gray-900 mb-3 flex items-center justify-between">
                                <span>সদস্যবৃন্দ ({group.members_count})</span>
                                <span className="text-xs text-gray-400">সর্বোচ্চ {group.max_members}</span>
                            </h3>

                            <div className="space-y-3">
                                {activeMembers.map((member) => (
                                    <div key={member.id} className="flex items-center justify-between text-xs">
                                        <div className="flex items-center gap-2">
                                            <div className="h-7 w-7 rounded-full bg-[#1A2E2F] text-white flex items-center justify-center font-bold text-[10px]">
                                                {member.user?.name ? member.user.name.charAt(0) : 'U'}
                                            </div>
                                            <div>
                                                <div className="font-semibold text-gray-900">{member.user?.name}</div>
                                                <div className="text-[10px] text-gray-400">{member.user?.reputation_level || 'তালিবুল ইলম'}</div>
                                            </div>
                                        </div>
                                        <span className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                                            member.role === 'owner'
                                                ? 'bg-amber-100 text-amber-800'
                                                : member.role === 'moderator'
                                                ? 'bg-purple-100 text-purple-800'
                                                : 'bg-gray-100 text-gray-600'
                                        }`}>
                                            {member.role === 'owner' ? 'পরিচালক' : member.role === 'moderator' ? 'মডারেটর' : 'সদস্য'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Rules Callout */}
                        <div className="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 text-xs text-amber-900 space-y-2">
                            <div className="font-bold flex items-center gap-1.5">
                                <span>⚠️ হালাকার আদব ও শিষ্টাচার</span>
                            </div>
                            <p>১. কুরআন-সুন্নাহ ও সালাফে সালেহীনের নীতিমালার আলোকে পরস্পরে সৌজন্যমূলক আচরণ বজায় রাখুন।</p>
                            <p>২. ফিতনা সৃষ্টিকারী ও অপ্রাসঙ্গিক বিষয় থেকে বিরত থাকুন।</p>
                            <p>৩. নিয়মিত উপস্থিত থেকে সাপ্তাহিক ইলমী লক্ষ্য অর্জনে সক্রিয় থাকুন।</p>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
