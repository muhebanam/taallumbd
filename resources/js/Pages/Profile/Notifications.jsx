import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function Notifications({ types, channels, preferences }) {
    const initial = Object.fromEntries(Object.entries(types).map(([type]) => [
        type,
        Object.fromEntries(Object.keys(channels).map((channel) => [
            channel,
            preferences?.[type]?.[channel] ?? true,
        ])),
    ]));
    const { data, setData, put, processing } = useForm({ preferences: initial });
    const toggle = (type, channel) => setData('preferences', {
        ...data.preferences,
        [type]: { ...data.preferences[type], [channel]: !data.preferences[type][channel] },
    });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">নোটিফিকেশন পছন্দ</h2>}>
            <Head title="নোটিফিকেশন পছন্দ" />
            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
                <div className="overflow-hidden rounded-2xl bg-white p-4 shadow sm:p-8">
                    <p className="mb-6 max-w-2xl text-sm leading-6 text-slate-600">কোন ধরনের বার্তা কোন মাধ্যমে পেতে চান, তা এখান থেকে নিয়ন্ত্রণ করুন। ওয়েব নোটিফিকেশন বন্ধ করলে হেডারের বেলেও তা দেখা যাবে না।</p>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[34rem] text-left text-sm">
                            <thead><tr className="border-b border-slate-200 text-slate-500"><th className="px-3 py-3">নোটিফিকেশনের ধরন</th>{Object.entries(channels).map(([channel, label]) => <th key={channel} className="px-3 py-3 text-center">{label}</th>)}</tr></thead>
                            <tbody>{Object.entries(types).map(([type, label]) => <tr key={type} className="border-b border-slate-100 last:border-0"><td className="px-3 py-4 font-semibold text-slate-800">{label}</td>{Object.keys(channels).map((channel) => <td key={channel} className="px-3 py-4 text-center"><input type="checkbox" checked={!!data.preferences[type]?.[channel]} onChange={() => toggle(type, channel)} className="h-5 w-5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" /></td>)}</tr>)}</tbody>
                        </table>
                    </div>
                    <button disabled={processing} onClick={() => put('/profile/notifications')} className="mt-6 rounded-xl bg-emerald-800 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-900 disabled:opacity-50">পছন্দ সংরক্ষণ করুন</button>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
