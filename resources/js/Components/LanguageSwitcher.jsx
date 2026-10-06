import React, { useState, useRef, useEffect } from 'react';
import { useTranslation } from '../Hooks/useTranslation';

export default function LanguageSwitcher({ className = '' }) {
    const {
        locale,
        currency,
        hijriDate,
        availableLocales,
        switchLocale,
        switchCurrency,
    } = useTranslation();

    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const currentLocaleInfo = availableLocales[locale] || { name: 'বাংলা', flag: '🇧🇩' };

    return (
        <div className={`relative inline-block text-left ${className}`} ref={dropdownRef}>
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-emerald-950 bg-emerald-50/80 hover:bg-emerald-100/90 rounded-full border border-emerald-200/60 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500"
                aria-expanded={isOpen}
                aria-label="Language and Currency Switcher"
            >
                <span className="text-sm">{currentLocaleInfo.flag || '🌐'}</span>
                <span className="font-semibold">{currentLocaleInfo.name}</span>
                <span className="text-gray-400">|</span>
                <span className="font-bold text-emerald-800">{currency === 'USD' ? '$ USD' : '৳ BDT'}</span>
                <svg className={`w-3.5 h-3.5 text-gray-500 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 p-3 z-50 animate-in fade-in zoom-in-95 duration-150">
                    {/* Hijri Date Display */}
                    {hijriDate && (
                        <div className="px-3 py-2 mb-2 bg-emerald-50/70 rounded-xl border border-emerald-100 flex items-center gap-2">
                            <span className="text-emerald-700 text-sm">🌙</span>
                            <span className="text-xs font-medium text-emerald-900 leading-tight">
                                {hijriDate}
                            </span>
                        </div>
                    )}

                    {/* Language Selection */}
                    <div className="text-[11px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1">
                        ভাষা / Language / اللغة
                    </div>
                    <div className="space-y-1 mb-2">
                        {Object.entries(availableLocales).map(([code, item]) => {
                            const isSelected = locale === code;
                            return (
                                <button
                                    key={code}
                                    type="button"
                                    onClick={() => {
                                        setIsOpen(false);
                                        switchLocale(code);
                                    }}
                                    className={`w-full flex items-center justify-between px-3 py-2 text-xs rounded-xl transition-colors ${
                                        isSelected
                                            ? 'bg-emerald-600 text-white font-bold'
                                            : 'text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    <div className="flex items-center gap-2.5">
                                        <span className="text-sm">{item.flag || '🌐'}</span>
                                        <span>{item.name}</span>
                                    </div>
                                    {isSelected && (
                                        <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    )}
                                </button>
                            );
                        })}
                    </div>

                    {/* Currency Selection */}
                    <div className="pt-2 border-t border-gray-100">
                        <div className="text-[11px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1">
                            মুদ্রা / Currency
                        </div>
                        <div className="grid grid-cols-2 gap-1.5 mt-1">
                            <button
                                type="button"
                                onClick={() => {
                                    setIsOpen(false);
                                    switchCurrency('BDT');
                                }}
                                className={`flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs rounded-xl font-medium transition-colors ${
                                    currency === 'BDT'
                                        ? 'bg-emerald-100 text-emerald-900 font-bold border border-emerald-300'
                                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                }`}
                            >
                                <span>৳</span>
                                <span>BDT</span>
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setIsOpen(false);
                                    switchCurrency('USD');
                                }}
                                className={`flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs rounded-xl font-medium transition-colors ${
                                    currency === 'USD'
                                        ? 'bg-emerald-100 text-emerald-900 font-bold border border-emerald-300'
                                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                }`}
                            >
                                <span>$</span>
                                <span>USD</span>
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
