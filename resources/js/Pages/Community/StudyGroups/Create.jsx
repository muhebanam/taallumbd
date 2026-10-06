import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function StudyGroupCreate({ courses = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        type: 'public',
        course_id: '',
        max_members: 100,
        weekly_goal: '',
        weekly_goal_target: 100,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/community/groups');
    };

    return (
        <MainLayout>
            <Head title="নতুন স্টাডি গ্রুপ তৈরি করুন — আত-তাআল্লুম" />

            <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <Link
                        href="/community/groups"
                        className="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"
                    >
                        ← সকল গ্রুপে ফিরে যান
                    </Link>
                    <h1 className="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                        নতুন ইসলামিক স্টাডি গ্রুপ শুরু করুন
                    </h1>
                    <p className="mt-1 text-sm text-gray-600">
                        নির্দিষ্ট সিলেবাস বা দ্বীনি গবেষণার জন্য সহপাঠীদের সাথে হালাকা গড়ে তুলুন
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-800">
                            গ্রুপের নাম <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="যেমন: ফিকহুস সুন্নাহ তাহকীক হালাকা"
                            className="mt-2 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                            required
                        />
                        {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-800">
                            গ্রুপের বিবরণ ও নিয়মাবলি
                        </label>
                        <textarea
                            rows={3}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="এই গ্রুপের মূল উদ্দেশ্য, আলোচনার বিষয়বস্তু ও শৃঙ্খলা সংক্রান্ত বিবরণ..."
                            className="mt-2 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                        />
                        {errors.description && <p className="mt-1 text-xs text-red-500">{errors.description}</p>}
                    </div>

                    {/* Group Type */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-800">
                            গ্রুপের ধরন (Privacy Level) <span className="text-red-500">*</span>
                        </label>
                        <div className="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label className={`flex flex-col p-3 rounded-xl border cursor-pointer transition ${
                                data.type === 'public' ? 'border-[#102526] bg-[#102526]/5 ring-1 ring-[#102526]' : 'border-gray-200'
                            }`}>
                                <input
                                    type="radio"
                                    name="type"
                                    value="public"
                                    checked={data.type === 'public'}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="sr-only"
                                />
                                <span className="text-xs font-bold text-gray-900">উন্মুক্ত (Public)</span>
                                <span className="mt-1 text-[11px] text-gray-500">যে-কেউ যুক্ত হতে পারবে ও আলোচনা দেখতে পারবে</span>
                            </label>

                            <label className={`flex flex-col p-3 rounded-xl border cursor-pointer transition ${
                                data.type === 'private' ? 'border-[#102526] bg-[#102526]/5 ring-1 ring-[#102526]' : 'border-gray-200'
                            }`}>
                                <input
                                    type="radio"
                                    name="type"
                                    value="private"
                                    checked={data.type === 'private'}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="sr-only"
                                />
                                <span className="text-xs font-bold text-gray-900">প্রাইভেট (Private)</span>
                                <span className="mt-1 text-[11px] text-gray-500">শুধুমাত্র ইনভাইট লিংক দিয়ে যুক্ত হওয়া যাবে</span>
                            </label>

                            <label className={`flex flex-col p-3 rounded-xl border cursor-pointer transition ${
                                data.type === 'course_linked' ? 'border-[#102526] bg-[#102526]/5 ring-1 ring-[#102526]' : 'border-gray-200'
                            }`}>
                                <input
                                    type="radio"
                                    name="type"
                                    value="course_linked"
                                    checked={data.type === 'course_linked'}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="sr-only"
                                />
                                <span className="text-xs font-bold text-gray-900">কোর্স-লিংকড</span>
                                <span className="mt-1 text-[11px] text-gray-500">নির্দিষ্ট কোর্সের ছাত্ররা স্বয়ংক্রিয়ভাবে অ্যাক্সেস পাবে</span>
                            </label>
                        </div>
                        {errors.type && <p className="mt-1 text-xs text-red-500">{errors.type}</p>}
                    </div>

                    {/* Linked Course (if course_linked) */}
                    {data.type === 'course_linked' && (
                        <div>
                            <label className="block text-sm font-semibold text-gray-800">
                                সংশ্লিষ্ট কোর্স নির্ধারণ করুন <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.course_id}
                                onChange={(e) => setData('course_id', e.target.value)}
                                className="mt-2 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                                required
                            >
                                <option value="">কোর্স নির্বাচন করুন</option>
                                {courses.map((c) => (
                                    <option key={c.id} value={c.id}>{c.title}</option>
                                ))}
                            </select>
                            {errors.course_id && <p className="mt-1 text-xs text-red-500">{errors.course_id}</p>}
                        </div>
                    )}

                    {/* Weekly Goal */}
                    <div className="rounded-xl bg-gray-50 p-4 border border-gray-200 space-y-4">
                        <div>
                            <label className="block text-sm font-semibold text-gray-800">
                                সাপ্তাহিক পাঠ্য লক্ষ্য (Weekly Goal)
                            </label>
                            <p className="text-xs text-gray-500">সদস্যরা একসাথে এই লক্ষ্য পূরণের অগ্রগতি ট্র্যাকিং করবে</p>
                            <input
                                type="text"
                                value={data.weekly_goal}
                                onChange={(e) => setData('weekly_goal', e.target.value)}
                                placeholder="যেমন: সূরা মুলক ১-৩০ আয়াত তাদাব্বুর অথবা সেকশন ৩ লেকচার সম্পন্ন"
                                className="mt-2 w-full rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                            />
                        </div>
                    </div>

                    {/* Max Members */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-800">
                            সর্বোচ্চ সদস্য সংখ্যা
                        </label>
                        <input
                            type="number"
                            min="2"
                            max="500"
                            value={data.max_members}
                            onChange={(e) => setData('max_members', parseInt(e.target.value) || 100)}
                            className="mt-2 w-36 rounded-xl border-gray-300 shadow-sm focus:border-[#102526] focus:ring-[#102526] text-sm"
                        />
                    </div>

                    <div className="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <Link
                            href="/community/groups"
                            className="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            বাতিল
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-xl bg-[#102526] px-6 py-2.5 text-sm font-bold text-white shadow transition hover:bg-[#1A2E2F] disabled:opacity-50"
                        >
                            {processing ? 'তৈরি হচ্ছে...' : 'গ্রুপ তৈরি করুন'}
                        </button>
                    </div>
                </form>
            </div>
        </MainLayout>
    );
}
