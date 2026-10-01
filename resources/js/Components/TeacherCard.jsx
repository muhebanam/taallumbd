import React from 'react';
import { Link } from '@inertiajs/react';
import TeacherVerifiedBadge from './TeacherVerifiedBadge';
import TeacherFollowButton from './TeacherFollowButton';

export default function TeacherCard({ teacher }) {
    const specialties = teacher.specialties ?? [];
    const displaySpecialties = specialties.slice(0, 3);
    const extraSpecialties = specialties.length - displaySpecialties.length;
    const rating = parseFloat(teacher.average_rating) || 5.0;

    return (
        <div className="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-[#1A2E2F]/40 hover:shadow-xl">
            <div>
                {/* Header Cover Bar */}
                <div className="relative h-24 w-full bg-gradient-to-r from-[#102526] via-[#1A2E2F] to-[#102526] overflow-hidden">
                    {teacher.cover_photo_url ? (
                        <img 
                            src={teacher.cover_photo_url} 
                            alt="" 
                            className="h-full w-full object-cover opacity-60"
                        />
                    ) : (
                        <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    )}
                </div>

                {/* Profile Avatar overlapping */}
                <div className="relative -mt-10 px-5 flex items-end justify-between">
                    <Link href={`/teachers/${teacher.slug}`} className="block transition-transform duration-300 hover:scale-105">
                        <img 
                            src={teacher.avatar_url} 
                            alt={teacher.name}
                            onError={(e) => {
                                e.target.onerror = null;
                                e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(teacher.name)}&background=102526&color=fff99a&size=150`;
                            }}
                            className="h-20 w-20 rounded-full border-4 border-white bg-slate-100 object-cover shadow-md"
                        />
                    </Link>

                    <div className="pb-1">
                        <TeacherFollowButton 
                            teacherId={teacher.id} 
                            isFollowing={teacher.is_following}
                            allowFollow={teacher.allow_follow}
                        />
                    </div>
                </div>

                {/* Content */}
                <div className="px-5 pt-3">
                    <div className="flex items-center gap-1.5 flex-wrap">
                        <Link 
                            href={`/teachers/${teacher.slug}`} 
                            className="text-base font-bold text-[#142425] hover:text-[#1A2E2F] transition-colors font-bangla"
                        >
                            {teacher.name}
                        </Link>
                        {teacher.is_verified && <TeacherVerifiedBadge />}
                    </div>

                    {teacher.designation && (
                        <p className="text-xs font-semibold text-emerald-800 mt-0.5">{teacher.designation}</p>
                    )}

                    {teacher.headline && (
                        <p className="text-xs text-slate-600 mt-1 line-clamp-1">{teacher.headline}</p>
                    )}

                    {teacher.short_bio && (
                        <p className="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">{teacher.short_bio}</p>
                    )}

                    {/* Specialties */}
                    {specialties.length > 0 && (
                        <div className="mt-3 flex flex-wrap gap-1">
                            {displaySpecialties.map((spec, i) => (
                                <span 
                                    key={i}
                                    className="inline-block rounded-md bg-[#F8FAF8] border border-slate-200 px-2 py-0.5 text-[10px] font-medium text-slate-700"
                                >
                                    {spec}
                                </span>
                            ))}
                            {extraSpecialties > 0 && (
                                <span className="inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    +{extraSpecialties} আরও
                                </span>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Bottom Stats and CTA */}
            <div className="p-5 pt-3">
                {/* Stats Grid */}
                <div className="grid grid-cols-4 gap-1 border-t border-slate-100 py-3 text-center text-[10px] text-slate-500">
                    <div>
                        <span className="block font-bold text-slate-800 text-xs">{teacher.courses_count ?? 0}</span>
                        কোর্স
                    </div>
                    <div>
                        <span className="block font-bold text-slate-800 text-xs">{teacher.articles_count ?? 0}</span>
                        প্রবন্ধ
                    </div>
                    <div>
                        <span className="block font-bold text-slate-800 text-xs">{teacher.fatawa_count ?? 0}</span>
                        ফাতাওয়া
                    </div>
                    <div>
                        <span className="block font-bold text-slate-800 text-xs">{teacher.followers_count ?? 0}</span>
                        অনুসারী
                    </div>
                </div>

                <Link 
                    href={`/teachers/${teacher.slug}`}
                    className="flex w-full items-center justify-center rounded-xl bg-slate-100 py-2.5 text-xs font-bold text-[#142425] transition-colors duration-200 hover:bg-[#1A2E2F] hover:text-white"
                >
                    একাডেমিক প্রোফাইল দেখুন
                </Link>
            </div>
        </div>
    );
}
