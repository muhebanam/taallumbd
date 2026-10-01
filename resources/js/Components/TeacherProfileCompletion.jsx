import React from 'react';

export default function TeacherProfileCompletion({ percentage = 0, suggestions = [], className = '' }) {
    return (
        <div className={`rounded-2xl border border-slate-100 bg-white p-6 shadow-sm ${className}`}>
            <h3 className="text-sm font-bold text-slate-800 flex items-center justify-between">
                <span>প্রোফাইল সম্পন্ন হয়েছে:</span>
                <span className="text-brand-deep font-extrabold text-base">{percentage}%</span>
            </h3>

            {/* Progress bar */}
            <div className="mt-3 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                <div 
                    className="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 transition-all duration-500" 
                    style={{ width: `${percentage}%` }}
                ></div>
            </div>

            {/* Suggestions */}
            {suggestions.length > 0 && (
                <div className="mt-5">
                    <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider">প্রোফাইল উন্নত করার পরামর্শ:</h4>
                    <ul className="mt-2.5 space-y-2">
                        {suggestions.map((suggestion, idx) => (
                            <li key={idx} className="flex items-start gap-2 text-xs text-slate-600">
                                <svg className="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{suggestion}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
