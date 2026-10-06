import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function LearningPathsIndex({ learningPaths = [] }) {
    return (
        <MainLayout>
            <Head title="লার্নিং পাথ (পরিকল্পিত পাঠপরিক্রমা) — আত-তাআল্লুম" />

            <div className="bg-[#102526] min-h-screen py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="max-w-3xl mb-12">
                        <span className="text-xs font-bold text-[#FFF99A] uppercase tracking-wider bg-[#FFF99A]/10 px-3 py-1 rounded-full border border-[#FFF99A]/20">
                            Roadmaps & Specializations
                        </span>
                        <h1 className="text-3xl sm:text-4xl font-extrabold text-white mt-3 mb-4 font-bangla">
                            ধারাবাহিক লার্নিং পাথ ও বিশেষজ্ঞ রোডম্যাপ
                        </h1>
                        <p className="text-gray-300 text-sm sm:text-base leading-relaxed">
                            কোনটির পর কোন কোর্স সম্পন্ন করবেন তা নিয়ে দ্বিধায় না ভুগে আলেমদের সুবিন্যস্ত পাঠপরিক্রমা অনুসরণ করুন। প্রতিটি লার্নিং পাথ শেষে অর্জন করুন স্পেশালাইজেশন সার্টিফিকেট।
                        </p>
                    </div>

                    {/* Paths Grid */}
                    {learningPaths.length === 0 ? (
                        <div className="text-center py-16 rounded-3xl bg-[#142C2E] border border-[#254244]">
                            <div className="text-4xl mb-3">🗺️</div>
                            <h3 className="text-lg font-bold text-white mb-2 font-bangla">শীঘ্রই নতুন লার্নিং পাথ আসছে</h3>
                            <p className="text-xs text-gray-400">বর্তমানে আমাদের সিলেবাস কমিটি নতুন পাঠপরিক্রমা প্রণয়ন করছেন।</p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {learningPaths.map((path) => (
                                <div
                                    key={path.id}
                                    className="flex flex-col justify-between rounded-3xl bg-[#142C2E] border border-[#254244] p-6 hover:border-[#FFF99A]/40 transition shadow-lg hover:shadow-black/50"
                                >
                                    <div>
                                        <div className="flex items-center justify-between mb-4">
                                            <span className="text-3xl">{path.icon || '🧭'}</span>
                                            <span className="text-[11px] font-bold px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-gray-300">
                                                {path.level || 'সকলের জন্য'}
                                            </span>
                                        </div>

                                        <h3 className="text-xl font-bold text-white mb-2 font-bangla">
                                            {path.title}
                                        </h3>
                                        <p className="text-xs text-gray-300 line-clamp-3 mb-6 leading-relaxed">
                                            {path.description}
                                        </p>
                                    </div>

                                    <div className="space-y-4 pt-4 border-t border-white/5">
                                        <div className="flex items-center justify-between text-xs text-gray-400">
                                            <span>📚 {path.courses_count || 0} টি কোর্স</span>
                                            <span>⏱️ {path.duration || 'স্ব-গতিশীল'}</span>
                                        </div>

                                        {/* Progress Bar if enrolled */}
                                        {path.is_enrolled && (
                                            <div>
                                                <div className="flex justify-between text-[11px] font-bold text-gray-300 mb-1">
                                                    <span>অগ্রগতি</span>
                                                    <span className="text-[#FFF99A]">{path.progress_percentage}%</span>
                                                </div>
                                                <div className="w-full h-2 rounded-full bg-black/40 overflow-hidden">
                                                    <div 
                                                        className="h-full bg-gradient-to-r from-emerald-400 to-[#FFF99A] transition-all"
                                                        style={{ width: `${path.progress_percentage}%` }}
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        <Link
                                            href={`/learning-paths/${path.slug}`}
                                            className="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs font-bold bg-[#1A383B] text-white hover:bg-[#FFF99A] hover:text-[#102526] transition font-bangla shadow"
                                        >
                                            {path.is_enrolled ? (path.is_completed ? 'সার্টিফিকেট ও রোডম্যাপ দেখুন' : 'পাথ চালিয়ে যান') : 'রোডম্যাপ দেখুন ও এনরোল করুন'} &rarr;
                                        </Link>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}
