import React, { useState } from 'react';
import { router, usePage } from '@inertiajs/react';

export default function TeacherFollowButton({ teacherId, isFollowing: initialFollowing, allowFollow = true, className = '' }) {
    const { auth } = usePage().props;
    const [isFollowing, setIsFollowing] = useState(initialFollowing);
    const [processing, setProcessing] = useState(false);

    if (!allowFollow) return null;

    const handleFollowToggle = (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!auth.user) {
            router.get('/login');
            return;
        }

        setProcessing(true);
        const url = isFollowing 
            ? `/teachers/${teacherId}/unfollow` 
            : `/teachers/${teacherId}/follow`;

        router.post(url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                setIsFollowing(!isFollowing);
                setProcessing(false);
            },
            onError: () => {
                setProcessing(false);
            }
        });
    };

    return (
        <button
            onClick={handleFollowToggle}
            disabled={processing}
            className={`inline-flex items-center justify-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition-all duration-300 ${
                isFollowing
                    ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300'
                    : 'bg-[#102526] hover:bg-[#1A2E2F] text-white border border-[#102526] shadow-sm hover:shadow-md'
            } disabled:opacity-50 disabled:cursor-not-allowed ${className}`}
        >
            {isFollowing ? (
                <>
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>ফলো করছেন</span>
                </>
            ) : (
                <>
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>ফলো করুন</span>
                </>
            )}
        </button>
    );
}
