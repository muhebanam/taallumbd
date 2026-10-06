import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function LearningPathShow({ learningPath, progress = {} }) {
    const handleEnroll = () => {
        router.post(`/learning-paths/${learningPath.slug}/enroll`);
    };

    const handleClaimCertificate = () => {
        router.post(`/learning-paths/${learningPath.slug}/claim-certificate`);
    };

    const steps = progress.steps || [];

    return (
        <MainLayout>
            <Head title={`${learningPath.title} — লার্নিং পাথ রোডম্যাপ — আত-তাআল্লুম`} />

            <div className="bg-[#102526] min-h-screen py-10">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Back link */}
                    <div className="mb-6">
                        <Link href="/learning-paths" className="text-xs text-gray-400 hover:text-[#FFF99A] transition flex items-center gap-1.5">
                            &larr; সকল লার্নিং পাথে ফিরুন
                        </Link>
                    </div>

                    {/* Path Hero Banner */}
                    <div className="rounded-3xl bg-[#142C2E] border border-[#254244] p-6 sm:p-8 mb-10 shadow-xl">
                        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                            <div className="space-y-3 max-w-2xl">
                                <div className="flex items-center gap-3">
                                    <span className="text-4xl">{learningPath.icon || '🧭'}</span>
                                    <div>
                                        <span className="text-xs font-bold text-[#FFF99A] uppercase tracking-wider">
                                            লার্নিং পাথ রোডম্যাপ
                                        </span>
                                        <h1 className="text-2xl sm:text-3xl font-extrabold text-white font-bangla">
                                            {learningPath.title}
                                        </h1>
                                    </div>
                                </div>
                                <p className="text-sm text-gray-300 leading-relaxed">
                                    {learningPath.description}
                                </p>
                                <div className="flex flex-wrap items-center gap-4 text-xs text-gray-400 pt-2">
                                    <span>🎯 লেভেল: <strong className="text-white">{learningPath.level || 'সকলের জন্য'}</strong></span>
                                    <span>⏱️ আনুমানিক সময়: <strong className="text-white">{learningPath.duration || 'স্ব-গতিশীল'}</strong></span>
                                    <span>📚 মোট কোর্স: <strong className="text-white">{progress.total_courses || 0} টি</strong></span>
                                </div>
                            </div>

                            {/* Actions / CTA Card */}
                            <div className="w-full sm:w-auto shrink-0 bg-[#1A383B] p-5 rounded-2xl border border-white/10 text-center space-y-3 min-w-[220px]">
                                <div className="text-xs text-gray-300">
                                    অগ্রগতি: <strong className="text-[#FFF99A] text-sm">{progress.progress_percentage || 0}%</strong>
                                </div>
                                <div className="w-full h-2 rounded-full bg-black/40 overflow-hidden">
                                    <div
                                        className="h-full bg-gradient-to-r from-emerald-400 to-[#FFF99A] transition-all"
                                        style={{ width: `${progress.progress_percentage || 0}%` }}
                                    />
                                </div>
                                <div className="text-[11px] text-gray-400">
                                    {progress.completed_courses || 0} / {progress.total_courses || 0} কোর্স সম্পন্ন
                                </div>

                                {!progress.is_enrolled ? (
                                    <button
                                        onClick={handleEnroll}
                                        className="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-[#FFF99A] text-[#102526] hover:bg-[#fff780] transition shadow font-bangla"
                                    >
                                        পাথে এনরোল করুন
                                    </button>
                                ) : progress.has_certificate ? (
                                    <Link
                                        href={`/verify/${progress.certificate?.uuid || progress.certificate?.certificate_no}`}
                                        className="inline-block w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-emerald-500 text-white hover:bg-emerald-600 transition shadow font-bangla"
                                    >
                                        🏆 সার্টিফিকেট দেখুন
                                    </Link>
                                ) : progress.can_claim_certificate ? (
                                    <button
                                        onClick={handleClaimCertificate}
                                        className="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-400 to-yellow-300 text-black animate-pulse hover:brightness-110 transition shadow font-bangla"
                                    >
                                        🎓 সার্টিফিকেট দাবি করুন!
                                    </button>
                                ) : (
                                    <span className="inline-block text-xs font-bold text-emerald-400">
                                        ✓ আপনি এই পাথে যুক্ত
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Step-by-Step Curriculum Roadmap */}
                    <div className="space-y-6">
                        <div className="border-b border-[#254244] pb-3">
                            <h2 className="text-xl font-bold text-white font-bangla">
                                পাঠপরিক্রমা ও কোর্স ক্রম
                            </h2>
                            <p className="text-xs text-gray-400 mt-1">
                                সর্বোচ্চ ফলাফলের জন্য ক্রমানুসারে প্রতিটি কোর্স সম্পন্ন করুন।
                            </p>
                        </div>

                        <div className="space-y-4">
                            {steps.map((step, index) => {
                                const course = step.course;
                                return (
                                    <div
                                        key={course.id}
                                        className={`relative flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border transition ${
                                            step.is_completed
                                                ? 'bg-[#142C2E]/90 border-emerald-500/40 ring-1 ring-emerald-500/20'
                                                : step.is_unlocked
                                                ? 'bg-[#142C2E] border-[#254244] hover:border-[#FFF99A]/40'
                                                : 'bg-[#102526]/80 border-dashed border-white/10 opacity-70'
                                        }`}
                                    >
                                        <div className="flex items-start gap-4">
                                            {/* Step Number Circle */}
                                            <div className={`h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 ${
                                                step.is_completed
                                                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                                    : step.is_unlocked
                                                    ? 'bg-[#FFF99A]/20 text-[#FFF99A] border border-[#FFF99A]/40'
                                                    : 'bg-white/5 text-gray-400 border border-white/10'
                                            }`}>
                                                {step.is_completed ? '✓' : index + 1}
                                            </div>

                                            {/* Course Details */}
                                            <div>
                                                <div className="flex items-center gap-2 mb-1">
                                                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                                        step.is_completed
                                                            ? 'bg-emerald-500/20 text-emerald-300'
                                                            : step.is_unlocked
                                                            ? 'bg-[#FFF99A]/20 text-[#FFF99A]'
                                                            : 'bg-gray-500/20 text-gray-400'
                                                    }`}>
                                                        {step.is_completed ? 'সম্পন্ন' : step.is_unlocked ? 'উন্মুক্ত' : 'লকড (পূর্বশর্ত প্রয়োজন)'}
                                                    </span>
                                                    {course.instructor?.name && (
                                                        <span className="text-xs text-gray-400">
                                                            উস্তায: {course.instructor.name}
                                                        </span>
                                                    )}
                                                </div>

                                                <h3 className="text-base font-bold text-white font-bangla">
                                                    {course.title}
                                                </h3>
                                                <p className="text-xs text-gray-300 line-clamp-1 mt-0.5">
                                                    {course.short_description}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Action Button */}
                                        <div className="shrink-0 flex items-center gap-3">
                                            {step.is_unlocked ? (
                                                <Link
                                                    href={`/courses/${course.slug || course.id}`}
                                                    className="py-2 px-4 rounded-xl text-xs font-bold bg-[#1A383B] text-white hover:bg-[#FFF99A] hover:text-[#102526] transition font-bangla"
                                                >
                                                    {step.is_completed ? 'রিভিউ করুন' : 'কোর্সে প্রবেশ করুন'} &rarr;
                                                </Link>
                                            ) : (
                                                <span className="text-xs text-gray-500 flex items-center gap-1">
                                                    🔒 আগের কোর্সটি সম্পন্ন করুন
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
