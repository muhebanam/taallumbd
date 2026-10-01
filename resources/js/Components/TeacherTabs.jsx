import React from 'react';

export default function TeacherTabs({ activeTab, setActiveTab, teacher }) {
    const tabs = [
        { name: 'পরিচিতি', count: null },
        { name: 'কোর্সসমূহ', count: teacher.courses_count },
        { name: 'প্রবন্ধ', count: teacher.articles_count },
        { name: 'ফাতাওয়া', count: teacher.fatawa_count },
        { name: 'প্রকাশনা / বই', count: teacher.publications_count },
        { name: 'রিভিউ', count: teacher.reviews_count },
        { name: 'প্রশ্ন করুন', count: null },
    ];

    return (
        <div className="border-b border-slate-200 bg-white px-4 py-2 sm:px-6">
            <nav className="flex space-x-6 overflow-x-auto scrollbar-none whitespace-nowrap" aria-label="Tabs">
                {tabs.map((tab) => {
                    const isActive = activeTab === tab.name;
                    return (
                        <button
                            key={tab.name}
                            onClick={() => setActiveTab(tab.name)}
                            className={`relative py-3 px-1 text-sm font-semibold border-b-2 transition-all duration-300 ${
                                isActive
                                    ? 'border-[#102526] text-[#102526]'
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                            }`}
                        >
                            <span className="flex items-center gap-1.5">
                                {tab.name}
                                {tab.count !== null && tab.count !== undefined && (
                                    <span className={`inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold ${
                                        isActive 
                                            ? 'bg-[#102526] text-[#FFF99A]' 
                                            : 'bg-slate-100 text-slate-500'
                                    }`}>
                                        {tab.count}
                                    </span>
                                )}
                            </span>
                        </button>
                    );
                })}
            </nav>
        </div>
    );
}
