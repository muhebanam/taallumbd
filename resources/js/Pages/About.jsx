import { Head } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

const SECTIONS = {
    'taallum': { title: 'আত-তাআল্লুম', body: 'আত-তাআল্লুম একটি অনলাইন দ্বীনি শিক্ষা প্ল্যাটফর্ম, যার লক্ষ্য কুরআন-সুন্নাহর নির্ভরযোগ্য ইলমকে সবার কাছে সহজবোধ্য বাংলায় পৌঁছে দেওয়া।' },
    'mission-vision': { title: 'লক্ষ্য উদ্দেশ্য', body: 'আমাদের লক্ষ্য: প্রামাণিক দ্বীনি শিক্ষাকে প্রযুক্তির মাধ্যমে সহজলভ্য করা; প্রতিটি মুসলিমের আমলী জীবনে সহীহ ইলমের আলো পৌঁছে দেওয়া।' },
    'advisors': { title: 'উপদেষ্টাবৃন্দ', body: 'আমাদের উপদেষ্টা পরিষদে রয়েছেন দেশের শীর্ষস্থানীয় উলামায়ে কেরাম। বিস্তারিত শীঘ্রই যুক্ত হবে।' },
    'directors': { title: 'পরিচালকবৃন্দ', body: 'পরিচালনা পর্ষদের তথ্য শীঘ্রই যুক্ত হবে, ইনশাআল্লাহ।' },
    'technical-team': { title: 'টেকনিক্যাল টিম', body: 'প্ল্যাটফর্মের ডেভেলপমেন্ট ও রক্ষণাবেক্ষণে নিয়োজিত টিমের পরিচিতি শীঘ্রই যুক্ত হবে।' },
};

export default function About({ section = 'taallum' }) {
    const content = SECTIONS[section] ?? SECTIONS['taallum'];
    return (
        <AppLayout>
            <Head title={content.title} />
            <div className="mx-auto max-w-3xl px-4 py-12">
                <h1 className="text-3xl font-bold text-brand-deep">{content.title}</h1>
                <p className="mt-6 leading-relaxed text-brand-text/85">{content.body}</p>
            </div>
        </AppLayout>
    );
}
