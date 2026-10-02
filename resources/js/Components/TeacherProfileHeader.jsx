import React from 'react';
import TeacherVerifiedBadge from './TeacherVerifiedBadge';
import TeacherFollowButton from './TeacherFollowButton';
import TeacherSocialLinks from './TeacherSocialLinks';

export default function TeacherProfileHeader({ teacher, isFollowing, onTabChange, studentsCount = 0 }) {
    const rating = parseFloat(teacher.average_rating) || 0;

    return (
        <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            {/* Cover photo banner */}
            <div className="h-48 w-full bg-gradient-to-r from-[#102526] to-[#1A2E2F] relative sm:h-64">
                {(teacher.cover_photo_url || teacher.cover_photo) ? (
                    <img 
                        src={teacher.cover_photo_url || teacher.cover_photo} 
                        alt="" 
                        className="h-full w-full object-cover opacity-80"
                    />
                ) : (
                    <div className="absolute inset-0 opacity-15 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:20px_20px]"></div>
                )}
            </div>

            {/* Header Content */}
            <div className="relative px-6 pb-6 sm:px-8">
                {/* Avatar positioning */}
                <div className="flex flex-col sm:flex-row sm:items-end justify-between -mt-20 sm:-mt-24 mb-4 gap-4">
                    <img 
                        src={teacher.avatar_url || teacher.avatar} 
                        alt={teacher.name}
                        onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(teacher.name)}&background=102526&color=fff99a&size=150`;
                        }}
                        className="h-36 w-36 rounded-full border-4 border-white bg-slate-100 object-cover shadow-md sm:h-44 sm:w-44"
                    />
                    
                    {/* Action buttons on right */}
                    <div className="flex flex-wrap gap-2 pt-2">
                        <TeacherFollowButton 
                            teacherId={teacher.id} 
                            isFollowing={isFollowing}
                            allowFollow={teacher.allow_follow}
                        />
                        <button 
                            onClick={() => onTabChange('প্রশ্ন করুন')}
                            className="inline-flex items-center justify-center gap-1.5 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 hover:bg-slate-50 hover:shadow"
                        >
                            <svg className="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>প্রশ্ন করুন</span>
                        </button>
                    </div>
                </div>

                {/* Main Information */}
                <div className="mt-2">
                    <div className="flex items-center gap-2 flex-wrap">
                        <h1 className="text-2xl font-bold text-slate-900 sm:text-3xl">{teacher.name}</h1>
                        {teacher.is_verified && <TeacherVerifiedBadge className="scale-110" />}
                    </div>

                    {teacher.designation && (
                        <p className="text-base font-semibold text-emerald-800 mt-1">{teacher.designation}</p>
                    )}

                    {teacher.headline && (
                        <p className="text-lg font-medium text-slate-700 mt-2 leading-relaxed max-w-4xl">{teacher.headline}</p>
                    )}

                    {teacher.short_bio && (
                        <p className="text-sm text-slate-500 mt-3 leading-relaxed max-w-3xl">{teacher.short_bio}</p>
                    )}

                    {/* Metadata & Stats summary */}
                    <div className="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                        {teacher.location && (
                            <span className="flex items-center gap-1">
                                <svg className="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {teacher.location}
                            </span>
                        )}

                        <span className="font-semibold text-brand-deep">
                            {teacher.followers_count ?? 0} ফলোয়ার
                        </span>

                        {studentsCount > 0 && (
                            <span className="font-semibold text-brand-deep">
                                {studentsCount} শিক্ষার্থী
                            </span>
                        )}

                        {rating > 0 && (
                            <span className="flex items-center gap-1 font-semibold text-brand-deep">
                                <svg className="h-4 w-4 fill-current text-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                {rating} ({teacher.reviews_count || 0} রিভিউ)
                            </span>
                        )}
                    </div>

                    {/* Social Media Links */}
                    <div className="mt-5 border-t border-slate-100 pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <TeacherSocialLinks teacher={teacher} />
                        
                        {/* Quick scroll action buttons */}
                        <div className="flex gap-2">
                            {teacher.courses_count > 0 && (
                                <button
                                    onClick={() => onTabChange('কোর্সসমূহ')}
                                    className="rounded-xl bg-brand-cream/20 hover:bg-brand-cream/35 border border-brand-cream/30 text-[#102526] text-xs font-bold px-4 py-2 transition-all duration-300"
                                >
                                    কোর্স দেখুন ({teacher.courses_count})
                                </button>
                            )}
                            {teacher.articles_count > 0 && (
                                <button
                                    onClick={() => onTabChange('প্রবন্ধ')}
                                    className="rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold px-4 py-2 transition-all duration-300"
                                >
                                    প্রবন্ধ পড়ুন ({teacher.articles_count})
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
