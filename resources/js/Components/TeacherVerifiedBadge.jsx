import React from 'react';

export default function TeacherVerifiedBadge({ className = '' }) {
    return (
        <span 
            className={`inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-300 shadow-xs ${className}`}
            title="আত-তাআল্লুম কর্তৃক যাচাইকৃত শিক্ষক"
        >
            <svg 
                className="h-3.5 w-3.5 fill-current text-emerald-600 shrink-0" 
                viewBox="0 0 20 20" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
            </svg>
            <span className="leading-none tracking-tight">যাচাইকৃত</span>
        </span>
    );
}
