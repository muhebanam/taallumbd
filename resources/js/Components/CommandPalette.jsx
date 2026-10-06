import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';

export default function CommandPalette({ isOpen, onClose }) {
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [results, setResults] = useState([]);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const inputRef = useRef(null);

    // Auto focus input when opened
    useEffect(() => {
        if (isOpen) {
            setQuery('');
            setResults([]);
            setSelectedIndex(0);
            setTimeout(() => inputRef.current?.focus(), 50);
        }
    }, [isOpen]);

    // Live search query debounce
    useEffect(() => {
        if (!isOpen) return;

        const trimmed = query.trim();
        if (trimmed.length < 2) {
            setResults([]);
            setLoading(false);
            return;
        }

        setLoading(true);
        const timer = setTimeout(async () => {
            try {
                const res = await fetch(`/search/live?q=${encodeURIComponent(trimmed)}`);
                if (res.ok) {
                    const data = await res.json();
                    setResults(data.items || []);
                    setSelectedIndex(0);
                }
            } catch (err) {
                console.error('Search failed', err);
            } finally {
                setLoading(false);
            }
        }, 220);

        return () => clearTimeout(timer);
    }, [query, isOpen]);

    // Keyboard navigation (Escape, ArrowUp, ArrowDown, Enter)
    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            onClose();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            setSelectedIndex((prev) => (results.length > 0 ? (prev + 1) % results.length : 0));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setSelectedIndex((prev) => (results.length > 0 ? (prev - 1 + results.length) % results.length : 0));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (results[selectedIndex]) {
                handleSelect(results[selectedIndex]);
            } else if (query.trim()) {
                handleViewAll();
            }
        }
    };

    const handleSelect = (item) => {
        onClose();
        if (item.url) {
            router.visit(item.url);
        }
    };

    const handleViewAll = () => {
        onClose();
        if (query.trim()) {
            router.visit(`/search?q=${encodeURIComponent(query.trim())}`);
        }
    };

    const getTypeBadgeColor = (type) => {
        switch (type) {
            case 'course': return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
            case 'teacher': return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
            case 'lesson': return 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30';
            case 'quran': return 'bg-teal-500/20 text-teal-300 border-teal-500/30';
            case 'hadith': return 'bg-blue-500/20 text-blue-300 border-blue-500/30';
            case 'fatwa': return 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30';
            case 'article': return 'bg-purple-500/20 text-purple-300 border-purple-500/30';
            case 'publication': return 'bg-orange-500/20 text-orange-300 border-orange-500/30';
            default: return 'bg-gray-500/20 text-gray-300 border-gray-500/30';
        }
    };

    if (!isOpen) return null;

    return (
        <div 
            className="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-black/60 backdrop-blur-sm transition-opacity"
            onClick={onClose}
        >
            <div 
                className="w-full max-w-2xl overflow-hidden rounded-2xl bg-[#102526] border border-[#254244] shadow-2xl shadow-black/80 flex flex-col"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Search Bar Input */}
                <div className="relative flex items-center border-b border-[#254244] px-4 py-3.5 bg-[#142C2E]">
                    <svg className="h-5 w-5 text-gray-400 shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>

                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onKeyDown={handleKeyDown}
                        placeholder="কোর্স, শিক্ষক, হাদিস, আয়াত, ফতোয়া বা প্রবন্ধ খুঁজুন... (বাংলা/عربي)"
                        className="w-full bg-transparent text-white placeholder-gray-400 focus:outline-none text-base font-bangla"
                    />

                    {loading && (
                        <div className="animate-spin rounded-full h-4 w-4 border-2 border-[#FFF99A] border-t-transparent shrink-0 ml-2" />
                    )}

                    {query && !loading && (
                        <button
                            onClick={() => setQuery('')}
                            className="text-gray-400 hover:text-white p-1 rounded-full text-xs shrink-0 ml-1"
                        >
                            ✕
                        </button>
                    )}

                    <kbd className="hidden sm:inline-block ml-2 px-2 py-0.5 text-[10px] font-mono text-gray-400 bg-white/5 border border-white/10 rounded">
                        ESC
                    </kbd>
                </div>

                {/* Results Section */}
                <div className="max-h-96 overflow-y-auto p-2 divide-y divide-white/5">
                    {query.trim().length >= 2 && results.length === 0 && !loading && (
                        <div className="py-10 text-center text-gray-400">
                            <p className="text-sm">"{query}" এর জন্য কোনো ফলাফল পাওয়া যায়নি।</p>
                            <p className="text-xs text-gray-500 mt-1">ভিন্ন বানান বা আরবি/বাংলা প্রতিশব্দ দিয়ে চেষ্টা করুন।</p>
                        </div>
                    )}

                    {results.map((item, idx) => (
                        <div
                            key={`${item.type}-${item.id}-${idx}`}
                            onClick={() => handleSelect(item)}
                            className={`flex items-center gap-3 p-3 rounded-xl cursor-pointer transition ${
                                selectedIndex === idx 
                                    ? 'bg-[#1A383B] text-white ring-1 ring-[#FFF99A]/30' 
                                    : 'text-gray-200 hover:bg-white/5'
                            }`}
                        >
                            {/* Thumbnail or Icon */}
                            {item.thumbnail ? (
                                <img
                                    src={item.thumbnail}
                                    alt=""
                                    className="h-10 w-10 rounded-lg object-cover bg-white/10 shrink-0"
                                    onError={(e) => { e.target.style.display = 'none'; }}
                                />
                            ) : (
                                <div className="h-10 w-10 rounded-lg bg-white/10 flex items-center justify-center text-lg shrink-0">
                                    {item.type === 'quran' ? '📖' : item.type === 'hadith' ? '📜' : item.type === 'teacher' ? '👤' : '📚'}
                                </div>
                            )}

                            {/* Details */}
                            <div className="flex-1 min-w-0">
                                <div className="flex items-center gap-2 mb-0.5">
                                    <span className={`px-2 py-0.5 text-[10px] font-bold rounded-full border ${getTypeBadgeColor(item.type)}`}>
                                        {item.type_label}
                                    </span>
                                    <h4 className="text-sm font-semibold text-white truncate font-bangla">
                                        {item.title}
                                    </h4>
                                </div>
                                {item.subtitle && (
                                    <p className="text-xs text-gray-400 truncate">
                                        {item.subtitle}
                                    </p>
                                )}
                            </div>

                            <span className="text-xs text-gray-500 shrink-0">
                                ↵
                            </span>
                        </div>
                    ))}

                    {query.trim().length < 2 && (
                        <div className="py-8 px-4 text-center">
                            <div className="flex justify-center gap-2 mb-3">
                                <span className="px-2.5 py-1 text-xs rounded-lg bg-white/5 text-gray-300">নামায / সালাত</span>
                                <span className="px-2.5 py-1 text-xs rounded-lg bg-white/5 text-gray-300">সহীহ বুখারী</span>
                                <span className="px-2.5 py-1 text-xs rounded-lg bg-white/5 text-gray-300">আল-ফাতিহা</span>
                                <span className="px-2.5 py-1 text-xs rounded-lg bg-white/5 text-gray-300">আকীদাহ</span>
                            </div>
                            <p className="text-xs text-gray-500">
                                অন্তত ২টি অক্ষর লিখুন বা ওপরের পরামর্শগুলো খুঁজুন
                            </p>
                        </div>
                    )}
                </div>

                {/* Footer Controls */}
                <div className="flex items-center justify-between border-t border-[#254244] bg-[#0E1F20] px-4 py-2.5 text-xs text-gray-400">
                    <div className="flex items-center gap-3">
                        <span><kbd className="px-1.5 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">↑↓</kbd> নেভিগেট</span>
                        <span><kbd className="px-1.5 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">↵</kbd> সিলেক্ট</span>
                    </div>

                    {query.trim().length >= 2 && (
                        <button
                            onClick={handleViewAll}
                            className="font-medium text-[#FFF99A] hover:underline flex items-center gap-1"
                        >
                            সকল ফলাফল দেখুন ({results.length}+) &rarr;
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}
