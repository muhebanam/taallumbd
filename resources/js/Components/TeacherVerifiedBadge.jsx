import React from 'react';

export default function TeacherVerifiedBadge({ className = '' }) {
    return (
        <span 
            className={`inline-flex items-center gap-1 rounded-full bg-brand-cream/20 px-2 py-0.5 text-xs font-semibold text-brand-cream border border-brand-cream/30 ${className}`}
            title="আত-তাআল্লুম কর্তৃক যাচাইকৃত শিক্ষক"
        >
            <svg 
                className="h-3 w-3 fill-current text-brand-cream" 
                viewBox="0 0 24 24" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
            <span>যাচাইকৃত</span>
        </span>
    );
}
