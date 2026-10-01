import React from 'react';

export default function TeacherSocialLinks({ teacher, className = '' }) {
    const links = [
        { key: 'website', url: teacher.website, icon: (
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
        ), color: 'hover:text-blue-500 hover:bg-blue-50', label: 'ওয়েবসাইট' },
        { key: 'facebook', url: teacher.facebook_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z" />
            </svg>
        ), color: 'hover:text-[#1877F2] hover:bg-blue-50', label: 'ফেসবুক' },
        { key: 'youtube', url: teacher.youtube_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.498 6.163a3.003 3.003 0 00-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.507a3.003 3.003 0 00-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 002.11 2.11c1.87.507 9.388.507 9.388.507s7.518 0 9.388-.507a3.003 3.003 0 002.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
            </svg>
        ), color: 'hover:text-[#FF0000] hover:bg-red-50', label: 'ইউটিউব' },
        { key: 'linkedin', url: teacher.linkedin_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
            </svg>
        ), color: 'hover:text-[#0077B5] hover:bg-blue-50', label: 'লিংকডইন' },
        { key: 'twitter', url: teacher.twitter_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
            </svg>
        ), color: 'hover:text-black hover:bg-slate-100', label: 'টুইটার / এক্স' },
        { key: 'instagram', url: teacher.instagram_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
            </svg>
        ), color: 'hover:text-[#E1306C] hover:bg-pink-50', label: 'ইনস্টাগ্রাম' },
        { key: 'telegram', url: teacher.telegram_url, icon: (
            <svg className="h-5 w-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.944 0C5.348 0 0 5.348 0 12s5.348 12 11.944 12c6.62 0 12.056-5.348 12.056-12S18.564 0 11.944 0zm5.556 8.306l-1.924 9.062c-.143.639-.522.797-1.057.492l-2.935-2.163-1.414 1.362c-.156.156-.287.287-.588.287l.21-2.981 5.43-4.904c.235-.211-.051-.328-.365-.119l-6.71 4.223-2.89-.904c-.628-.197-.639-.628.13-.931l11.29-4.354c.522-.197.978.118.803.931z" />
            </svg>
        ), color: 'hover:text-[#229ED9] hover:bg-blue-50', label: 'টেলিগ্রাম' }
    ];

    const activeLinks = links.filter(l => l.url);

    if (activeLinks.length === 0) return null;

    return (
        <div className={`flex flex-wrap gap-2 ${className}`}>
            {activeLinks.map((link) => (
                <a
                    key={link.key}
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={`inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition-all duration-300 border border-slate-200 ${link.color}`}
                    title={link.label}
                >
                    {link.icon}
                </a>
            ))}
        </div>
    );
}
