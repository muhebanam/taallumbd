import React from 'react';

export default function TeacherExpertiseMap({ expertiseMap = [], specialties = [], className = '' }) {
    const hasMap = Array.isArray(expertiseMap) && expertiseMap.length > 0;
    const hasSpecialties = Array.isArray(specialties) && specialties.length > 0;

    if (!hasMap && !hasSpecialties) return null;

    return (
        <div className={`rounded-2xl border border-slate-100 bg-white p-6 shadow-sm ${className}`}>
            <h2 className="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg className="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span>বিশেষজ্ঞতার ক্ষেত্র</span>
            </h2>

            {hasMap ? (
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    {expertiseMap.map((item, idx) => (
                        <div key={idx} className="rounded-xl bg-slate-50 border border-slate-100 p-4 transition-all duration-300 hover:shadow-sm">
                            <h3 className="font-bold text-slate-800 text-sm border-b border-slate-200/60 pb-1.5">{item.main_area}</h3>
                            <div className="mt-2.5 flex flex-wrap gap-1.5">
                                {(item.sub_areas ?? []).map((sub, sIdx) => (
                                    <span 
                                        key={sIdx}
                                        className="inline-block rounded-md bg-white border border-slate-200 px-2 py-0.5 text-xs text-slate-600 font-medium"
                                    >
                                        {sub}
                                    </span>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="mt-4 flex flex-wrap gap-2">
                    {specialties.map((spec, idx) => (
                        <span 
                            key={idx}
                            className="inline-block rounded-full bg-emerald-50 border border-emerald-100 px-3.5 py-1 text-sm font-semibold text-emerald-800"
                        >
                            {spec}
                        </span>
                    ))}
                </div>
            )}
        </div>
    );
}
