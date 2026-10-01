import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardLayout from '../../../Layouts/DashboardLayout';

export default function CreateTeacher({ users }) {
    const { data, setData, post, processing, errors } = useForm({
        user_id: '',
        name: '',
        designation: '',
        headline: '',
        short_bio: '',
        bio: '',
        location: '',
        email: '',
        phone: '',
        website: '',
        facebook_url: '',
        youtube_url: '',
        linkedin_url: '',
        twitter_url: '',
        instagram_url: '',
        telegram_url: '',
        avatar_file: null,
        cover_file: null,
        specialties: [],
        knowledge_path: [''],
        expertise_map: [{ main_area: '', sub_areas: '' }],
        qualifications: [{ degree_title: '', institution: '', department: '', year: '', description: '' }],
        experiences: [{ title: '', organization: '', start_year: '', end_year: '', currently_working: false, description: '' }],
        office_hours: [{ day: '', time: '', method: '' }],
        consultation_enabled: false,
        consultation_note: '',
        status: 'active',
        featured: false,
        is_verified: false,
        allow_follow: true,
        show_email: false,
        show_phone: false,
        sort_order: 0,
    });

    const [avatarPreview, setAvatarPreview] = useState(null);
    const [coverPreview, setCoverPreview] = useState(null);
    const [activeFormTab, setActiveFormTab] = useState('basic');

    const specialtiesOptions = [
        'আক্বীদা', 'কুরআন', 'তাফসীর', 'হাদীস', 'ফিকহ', 'সীরাত', 
        'আরবী ভাষা', 'ইসলামী অর্থনীতি', 'হালাল-হারাম', 'ইতিহাস', 'आत्मশুদ্ধি', 'সমকালীন মাসআলা', 'অন্যান্য'
    ];

    const handleFileChange = (e, type) => {
        const file = e.target.files[0];
        if (file) {
            setData(type === 'avatar' ? 'avatar_file' : 'cover_file', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                if (type === 'avatar') {
                    setAvatarPreview(reader.result);
                } else {
                    setCoverPreview(reader.result);
                }
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSpecialtyChange = (spec) => {
        const current = [...data.specialties];
        const idx = current.indexOf(spec);
        if (idx >= 0) {
            current.splice(idx, 1);
        } else {
            current.push(spec);
        }
        setData('specialties', current);
    };

    // Repeatable handlers
    const addRepeatableRow = (field, defaultObject) => {
        setData(field, [...data[field], defaultObject]);
    };

    const removeRepeatableRow = (field, idx) => {
        const current = [...data[field]];
        current.splice(idx, 1);
        setData(field, current);
    };

    const handleRepeatableChange = (field, idx, key, val) => {
        const current = [...data[field]];
        if (key === null) {
            current[idx] = val; // for simple array like knowledge_path
        } else {
            current[idx] = { ...current[idx], [key]: val };
        }
        setData(field, current);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        // Convert expertise_map sub_areas comma string to array before submitting
        const formattedExpertiseMap = data.expertise_map.map(item => ({
            main_area: item.main_area,
            sub_areas: typeof item.sub_areas === 'string' 
                ? item.sub_areas.split(',').map(s => s.trim()).filter(Boolean)
                : item.sub_areas
        })).filter(item => item.main_area);

        // Filter empty simple arrays/objects
        const formattedKnowledgePath = data.knowledge_path.filter(Boolean);
        const formattedQualifications = data.qualifications.filter(q => q.degree_title && q.institution);
        const formattedExperiences = data.experiences.filter(exp => exp.title && exp.organization);
        const formattedOfficeHours = data.office_hours.filter(oh => oh.day && oh.time);

        const formData = {
            ...data,
            expertise_map: formattedExpertiseMap,
            knowledge_path: formattedKnowledgePath,
            qualifications: formattedQualifications,
            experiences: formattedExperiences,
            office_hours: formattedOfficeHours
        };

        post('/admin/teachers', formData);
    };

    return (
        <DashboardLayout title="নতুন শিক্ষক প্রোফাইল">
            <Head title="নতুন শিক্ষক" />

            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/teachers" className="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                    ← তালিকা
                </Link>
            </div>

            <div className="grid gap-8 lg:grid-cols-4">
                {/* Left tab navigator */}
                <div className="lg:col-span-1 space-y-2">
                    {[
                        { id: 'basic', label: 'মৌলিক তথ্য' },
                        { id: 'images', label: 'প্রোফাইল ও কভার ছবি' },
                        { id: 'specialties', label: 'বিশেষজ্ঞতা ও জ্ঞানপথ' },
                        { id: 'timeline', label: 'শিক্ষা ও অভিজ্ঞতা' },
                        { id: 'office', label: 'প্রশ্নোত্তর সময় ও যোগাযোগ' },
                        { id: 'social', label: 'সোশ্যাল মিডিয়া লিংক' },
                        { id: 'settings', label: 'প্রোফাইল সেটিংস' },
                    ].map(tab => (
                        <button
                            key={tab.id}
                            type="button"
                            onClick={() => setActiveFormTab(tab.id)}
                            className={`w-full text-right justify-start rounded-xl px-4 py-3 text-sm font-bold transition-all duration-200 border ${
                                activeFormTab === tab.id
                                    ? 'bg-[#102526] text-white border-[#102526]'
                                    : 'bg-white text-slate-600 border-slate-100 hover:bg-slate-50'
                            }`}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* Main form columns */}
                <div className="lg:col-span-3">
                    <form onSubmit={handleSubmit} className="space-y-6" encType="multipart/form-data">
                        
                        {/* Tab Content: Basic Info */}
                        {activeFormTab === 'basic' && (
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">মৌলিক তথ্য</h3>
                                
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label htmlFor="user_id" className="block text-xs font-bold text-slate-500 uppercase">ইউজার লিঙ্ক করুন</label>
                                        <select
                                            id="user_id"
                                            value={data.user_id}
                                            onChange={e => setData('user_id', e.target.value)}
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                        >
                                            <option value="">কোনো ইউজার লিঙ্ক করবেন না</option>
                                            {users.map(u => (
                                                <option key={u.id} value={u.id}>{u.name} ({u.email})</option>
                                            ))}
                                        </select>
                                        {errors.user_id && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.user_id}</p>}
                                        <p className="mt-1 text-[10px] text-slate-400 font-medium">ইনস্ট্রাক্টরের লগইন ইউজারের সাথে এই প্রোফাইলটি লিঙ্ক করুন।</p>
                                    </div>

                                    <div>
                                        <label htmlFor="name" className="block text-xs font-bold text-slate-500 uppercase">শিক্ষকের নাম</label>
                                        <input
                                            type="text"
                                            id="name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            required
                                        />
                                        {errors.name && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="designation" className="block text-xs font-bold text-slate-500 uppercase">পদবি / পরিচয়</label>
                                        <input
                                            type="text"
                                            id="designation"
                                            value={data.designation}
                                            onChange={e => setData('designation', e.target.value)}
                                            placeholder="উদা: মুফতী ও গবেষক"
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                        />
                                        {errors.designation && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.designation}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="location" className="block text-xs font-bold text-slate-500 uppercase">অবস্থান</label>
                                        <input
                                            type="text"
                                            id="location"
                                            value={data.location}
                                            onChange={e => setData('location', e.target.value)}
                                            placeholder="উদা: ঢাকা, বাংলাদেশ"
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label htmlFor="headline" className="block text-xs font-bold text-slate-500 uppercase">সংক্ষিপ্ত হেডলাইন</label>
                                    <input
                                        type="text"
                                        id="headline"
                                        value={data.headline}
                                        onChange={e => setData('headline', e.target.value)}
                                        placeholder="উদা: ফিকহ ও সমকালীন হালাল-হারাম বিষয়ক শিক্ষক"
                                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                    />
                                    {errors.headline && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.headline}</p>}
                                </div>

                                <div>
                                    <label htmlFor="short_bio" className="block text-xs font-bold text-slate-500 uppercase">সংক্ষিপ্ত পরিচিতি</label>
                                    <textarea
                                        id="short_bio"
                                        rows="2"
                                        value={data.short_bio}
                                        onChange={e => setData('short_bio', e.target.value)}
                                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    ></textarea>
                                </div>

                                <div>
                                    <label htmlFor="bio" className="block text-xs font-bold text-slate-500 uppercase">বিস্তারিত বায়ো</label>
                                    <textarea
                                        id="bio"
                                        rows="6"
                                        value={data.bio}
                                        onChange={e => setData('bio', e.target.value)}
                                        className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Images */}
                        {activeFormTab === 'images' && (
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">ছবি ও কভার ইমেজ</h3>

                                {/* Profile Picture */}
                                <div>
                                    <span className="block text-xs font-bold text-slate-500 uppercase">প্রোফাইল ছবি (Avatar)</span>
                                    <div className="mt-3 flex items-center gap-4">
                                        <div className="h-24 w-24 rounded-full border border-slate-200 bg-slate-50 overflow-hidden shrink-0">
                                            {avatarPreview ? (
                                                <img src={avatarPreview} alt="Preview" className="h-full w-full object-cover" />
                                            ) : (
                                                <div className="h-full w-full flex items-center justify-center text-slate-300">No Image</div>
                                            )}
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={e => handleFileChange(e, 'avatar')}
                                            className="text-xs font-bold text-slate-600"
                                        />
                                    </div>
                                    {errors.avatar_file && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.avatar_file}</p>}
                                </div>

                                {/* Cover Banner */}
                                <div>
                                    <span className="block text-xs font-bold text-slate-500 uppercase">কভার ছবি (Cover Banner)</span>
                                    <div className="mt-3 space-y-3">
                                        <div className="h-32 w-full rounded-xl border border-slate-200 bg-slate-50 overflow-hidden relative">
                                            {coverPreview ? (
                                                <img src={coverPreview} alt="Preview" className="h-full w-full object-cover" />
                                            ) : (
                                                <div className="absolute inset-0 flex items-center justify-center text-slate-300">No Image</div>
                                            )}
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={e => handleFileChange(e, 'cover')}
                                            className="text-xs font-bold text-slate-600"
                                        />
                                    </div>
                                    {errors.cover_file && <p className="mt-1 text-xs text-red-600 font-semibold">{errors.cover_file}</p>}
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Specialties & Knowledge Path */}
                        {activeFormTab === 'specialties' && (
                            <div className="space-y-6">
                                {/* Specialties Checklist */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">বিশেষজ্ঞতা / পড়ানোর বিষয়সমূহ</h3>
                                    <div className="mt-4 grid gap-3 grid-cols-2 sm:grid-cols-3">
                                        {specialtiesOptions.map(spec => {
                                            const isChecked = data.specialties.includes(spec);
                                            return (
                                                <label key={spec} className="inline-flex items-center gap-2 cursor-pointer select-none">
                                                    <input
                                                        type="checkbox"
                                                        checked={isChecked}
                                                        onChange={() => handleSpecialtyChange(spec)}
                                                        className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                                    />
                                                    <span className="text-xs font-semibold text-slate-700">{spec}</span>
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Knowledge Path Repeatable */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex justify-between items-center">
                                        <span>এই শিক্ষকের মাধ্যমে কী শিখবে? (Knowledge Path)</span>
                                        <button
                                            type="button"
                                            onClick={() => addRepeatableRow('knowledge_path', '')}
                                            className="text-xs font-bold text-emerald-600 hover:underline"
                                        >
                                            + নতুন ধাপ
                                        </button>
                                    </h3>
                                    
                                    <div className="mt-4 space-y-3">
                                        {data.knowledge_path.map((point, idx) => (
                                            <div key={idx} className="flex gap-2 items-center">
                                                <span className="text-xs font-bold text-slate-400 w-4">{idx + 1}.</span>
                                                <input
                                                    type="text"
                                                    value={point}
                                                    onChange={e => handleRepeatableChange('knowledge_path', idx, null, e.target.value)}
                                                    placeholder="উদা: আক্বীদা ও ইসলামের মৌলিক স্তরের শিক্ষা"
                                                    className="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => removeRepeatableRow('knowledge_path', idx)}
                                                    className="text-xs text-red-500 font-bold hover:underline"
                                                >
                                                    বাদ
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Expertise Map Repeatable */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex justify-between items-center">
                                        <span>বিশেষজ্ঞতার মানচিত্র (Expertise Map)</span>
                                        <button
                                            type="button"
                                            onClick={() => addRepeatableRow('expertise_map', { main_area: '', sub_areas: '' })}
                                            className="text-xs font-bold text-emerald-600 hover:underline"
                                        >
                                            + নতুন ক্ষেত্র
                                        </button>
                                    </h3>
                                    
                                    <div className="mt-4 space-y-4">
                                        {data.expertise_map.map((item, idx) => (
                                            <div key={idx} className="rounded-xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-xs font-bold text-slate-400">ক্ষেত্র #{idx + 1}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeRepeatableRow('expertise_map', idx)}
                                                        className="text-xs text-red-500 font-bold hover:underline"
                                                    >
                                                        বাদ দিন
                                                    </button>
                                                </div>

                                                <div className="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">মূল বিষয়</label>
                                                        <input
                                                            type="text"
                                                            value={item.main_area}
                                                            onChange={e => handleRepeatableChange('expertise_map', idx, 'main_area', e.target.value)}
                                                            placeholder="উদা: ফিকহ"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">উপ-বিষয়সমূহ (কমা দিয়ে লিখুন)</label>
                                                        <input
                                                            type="text"
                                                            value={item.sub_areas}
                                                            onChange={e => handleRepeatableChange('expertise_map', idx, 'sub_areas', e.target.value)}
                                                            placeholder="উদা: পারিবারিক মাসআলা, হালাল-হারাম, অর্থনীতি"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Timeline */}
                        {activeFormTab === 'timeline' && (
                            <div className="space-y-6">
                                {/* Qualifications Repeatable */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex justify-between items-center">
                                        <span>শিক্ষাগত যোগ্যতা (Qualifications)</span>
                                        <button
                                            type="button"
                                            onClick={() => addRepeatableRow('qualifications', { degree_title: '', institution: '', department: '', year: '', description: '' })}
                                            className="text-xs font-bold text-emerald-600 hover:underline"
                                        >
                                            + নতুন যোগ্যতা
                                        </button>
                                    </h3>

                                    <div className="mt-4 space-y-4">
                                        {data.qualifications.map((item, idx) => (
                                            <div key={idx} className="rounded-xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-xs font-bold text-slate-400">ডিগ্রি #{idx + 1}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeRepeatableRow('qualifications', idx)}
                                                        className="text-xs text-red-500 font-bold hover:underline"
                                                    >
                                                        বাদ দিন
                                                    </button>
                                                </div>

                                                <div className="grid gap-3 sm:grid-cols-4">
                                                    <div className="sm:col-span-2">
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">সনদ / ডিগ্রির নাম</label>
                                                        <input
                                                            type="text"
                                                            value={item.degree_title}
                                                            onChange={e => handleRepeatableChange('qualifications', idx, 'degree_title', e.target.value)}
                                                            placeholder="উদা: দাওরায়ে হাদীস"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">বিভাগ</label>
                                                        <input
                                                            type="text"
                                                            value={item.department}
                                                            onChange={e => handleRepeatableChange('qualifications', idx, 'department', e.target.value)}
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">সাল</label>
                                                        <input
                                                            type="text"
                                                            value={item.year}
                                                            onChange={e => handleRepeatableChange('qualifications', idx, 'year', e.target.value)}
                                                            placeholder="২০১৮"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                </div>

                                                <div>
                                                    <label className="block text-[10px] font-bold text-slate-400 uppercase">প্রতিষ্ঠান</label>
                                                    <input
                                                        type="text"
                                                        value={item.institution}
                                                        onChange={e => handleRepeatableChange('qualifications', idx, 'institution', e.target.value)}
                                                        className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                    />
                                                </div>

                                                <div>
                                                    <label className="block text-[10px] font-bold text-slate-400 uppercase">সংক্ষিপ্ত বিবরণ</label>
                                                    <textarea
                                                        value={item.description}
                                                        onChange={e => handleRepeatableChange('qualifications', idx, 'description', e.target.value)}
                                                        rows="2"
                                                        className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                    ></textarea>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Experiences Repeatable */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex justify-between items-center">
                                        <span>অভিজ্ঞতা (Experiences)</span>
                                        <button
                                            type="button"
                                            onClick={() => addRepeatableRow('experiences', { title: '', organization: '', start_year: '', end_year: '', currently_working: false, description: '' })}
                                            className="text-xs font-bold text-emerald-600 hover:underline"
                                        >
                                            + নতুন অভিজ্ঞতা
                                        </button>
                                    </h3>

                                    <div className="mt-4 space-y-4">
                                        {data.experiences.map((item, idx) => (
                                            <div key={idx} className="rounded-xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-xs font-bold text-slate-400">অভিজ্ঞতা #{idx + 1}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeRepeatableRow('experiences', idx)}
                                                        className="text-xs text-red-500 font-bold hover:underline"
                                                    >
                                                        বাদ দিন
                                                    </button>
                                                </div>

                                                <div className="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">পদবি / কাজের ধরন</label>
                                                        <input
                                                            type="text"
                                                            value={item.title}
                                                            onChange={e => handleRepeatableChange('experiences', idx, 'title', e.target.value)}
                                                            placeholder="উদা: ফিকহ শিক্ষক"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">প্রতিষ্ঠান / অর্গানাইজেশন</label>
                                                        <input
                                                            type="text"
                                                            value={item.organization}
                                                            onChange={e => handleRepeatableChange('experiences', idx, 'organization', e.target.value)}
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                </div>

                                                <div className="grid gap-3 sm:grid-cols-3">
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">শুরুর সাল</label>
                                                        <input
                                                            type="text"
                                                            value={item.start_year}
                                                            onChange={e => handleRepeatableChange('experiences', idx, 'start_year', e.target.value)}
                                                            placeholder="২০২০"
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 uppercase">শেষ সাল</label>
                                                        <input
                                                            type="text"
                                                            value={item.end_year}
                                                            onChange={e => handleRepeatableChange('experiences', idx, 'end_year', e.target.value)}
                                                            placeholder="২০২২"
                                                            disabled={item.currently_working}
                                                            className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                                        />
                                                    </div>
                                                    <div className="flex items-center pt-5">
                                                        <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                                            <input
                                                                type="checkbox"
                                                                checked={item.currently_working}
                                                                onChange={e => {
                                                                    const checked = e.target.checked;
                                                                    handleRepeatableChange('experiences', idx, 'currently_working', checked);
                                                                    if (checked) {
                                                                        handleRepeatableChange('experiences', idx, 'end_year', '');
                                                                    }
                                                                }}
                                                                className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                                            />
                                                            <span className="text-xs font-semibold text-slate-700">বর্তমানে কর্মরত</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label className="block text-[10px] font-bold text-slate-400 uppercase">দায়িত্ব ও অর্জন</label>
                                                    <textarea
                                                        value={item.description}
                                                        onChange={e => handleRepeatableChange('experiences', idx, 'description', e.target.value)}
                                                        rows="2"
                                                        className="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm bg-white"
                                                    ></textarea>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Office Hours */}
                        {activeFormTab === 'office' && (
                            <div className="space-y-6">
                                {/* Office Hours Settings */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">প্রশ্নোত্তর সময় সেটিংস</h3>
                                    
                                    <div className="flex items-center">
                                        <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                checked={data.consultation_enabled}
                                                onChange={e => setData('consultation_enabled', e.target.checked)}
                                                className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                            />
                                            <span className="text-sm font-bold text-slate-700">প্রশ্নোত্তর সময় চালু করুন</span>
                                        </label>
                                    </div>

                                    {data.consultation_enabled && (
                                        <>
                                            <div>
                                                <label htmlFor="consultation_note" className="block text-xs font-bold text-slate-500 uppercase">প্রশ্নোত্তর সময় সংক্রান্ত নোট</label>
                                                <input
                                                    type="text"
                                                    id="consultation_note"
                                                    value={data.consultation_note}
                                                    onChange={e => setData('consultation_note', e.target.value)}
                                                    placeholder="উদা: মুফতী সাহেব সাধারণত নির্দিষ্ট সময়গুলোতে অনলাইনে উত্তর দিয়ে থাকেন।"
                                                    className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                />
                                            </div>

                                            {/* Repeatable Hours */}
                                            <div className="border-t border-slate-100 pt-4">
                                                <h4 className="text-xs font-bold text-slate-500 uppercase flex justify-between items-center">
                                                    <span>নির্ধারিত সময়সমূহ</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => addRepeatableRow('office_hours', { day: '', time: '', method: '' })}
                                                        className="text-xs font-bold text-emerald-600 hover:underline"
                                                    >
                                                        + সময় যোগ করুন
                                                    </button>
                                                </h4>

                                                <div className="mt-3 space-y-3">
                                                    {data.office_hours.map((oh, idx) => (
                                                        <div key={idx} className="flex gap-2 items-center">
                                                            <input
                                                                type="text"
                                                                value={oh.day}
                                                                onChange={e => handleRepeatableChange('office_hours', idx, 'day', e.target.value)}
                                                                placeholder="দিন (উদা: শনি - সোম)"
                                                                className="w-1/3 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                            />
                                                            <input
                                                                type="text"
                                                                value={oh.time}
                                                                onChange={e => handleRepeatableChange('office_hours', idx, 'time', e.target.value)}
                                                                placeholder="সময় (উদা: রাত ৯টা - ১০টা)"
                                                                className="w-1/3 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                            />
                                                            <input
                                                                type="text"
                                                                value={oh.method}
                                                                onChange={e => handleRepeatableChange('office_hours', idx, 'method', e.target.value)}
                                                                placeholder="মাধ্যম (উদা: অনলাইন/ফোনে)"
                                                                className="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                            />
                                                            <button
                                                                type="button"
                                                                onClick={() => removeRepeatableRow('office_hours', idx)}
                                                                className="text-xs text-red-500 font-bold hover:underline"
                                                            >
                                                                বাদ
                                                            </button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </div>

                                {/* Visibility and Contacts */}
                                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">যোগাযোগের তথ্য ও ভিজিবিলিটি</h3>
                                    
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="email" className="block text-xs font-bold text-slate-500 uppercase">পাবলিক ইমেইল</label>
                                            <input
                                                type="email"
                                                id="email"
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                                className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="phone" className="block text-xs font-bold text-slate-500 uppercase">পাবলিক ফোন নম্বর</label>
                                            <input
                                                type="text"
                                                id="phone"
                                                value={data.phone}
                                                onChange={e => setData('phone', e.target.value)}
                                                className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            />
                                        </div>
                                    </div>

                                    <div className="pt-2 flex flex-wrap gap-4">
                                        <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                checked={data.show_email}
                                                onChange={e => setData('show_email', e.target.checked)}
                                                className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                            />
                                            <span className="text-xs font-semibold text-slate-700">প্রোফাইলে ইমেইল দেখান</span>
                                        </label>

                                        <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                checked={data.show_phone}
                                                onChange={e => setData('show_phone', e.target.checked)}
                                                className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                            />
                                            <span className="text-xs font-semibold text-slate-700">প্রোফাইলে ফোন নম্বর দেখান</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Social links */}
                        {activeFormTab === 'social' && (
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">সোশ্যাল মিডিয়া লিংকসমূহ</h3>
                                
                                <div className="grid gap-4 sm:grid-cols-2">
                                    {[
                                        { key: 'website', label: 'ব্যক্তিগত ওয়েবসাইট' },
                                        { key: 'facebook_url', label: 'ফেসবুক লিংক' },
                                        { key: 'youtube_url', label: 'ইউটিউব লিংক' },
                                        { key: 'linkedin_url', label: 'লিংকডইন লিংক' },
                                        { key: 'twitter_url', label: 'এক্স (টুইটার) লিংক' },
                                        { key: 'instagram_url', label: 'ইনস্টাগ্রাম লিংক' },
                                        { key: 'telegram_url', label: 'টেলিগ্রাম লিংক' }
                                    ].map(link => (
                                        <div key={link.key}>
                                            <label htmlFor={link.key} className="block text-xs font-bold text-slate-500 uppercase">{link.label}</label>
                                            <input
                                                type="url"
                                                id={link.key}
                                                value={data[link.key]}
                                                onChange={e => setData(link.key, e.target.value)}
                                                placeholder="https://..."
                                                className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm placeholder-slate-400"
                                            />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Tab Content: Profile settings (Admin only controls) */}
                        {activeFormTab === 'settings' && (
                            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                                <h3 className="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">প্রোফাইল সেটিংস (অ্যাডমিন কন্ট্রোল)</h3>
                                
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label htmlFor="status" className="block text-xs font-bold text-slate-500 uppercase font-semibold">স্ট্যাটাস</label>
                                        <select
                                            id="status"
                                            value={data.status}
                                            onChange={e => setData('status', e.target.value)}
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                        >
                                            <option value="active">সক্রিয় (Active)</option>
                                            <option value="pending">পেন্ডিং (Pending)</option>
                                            <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                                            <option value="rejected">বাতিলকৃত (Rejected)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label htmlFor="sort_order" className="block text-xs font-bold text-slate-500 uppercase">সাজানোর ক্রম (Sort Order)</label>
                                        <input
                                            type="number"
                                            id="sort_order"
                                            value={data.sort_order}
                                            onChange={e => setData('sort_order', parseInt(e.target.value))}
                                            className="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                        />
                                    </div>
                                </div>

                                <div className="flex flex-wrap gap-6 border-t border-slate-100 pt-4">
                                    <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            checked={data.is_verified}
                                            onChange={e => setData('is_verified', e.target.checked)}
                                            className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                        />
                                        <span className="text-sm font-bold text-slate-700">ভেরিফাইড শিক্ষক (Verified Scholar)</span>
                                    </label>

                                    <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            checked={data.featured}
                                            onChange={e => setData('featured', e.target.checked)}
                                            className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                        />
                                        <span className="text-sm font-bold text-slate-700">ফিচার্ড শিক্ষক (Featured)</span>
                                    </label>

                                    <label className="inline-flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            checked={data.allow_follow}
                                            onChange={e => setData('allow_follow', e.target.checked)}
                                            className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                        />
                                        <span className="text-sm font-bold text-slate-700">ফলো করার অনুমতি দিন</span>
                                    </label>
                                </div>
                            </div>
                        )}

                        {/* Sticky Action Button at bottom */}
                        <div className="flex items-center justify-end gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sticky bottom-4 z-10">
                            <Link 
                                href="/admin/teachers"
                                className="rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold px-5 py-2.5 transition"
                            >
                                বাতিল করুন
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-xl bg-[#102526] hover:bg-[#1A2E2F] text-white text-sm font-bold px-6 py-2.5 shadow transition disabled:opacity-50"
                            >
                                {processing ? 'সংরক্ষণ করা হচ্ছে...' : 'প্রোফাইল সংরক্ষণ করুন'}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </DashboardLayout>
    );
}
