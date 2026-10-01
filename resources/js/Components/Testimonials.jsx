import React from 'react';

const TESTIMONIALS = [
    {
        name: 'মুহাম্মদ তানভীর আহমেদ',
        role: 'শিক্ষার্থী, আল-কুরআন ও তাজবীদ কোর্স',
        location: 'ঢাকা',
        quote: 'মাখরাজ ও সিফাত এত সহজে এবং সুস্পষ্ট ভিডিও বিশ্লেষণের মাধ্যমে শেখা যাবে আগে কল্পনাও করিনি। উস্তাযের প্রতিটি ভুল ধরিয়ে দেওয়ার আন্তরিকতা অসাধারণ।',
        rating: 5,
        avatar: 'https://ui-avatars.com/api/?name=Tanvir+Ahmed&background=1A2E2F&color=FFF99A',
    },
    {
        name: 'আব্দুর রহমান শাকিল',
        role: 'শিক্ষার্থী, ফিকহুস সালাত ও তাহাড়াত',
        location: 'চট্টগ্রাম',
        quote: 'দৈনন্দিন জীবনে যেসব সূক্ষ্ম মাসআলা নিয়ে দ্বিধাদ্বন্দ্বে থাকতাম, নির্ভরযোগ্য কিতাবের উদ্ধৃতিসহ সেগুলো পরিষ্কার হয়ে গেছে। আলহামদুলিল্লাহ!',
        rating: 5,
        avatar: 'https://ui-avatars.com/api/?name=Abdur+Rahman&background=102526&color=FFF99A',
    },
    {
        name: 'ফাতিমা তুয যোহরা',
        role: 'শিক্ষার্থী, আরবি ভাষা ও ব্যাকরণ (লেভেল ১)',
        location: 'সিলেট',
        quote: 'ঘরে বসেই মানসম্মত কারিকুলামে আরবি ব্যাকরণ এত সুন্দর গুছিয়ে শেখানো সত্যিই প্রশংসনীয়। লেকচার শিট ও কুইজগুলো খুবই কার্যকরী।',
        rating: 5,
        avatar: 'https://ui-avatars.com/api/?name=Fatima+Zohra&background=1A2E2F&color=FFF99A',
    },
];

export default function Testimonials() {
    return (
        <section className="py-16 sm:py-24 bg-white">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="text-center max-w-2xl mx-auto">
                    <span className="inline-block rounded-full bg-[#1A2E2F]/10 px-4 py-1 text-xs font-bold text-[#1A2E2F] uppercase tracking-wider">
                        শিক্ষার্থীদের অভিজ্ঞতা
                    </span>
                    <h2 className="mt-3 text-3xl font-extrabold text-[#142425] sm:text-4xl font-bangla">
                        শিক্ষার্থীদের সন্তুষ্টি ও অনুভূতির কথা
                    </h2>
                    <p className="mt-3 text-base text-slate-600 font-bangla">
                        হাজারো শিক্ষার্থী আত-তাআল্লুমের সাথে বিশুদ্ধ দ্বীন শেখার পথচলায় যুক্ত আছেন।
                    </p>
                </div>

                {/* Testimonial Cards Grid */}
                <div className="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                    {TESTIMONIALS.map((t, idx) => (
                        <div
                            key={idx}
                            className="relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-[#F8FAF8] p-8 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-[#1A2E2F]/30"
                        >
                            <div>
                                {/* Stars */}
                                <div className="flex items-center text-amber-500 mb-4">
                                    {[...Array(t.rating)].map((_, i) => (
                                        <svg key={i} className="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    ))}
                                </div>

                                <p className="text-sm text-slate-700 leading-relaxed italic">
                                    "{t.quote}"
                                </p>
                            </div>

                            {/* Author */}
                            <div className="mt-8 pt-4 border-t border-slate-200/70 flex items-center gap-3">
                                <img
                                    src={t.avatar}
                                    alt={t.name}
                                    className="h-11 w-11 rounded-full object-cover border border-slate-300"
                                />
                                <div>
                                    <h4 className="text-sm font-bold text-[#142425]">{t.name}</h4>
                                    <p className="text-xs text-slate-500">{t.role} • {t.location}</p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
