// Single source of truth for the main navigation.
// Course/article/fatwa categories come dynamically from navCategories (Inertia shared prop);
// static submenus (publications, about) live here.

export const coursesByCategory = {
    'aqeedah': [
        'আক্বাঈদ ও ঈমান কোর্স',
        'খতমে নবুওয়্যাত কোর্স'
    ],
    'quran': [
        'কুরআন শিক্ষা কোর্স',
        'তাজবীদ কোর্স',
        'কুরআনিক আরবি কোর্স',
        'তরজমাতুল কুরআন কোর্স'
    ],
    'tafsir': [
        'উসূলে তাফসীর কোর্স'
    ],
    'seerah': [
        'সীরাত কোর্স'
    ],
    'hadith': [
        'উসূলুল হাদীস কোর্স'
    ],
    'fiqh': [
        'উসূলুল ফিকহ কোর্স',
        'ইসলামি ফিকহ কোর্স',
        'কাওয়াঈদুল ফিকহ কোর্স',
        'ফিকহুল হালাল কোর্স',
        'ইসলামি অর্থনীতি কোর্স',
        'উলূমুল মিরাস কোর্স'
    ],
    'arabic-language': [
        'আরবী ভাষা শিক্ষা কোর্স'
    ],
    'miscellaneous': [
        'ইসলামী ভূগোল কোর্স',
        'স্কিল ডেভেলপমেন্ট',
        'অন্যান্য'
    ]
};

export const courseSubmenuLinks = Object.values(coursesByCategory).flat();

export const publicationsMenu = [
    {
        label: 'অডিও / ভিডিও', href: '/publications/audio-video',
        children: [
            { label: 'জুমার বয়ান', href: '/publications/audio-video/jumma-bayan' },
            { label: 'মাসিক তাফসীর', href: '/publications/audio-video/monthly-tafsir' },
            { label: 'দ্বীনি আলোচনা', href: '/publications/audio-video/islamic-discussion' },
            { label: 'ডকুমেন্টরি', href: '/publications/audio-video/documentary' },
        ],
    },
    {
        label: 'বুক স্টোর', href: '/publications/book-store',
        children: [
            { label: 'প্রকাশিত বই', href: '/publications/book-store/printed-books' },
            { label: 'ই-বুক', href: '/publications/book-store/ebooks' },
        ],
    },
    { label: 'দাওয়াহ পোস্টার', href: '/publications/dawah-poster' },
];

export const aboutMenu = [
    { label: 'আত-তাআল্লুম', href: '/about/taallum' },
    { label: 'লক্ষ্য উদ্দেশ্য', href: '/about/mission-vision' },
    { label: 'উপদেষ্টাবৃন্দ', href: '/about/advisors' },
    { label: 'পরিচালকবৃন্দ', href: '/about/directors' },
    { label: 'শিক্ষকমণ্ডলী', href: '/about/teachers' },
    { label: 'টেকনিক্যাল টিম', href: '/about/technical-team' },
];
