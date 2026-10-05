import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function PaymentSettings({ paymentSettings }) {
    const { data, setData, post, processing, recentlySuccessful } = useForm({
        settings: paymentSettings || {
            bkash: { number: '01700000000', type: 'personal', instructions: '', enabled: true },
            nagad: { number: '01700000000', type: 'personal', instructions: '', enabled: true },
            rocket: { number: '01700000000', type: 'personal', instructions: '', enabled: true },
        },
    });

    const handleChange = (method, field, value) => {
        setData('settings', {
            ...data.settings,
            [method]: {
                ...data.settings[method],
                [field]: value,
            },
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings/payments', {
            preserveScroll: true,
        });
    };

    const methodLabels = {
        bkash: { name: 'bKash (বিকাশ)', color: 'border-pink-500 text-pink-600', badge: 'bg-pink-50 text-pink-700' },
        nagad: { name: 'Nagad (নগদ)', color: 'border-orange-500 text-orange-600', badge: 'bg-orange-50 text-orange-700' },
        rocket: { name: 'Rocket (রকেট)', color: 'border-purple-500 text-purple-600', badge: 'bg-purple-50 text-purple-700' },
    };

    return (
        <DashboardLayout title="পেমেন্ট গেটওয়ে কনফিগারেশন">
            <Head title="পেমেন্ট সেটিংস — অ্যাডমিন" />

            <div className="max-w-4xl mx-auto space-y-6">
                <div className="card p-6 border-b border-gray-100">
                    <h2 className="text-xl font-bold text-gray-900">ম্যানুয়াল পেমেন্ট নম্বর ও নির্দেশনা সেটিংস</h2>
                    <p className="text-sm text-gray-500 mt-1">
                        শিক্ষার্থীরা কোর্স কেনার সময় এই নম্বরগুলো দেখতে পাবে এবং সরাসরি Send Money করে TrxID জমা দেবে।
                    </p>
                </div>

                {recentlySuccessful && (
                    <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium flex items-center gap-2">
                        <span>✓</span>
                        পেমেন্ট গেটওয়ে সেটিংস সফলভাবে সংরক্ষিত হয়েছে।
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {Object.entries(data.settings).map(([key, setting]) => {
                        const meta = methodLabels[key] || { name: key.toUpperCase(), color: 'border-teal-500', badge: 'bg-teal-50 text-teal-700' };

                        return (
                            <div key={key} className="card p-6 border-l-4 rounded-xl shadow-sm space-y-4" style={{ borderLeftColor: key === 'bkash' ? '#e2136e' : key === 'nagad' ? '#f7941d' : '#8c3494' }}>
                                <div className="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <div className="flex items-center gap-3">
                                        <h3 className="text-lg font-bold text-gray-900">{meta.name}</h3>
                                        <span className={`text-xs px-2.5 py-0.5 rounded-full font-semibold ${meta.badge}`}>
                                            {setting.type === 'personal' ? 'ব্যক্তিগত (Personal)' : setting.type === 'merchant' ? 'মার্চেন্ট (Merchant)' : 'এজেন্ট'}
                                        </span>
                                    </div>
                                    <label className="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                                        <input
                                            type="checkbox"
                                            checked={setting.enabled}
                                            onChange={(e) => handleChange(key, 'enabled', e.target.checked)}
                                            className="rounded border-gray-300 text-[#102526] focus:ring-[#102526] h-4 w-4"
                                        />
                                        <span>চালু রাখুন</span>
                                    </label>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 mb-1">
                                            রিসিভিং মোবাইল নম্বর <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={setting.number}
                                            onChange={(e) => handleChange(key, 'number', e.target.value)}
                                            placeholder="01XXXXXXXXX"
                                            className="input w-full text-sm border-gray-300 rounded-lg focus:border-[#102526] focus:ring-[#102526]"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 mb-1">
                                            অ্যাকাউন্ট টাইপ <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={setting.type}
                                            onChange={(e) => handleChange(key, 'type', e.target.value)}
                                            className="select w-full text-sm border-gray-300 rounded-lg focus:border-[#102526] focus:ring-[#102526]"
                                        >
                                            <option value="personal">Personal (সেন্ড মানি)</option>
                                            <option value="merchant">Merchant (পেমেন্ট)</option>
                                            <option value="agent">Agent (ক্যাশ ইন)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 mb-1">
                                        শিক্ষার্থীদের জন্য বিশেষ নির্দেশনা
                                    </label>
                                    <textarea
                                        rows="2"
                                        value={setting.instructions}
                                        onChange={(e) => handleChange(key, 'instructions', e.target.value)}
                                        placeholder="পেমেন্ট করার নির্দিষ্ট নিয়ম বা বার্তা..."
                                        className="input w-full text-sm border-gray-300 rounded-lg focus:border-[#102526] focus:ring-[#102526]"
                                    ></textarea>
                                </div>
                            </div>
                        );
                    })}

                    <div className="flex justify-end gap-3 pt-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="btn btn-primary px-6 py-2.5 bg-[#102526] hover:bg-[#1A2E2F] text-white rounded-lg font-semibold text-sm shadow transition-colors disabled:opacity-50"
                        >
                            {processing ? 'সংরক্ষণ হচ্ছে...' : 'সেটিংস সংরক্ষণ করুন'}
                        </button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
