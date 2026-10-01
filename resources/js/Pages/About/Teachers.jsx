import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import TeacherCard from '../../Components/TeacherCard';
import Pagination from '../../Components/Pagination';

export default function TeachersIndex({ teachers, featuredTeachers = [], stats = {}, filters = {} }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [specialty, setSpecialty] = useState(filters.specialty ?? 'সবাই');
    const [sort, setSort] = useState(filters.sort ?? 'popular');
    const [verified, setVerified] = useState(filters.verified ?? false);

    const specialtiesList = [
        'সবাই', 'আক্বীদা', 'কুরআন', 'তাফসীর', 'হাদীস', 'ফিকহ', 'সীরাত', 
        'আরবী ভাষা', 'ইসলামী অর্থনীতি', 'হালাল-হারাম', 'आत्मশুদ্ধি', 'ইতিহাস', 'অন্যান্য'
    ];

    const sortOptions = [
        { value: 'popular', label: 'জনপ্রিয়' },
        { value: 'latest', label: 'নতুন যুক্ত' },
        { value: 'courses', label: 'সর্বাধিক কোর্স' },
        { value: 'articles', label: 'সর্বাধিক প্রবন্ধ' },
        { value: 'fatawa', label: 'সর্বাধিক ফাতাওয়া' },
        { value: 'rating', label: 'সর্বোচ্চ রেটিং' }
    ];

    const applyFilters = () => {
        router.get('/about/teachers', {
            search: search || undefined,
            specialty: specialty === 'সবাই' ? undefined : specialty,
            sort: sort,
            verified: verified ? 1 : undefined
        }, {
            preserveState: true,
            replace: true
        });
    };

    // Debounce search input or apply filters on submit
    const handleSearchSubmit = (e) => {
        e.preventDefault();
        applyFilters();
    };

    const handleSpecialtyChange = (spec) => {
        setSpecialty(spec);
        router.get('/about/teachers', {
            search: search || undefined,
            specialty: spec === 'সবাই' ? undefined : spec,
            sort: sort,
            verified: verified ? 1 : undefined
        }, { replace: true });
    };

    const handleSortChange = (e) => {
        const val = e.target.value;
        setSort(val);
        router.get('/about/teachers', {
            search: search || undefined,
            specialty: specialty === 'সবাই' ? undefined : specialty,
            sort: val,
            verified: verified ? 1 : undefined
        }, { replace: true });
    };

    const handleVerifiedToggle = () => {
        const nextVal = !verified;
        setVerified(nextVal);
        router.get('/about/teachers', {
            search: search || undefined,
            specialty: specialty === 'সবাই' ? undefined : specialty,
            sort: sort,
            verified: nextVal ? 1 : undefined
        }, { replace: true });
    };

    return (
        <AppLayout>
            <Head title="শিক্ষকমণ্ডলী" />

            {/* Hero Section */}
            <div className="bg-[#102526] text-white py-16 px-4 relative overflow-hidden border-b border-brand-cream/15">
                <div className="absolute inset-0 opacity-5 bg-[radial-gradient(#FFF99A_1px,transparent_1px)] [background-size:24px_24px]"></div>
                <div className="mx-auto max-w-7xl relative z-10 text-center">
                    <span className="inline-block rounded-full bg-brand-cream/10 px-3 py-1 text-xs font-semibold text-brand-cream border border-brand-cream/20">আমাদের শিক্ষকমণ্ডলী</span>
                    <h1 className="text-3xl font-extrabold text-brand-cream sm:text-5xl mt-4">আত-তাআল্লুম শিক্ষকমণ্ডলী</h1>
                    <p className="mt-4 text-slate-300 max-w-3xl mx-auto text-sm leading-relaxed sm:text-base">
                        অভিজ্ঞ উলামা, মুফতী ও গবেষকদের তত্ত্বাবধানে সাজানো দ্বীনি শিক্ষার নির্ভরযোগ্য প্ল্যাটফর্ম। প্রতিটি শিক্ষক নিজ নিজ বিষয়ে অভিজ্ঞ, গবেষণাপ্রবণ এবং শিক্ষার্থীদের প্রশ্নোত্তর ও একাডেমিক গাইডলাইনে সহযোগিতা করেন।
                    </p>

                    {/* Stats List */}
                    <div className="mt-10 grid grid-cols-2 gap-4 max-w-4xl mx-auto sm:grid-cols-4 lg:grid-cols-6">
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_teachers ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">সক্রিয় শিক্ষক</span>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_courses ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">মোট কোর্স</span>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_articles ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">প্রবন্ধসমূহ</span>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_fatawa ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">ফাতাওয়া উত্তর</span>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_publications ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">প্রকাশনা/বই</span>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-4">
                            <span className="block text-2xl font-extrabold text-brand-cream">{stats.total_followers ?? 0}</span>
                            <span className="text-xs text-slate-400 mt-1 block">মোট ফলোয়ার</span>
                        </div>
                    </div>
                </div>
            </div>

            <div className="mx-auto max-w-7xl px-4 py-12">
                {/* Featured / Verified Teachers Banner */}
                {featuredTeachers.length > 0 && !filters.search && !filters.specialty && (
                    <div className="mb-12">
                        <h2 className="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <svg className="h-5 w-5 text-amber-500 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>ফিচার্ড শিক্ষকমণ্ডলী</span>
                        </h2>
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {featuredTeachers.map((teacher) => (
                                <TeacherCard key={teacher.id} teacher={teacher} />
                            ))}
                        </div>
                    </div>
                )}

                {/* Filter and search layout */}
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    {/* Search form */}
                    <form onSubmit={handleSearchSubmit} className="relative w-full md:w-96 flex">
                        <input
                            type="text"
                            placeholder="শিক্ষকের নাম, বিষয় বা দক্ষতা খুঁজুন..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full rounded-l-xl border border-slate-200 pl-4 pr-10 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                        <button
                            type="submit"
                            className="bg-[#102526] hover:bg-[#1A2E2F] text-white px-5 rounded-r-xl text-sm font-semibold transition-colors duration-300"
                        >
                            খুঁজুন
                        </button>
                    </form>

                    {/* Sorting dropdown & Verified Toggle */}
                    <div className="flex flex-wrap items-center gap-4 w-full md:w-auto justify-end">
                        {/* Verified Switch */}
                        <label className="inline-flex items-center cursor-pointer select-none">
                            <input
                                type="checkbox"
                                checked={verified}
                                onChange={handleVerifiedToggle}
                                className="sr-only peer"
                            />
                            <div className="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span className="ms-3 text-sm font-bold text-slate-700">ভেরিফাইড শিক্ষক</span>
                        </label>

                        {/* Sort Dropdown */}
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-bold text-slate-500 whitespace-nowrap">সাজান:</span>
                            <select
                                value={sort}
                                onChange={handleSortChange}
                                className="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700"
                            >
                                {sortOptions.map(opt => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                {/* Specialties filter tabs */}
                <div className="mb-8 flex gap-1.5 overflow-x-auto scrollbar-none pb-2 whitespace-nowrap">
                    {specialtiesList.map((spec) => (
                        <button
                            key={spec}
                            onClick={() => handleSpecialtyChange(spec)}
                            className={`rounded-full px-4 py-1.5 text-xs font-bold transition-all duration-300 ${
                                specialty === spec
                                    ? 'bg-[#102526] text-white border border-[#102526]'
                                    : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                            }`}
                        >
                            {spec}
                        </button>
                    ))}
                </div>

                {/* Teachers Grid */}
                {teachers.data.length > 0 ? (
                    <div>
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {teachers.data.map((teacher) => (
                                <TeacherCard key={teacher.id} teacher={teacher} />
                            ))}
                        </div>

                        {/* Pagination */}
                        <div className="mt-12 flex justify-center">
                            <Pagination links={teachers.links} />
                        </div>
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-slate-200 p-12 text-center">
                        <svg className="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 className="mt-4 text-base font-bold text-slate-800">শিক্ষক পাওয়া যায়নি</h3>
                        <p className="mt-2 text-sm text-slate-500">এই অনুসন্ধানের সাথে মিল থাকা কোনো শিক্ষক পাওয়া যায়নি। অনুগ্রহ করে অন্য কিছু লিখে খুঁজুন।</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
