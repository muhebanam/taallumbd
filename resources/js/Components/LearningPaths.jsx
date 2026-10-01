import React from 'react';
import { Link } from '@inertiajs/react';

const PATHS = [
    {
        id: 'beginner',
        level: 'প্রাথমিক স্তর',
        badgeColor: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        title: 'বুনিয়াদী দ্বীনি শিক্ষা পাথ',
        subtitle: 'প্রতিটি মুসলিম নর-নারীর ওপর ফরজ পরিমাণ জ্ঞানের সুসংগঠিত কোর্স প্যাকেজ।',
        duration: '৩–৬ মাস',
        icon: '🌱',
        modules: [
            'সহীহ মাখরাজ ও বিশুদ্ধ কুরআন তিলাওয়াত',
            'ঈমানের মৌলিক ৬টি স্তম্ভ ও সঠিক আকীদাহ',
            'তাহাড়াত, সালাত ও দৈনন্দিন জরুরি মাসায়েল',
            'মাসনূন দু‘আ ও দৈনন্দিন যিকির-আযকার',
        ],
        href: '/courses?level=beginner',
    },
    {
        id: 'intermediate',
        level: 'মধ্যবর্তী স্তর',
        badgeColor: 'bg-amber-100 text-amber-800 border-amber-200',
        title: 'কুরআন, হাদীস ও ফিকহ পাঠ',
        subtitle: 'ইসলামের মৌলিক শাস্ত্রসমূহের গভীরে প্রবেশ এবং আরবী ভাষার প্রাথমিক বুনিয়াদ।',
        duration: '৬–১২ মাস',
        icon: '📖',
        modules: [
            'নির্বাচিত সূরাসমূহের অর্থ ও সংক্ষিপ্ত তাফসীর',
            'রিয়াযুস সলেহীন ও চল্লিশ হাদীসের গভীর বিশ্লেষণ',
            'মু‘আমালাত: ব্যবসা-বাণিজ্য, লেনদেন ও হালাল জীবিকা',
            'আরবী ভাষা ও ব্যাকরণ (নাহু-সরফের সূচনা)',
        ],
        href: '/courses?level=intermediate',
    },
    {
        id: 'advanced',
        level: 'উচ্চতর স্তর',
        badgeColor: 'bg-purple-100 text-purple-800 border-purple-200',
        title: 'উসূলে শরীয়াহ ও ফিকহী গবেষণা',
        subtitle: 'আলেমে দ্বীন ও গবেষকদের জন্য উসূলে ফিকহ, উলূমুল হাদীস ও সমসাময়িক ফাতাওয়া পাঠ।',
        duration: '১–২ বছর',
        icon: '🏛️',
        modules: [
            'উসূলে ফিকহ ও কাওয়াইদে ফিকহিয়্যাহ',
            'উলূমুল হাদীস ও রাবী তাহকীক নীতি',
            'ইসলামী ব্যাংকিং, অর্থায়ন ও ডিজিটাল লেনদেন ফিকহ',
            'সমসাময়িক জটিল সমস্যার ফাতাওয়া গবেষণা পদ্ধতি',
        ],
        href: '/courses?level=advanced',
    },
];

export default function LearningPaths() {
    return (
        <section className="py-16 sm:py-24 bg-[#F8FAF8]">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Section Header */}
                <div className="text-center max-w-3xl mx-auto">
                    <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                        পরিকল্পিত সিলেবাস
                    </span>
                    <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                        আপনার উপযুক্ত লার্নিং পাথ বেছে নিন
                    </h2>
                    <p className="mt-4 text-base text-slate-600 font-bangla">
                        শিক্ষানবিস থেকে শুরু করে গভীর তালিবে ইলম পর্যন্ত—স্তরভিত্তিক সুনির্দিষ্ট রোডম্যাপ যা আপনাকে নিয়ে যাবে নিয়মতান্ত্রিক জ্ঞানার্জনের লক্ষ্যে।
                    </p>
                </div>

                {/* Path Cards Grid */}
                <div className="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {PATHS.map((path) => (
                        <div
                            key={path.id}
                            className="group relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:border-[#1A2E2F]/30"
                        >
                            <div>
                                {/* Header with Icon and Level Badge */}
                                <div className="flex items-center justify-between">
                                    <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1A2E2F]/5 text-2xl group-hover:bg-[#1A2E2F] group-hover:text-white transition-colors duration-300">
                                        {path.icon}
                                    </span>
                                    <span className={`rounded-full border px-3 py-1 text-xs font-bold ${path.badgeColor}`}>
                                        {path.level}
                                    </span>
                                </div>

                                <h3 className="mt-6 text-xl font-bold text-[#142425] group-hover:text-[#1A2E2F] transition-colors">
                                    {path.title}
                                </h3>
                                <p className="mt-2 text-sm text-slate-600 leading-relaxed">
                                    {path.subtitle}
                                </p>

                                {/* Duration & Modules List */}
                                <div className="mt-6 border-t border-slate-100 pt-5">
                                    <div className="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3">
                                        <span>⏱ সময়কাল: {path.duration}</span>
                                    </div>

                                    <ul className="space-y-2.5">
                                        {path.modules.map((mod, idx) => (
                                            <li key={idx} className="flex items-start gap-2.5 text-xs text-slate-700">
                                                <svg className="h-4 w-4 shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>{mod}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>

                            {/* Action Button */}
                            <div className="mt-8 pt-4">
                                <Link
                                    href={path.href}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-50 border border-slate-200 py-3 text-sm font-bold text-[#142425] transition-all duration-200 group-hover:bg-[#1A2E2F] group-hover:text-white group-hover:border-[#1A2E2F]"
                                >
                                    <span>এই পাথের কোর্সগুলো দেখুন</span>
                                    <svg className="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
