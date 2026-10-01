import React from 'react';
import { Link } from '@inertiajs/react';

export default function Footer() {
    return (
        <footer className="mt-20 border-t border-[#1A2E2F]/10 bg-[#102526] text-white/80">
            {/* Top Islamic Accent Bar */}
            <div className="h-1.5 w-full bg-gradient-to-r from-[#1A2E2F] via-[#FFF99A] to-[#1A2E2F]"></div>

            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-5">
                    {/* Brand Column */}
                    <div className="lg:col-span-2 space-y-4">
                        <Link href="/" className="inline-flex items-center gap-3">
                            <img 
                                src="/images/logo.svg" 
                                alt="আত-তাআল্লুম" 
                                onError={(e) => {
                                    e.target.onerror = null;
                                    e.target.src = '/images/logo.png';
                                }}
                                className="h-12 w-12 rounded-full bg-white/10 p-0.5 object-contain" 
                            />
                            <div>
                                <span className="text-2xl font-bold tracking-tight text-[#FFF99A] font-bangla">আত-তাআল্লুম</span>
                                <p className="text-xs text-emerald-300 font-medium">TaallumBD Digital Islamic Ecosystem</p>
                            </div>
                        </Link>

                        <p className="text-sm leading-relaxed text-slate-300 pr-4">
                            আধুনিক তথ্যপ্রযুক্তির সমন্বয়ে বিশুদ্ধ ইসলামী জ্ঞান অর্জনের এক উন্মুক্ত ডিজিটাল প্ল্যাটফর্ম। নির্ভরযোগ্য বিজ্ঞ উলামায়ে কেরামের প্রত্যক্ষ তত্ত্বাবধানে কুরআন, হাদীস, ফিকহ, আকীদাহ ও আরবী ভাষার সুসংগঠিত প্রাতিষ্ঠানিক সিলেবাস।
                        </p>

                        <div className="flex items-center gap-3 pt-2">
                            <span className="text-xs text-white/50">নিরাপদ পেমেন্ট পার্টনার:</span>
                            <div className="flex items-center gap-2 text-xs font-semibold text-[#FFF99A]">
                                <span className="rounded bg-white/10 px-2 py-0.5">বিকাশ</span>
                                <span className="rounded bg-white/10 px-2 py-0.5">নগদ</span>
                                <span className="rounded bg-white/10 px-2 py-0.5">SSLCommerz</span>
                            </div>
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h4 className="text-sm font-bold uppercase tracking-wider text-[#FFF99A]">কোর্স ও পাঠ্যক্রম</h4>
                        <ul className="mt-4 space-y-2.5 text-sm">
                            <li><Link href="/courses" className="hover:text-[#FFF99A] transition">সকল কোর্স</Link></li>
                            <li><Link href="/courses/category/quran" className="hover:text-[#FFF99A] transition">কুরআন ও তাজবীদ</Link></li>
                            <li><Link href="/courses/category/hadith" className="hover:text-[#FFF99A] transition">হাদীস শাস্ত্র</Link></li>
                            <li><Link href="/courses/category/fiqh" className="hover:text-[#FFF99A] transition">দৈনন্দিন ফিকহ</Link></li>
                            <li><Link href="/courses/category/arabic" className="hover:text-[#FFF99A] transition">আরবি ভাষা ও ব্যাকরণ</Link></li>
                        </ul>
                    </div>

                    {/* Knowledge Library */}
                    <div>
                        <h4 className="text-sm font-bold uppercase tracking-wider text-[#FFF99A]">জ্ঞানভাণ্ডার</h4>
                        <ul className="mt-4 space-y-2.5 text-sm">
                            <li><Link href="/about/teachers" className="hover:text-[#FFF99A] transition">শিক্ষকমণ্ডলী (Scholars)</Link></li>
                            <li><Link href="/fatawa" className="hover:text-[#FFF99A] transition">ফাতাওয়া ও সমাধান</Link></li>
                            <li><Link href="/fatawa/ask" className="hover:text-[#FFF99A] transition">শরয়ী প্রশ্ন করুন</Link></li>
                            <li><Link href="/articles" className="hover:text-[#FFF99A] transition">গবেষণাধর্মী প্রবন্ধ</Link></li>
                            <li><Link href="/publications" className="hover:text-[#FFF99A] transition">কিতাব ও প্রকাশনা</Link></li>
                        </ul>
                    </div>

                    {/* Contact & Support */}
                    <div>
                        <h4 className="text-sm font-bold uppercase tracking-wider text-[#FFF99A]">যোগাযোগ ও সহায়তা</h4>
                        <ul className="mt-4 space-y-2.5 text-sm text-slate-300">
                            <li className="flex items-start gap-2">
                                <span className="text-[#FFF99A]">📍</span>
                                <span>ঢাকা, বাংলাদেশ</span>
                            </li>
                            <li className="flex items-center gap-2">
                                <span className="text-[#FFF99A]">✉️</span>
                                <a href="mailto:support@taallumbd.com" className="hover:text-white transition">support@taallumbd.com</a>
                            </li>
                            <li className="flex items-center gap-2">
                                <span className="text-[#FFF99A]">🌐</span>
                                <span>www.taallumbd.com</span>
                            </li>
                            <li className="pt-2">
                                <Link 
                                    href="/become-instructor" 
                                    className="inline-block rounded-lg bg-[#1A2E2F] border border-[#FFF99A]/30 px-3 py-1.5 text-xs font-semibold text-[#FFF99A] hover:bg-[#FFF99A] hover:text-[#102526] transition"
                                >
                                    উস্তায/শিক্ষক হিসেবে আবেদন
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Bottom Bar */}
                <div className="mt-12 border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/60">
                    <p>© {new Date().getFullYear()} আত-তাআল্লুম (TaallumBD)। সর্বস্বত্ব সংরক্ষিত।</p>
                    <div className="flex items-center gap-6">
                        <Link href="/about" className="hover:text-white transition">পরিচিতি</Link>
                        <Link href="/contact" className="hover:text-white transition">যোগাযোগ</Link>
                        <span>বিসমিল্লাহির রহমানির রহিম</span>
                    </div>
                </div>
            </div>
        </footer>
    );
}
