import React from 'react';

export default function TeacherTimeline({ items = [], type = 'education', className = '' }) {
    if (!Array.isArray(items) || items.length === 0) return null;

    const title = type === 'education' ? 'শিক্ষাগত যোগ্যতা' : 'অভিজ্ঞতা';
    const icon = type === 'education' ? (
        <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
        </svg>
    ) : (
        <svg className="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    );

    return (
        <div className={`rounded-2xl border border-slate-100 bg-white p-6 shadow-sm ${className}`}>
            <h2 className="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                {icon}
                <span>{title}</span>
            </h2>

            <div className="relative mt-6 pl-4 border-l border-slate-200 ml-3 space-y-6">
                {items.map((item, idx) => {
                    const isEducation = type === 'education';
                    const mainTitle = isEducation ? item.degree_title : item.title;
                    const subTitle = isEducation ? item.institution : item.organization;
                    const timeRange = isEducation 
                        ? item.year 
                        : `${item.start_year} - ${item.currently_working ? 'বর্তমান' : (item.end_year ?? '')}`;

                    return (
                        <div key={idx} className="relative">
                            {/* Timeline bullet node */}
                            <div className="absolute -left-[25px] top-1.5 h-4 w-4 rounded-full border-2 border-white bg-slate-300 shadow-sm ring-4 ring-white group-hover:bg-brand-deep">
                                <div className={`h-2 w-2 rounded-full m-auto mt-[2px] ${isEducation ? 'bg-indigo-600' : 'bg-blue-600'}`}></div>
                            </div>

                            <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-1 pl-4">
                                <div>
                                    <h3 className="font-bold text-slate-800 text-sm">{mainTitle}</h3>
                                    <p className="text-xs text-slate-500 font-semibold mt-0.5">{subTitle} {isEducation && item.department && `(${item.department} বিভাগ)`}</p>
                                    {item.description && (
                                        <p className="text-xs text-slate-500 mt-2 leading-relaxed whitespace-pre-line">{item.description}</p>
                                    )}
                                </div>
                                {timeRange && (
                                    <span className="shrink-0 text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded-full px-2.5 py-0.5 h-fit sm:mt-0.5">
                                        {timeRange}
                                    </span>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
