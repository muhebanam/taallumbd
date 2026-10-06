import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function TwoFactorSetup({ secret, qrUri, recoveryCodes, isMandatory }) {
    const [copied, setCopied] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        code: '',
    });

    const copySecret = () => {
        navigator.clipboard.writeText(secret);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const downloadRecoveryCodes = () => {
        const text = `আত-তাআল্লুম (Taallum BD) — ২-ধাপ যাচাইকরণ রিকভারি কোড\nতারিখ: ${new Date().toLocaleDateString()}\n\nনিচের কোডগুলো অত্যন্ত সুরক্ষিত স্থানে সংরক্ষণ করুন:\n\n${recoveryCodes.join('\n')}\n`;
        const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'taallum-recovery-codes.txt';
        link.click();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('two-factor.confirm'));
    };

    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data=${encodeURIComponent(qrUri)}`;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <Head title="২-ধাপ যাচাইকরণ (2FA) সেটআপ — আত-তাআল্লুম" />

            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <div className="flex justify-center">
                    <div className="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                        ت
                    </div>
                </div>
                <h2 className="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    ২-ধাপ যাচাইকরণ (2FA) সেটআপ
                </h2>
                {isMandatory && (
                    <p className="mt-2 text-center text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 p-2 rounded-lg border border-amber-200 dark:border-amber-800">
                        🛡️ অ্যাডমিন ও ইনস্ট্রাক্টর অ্যাকাউন্টের জন্য TOTP ২-ধাপ যাচাইকরণ বাধ্যতামূলক।
                    </p>
                )}
            </div>

            <div className="mt-6 sm:mx-auto sm:w-full sm:max-w-xl">
                <div className="bg-white dark:bg-slate-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-slate-200 dark:border-slate-700">
                    {/* Step 1: QR Code & Secret */}
                    <div className="space-y-4">
                        <div className="flex items-center space-x-2">
                            <span className="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center text-sm">১</span>
                            <h3 className="font-semibold text-slate-900 dark:text-white text-base">
                                প্রমাণীকরণ অ্যাপে কিউআর কোড স্ক্যান করুন
                            </h3>
                        </div>
                        <p className="text-xs text-slate-600 dark:text-slate-400">
                            Google Authenticator, Microsoft Authenticator অথবা 2FAS অ্যাপের মাধ্যমে স্ক্যান করুন:
                        </p>

                        <div className="flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                            <img
                                src={qrImageUrl}
                                alt="2FA QR Code"
                                className="w-48 h-48 rounded-lg shadow-sm bg-white p-2"
                            />
                            <div className="mt-4 w-full">
                                <label className="block text-xs font-medium text-slate-500 dark:text-slate-400 text-center mb-1">
                                    অথবা ম্যানুয়ালি সিক্রেট কী এন্ট্রি করুন:
                                </label>
                                <div className="flex items-center justify-center space-x-2">
                                    <code className="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 rounded font-mono text-sm tracking-widest text-emerald-700 dark:text-emerald-400 font-bold">
                                        {secret}
                                    </code>
                                    <button
                                        type="button"
                                        onClick={copySecret}
                                        className="text-xs px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded font-medium transition"
                                    >
                                        {copied ? '✓ কপি হয়েছে' : 'কপি'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Step 2: Recovery Codes */}
                    <div className="mt-8 space-y-3">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-2">
                                <span className="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center text-sm">২</span>
                                <h3 className="font-semibold text-slate-900 dark:text-white text-base">
                                    জরুরি রিকভারি কোড সংরক্ষণ
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={downloadRecoveryCodes}
                                className="text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold"
                            >
                                📥 ডাউনলোড করুন
                            </button>
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            মোবাইল হারিয়ে গেলে লগইন করতে এই কোডগুলো প্রয়োজন হবে। প্রতিটি কোড একবার ব্যবহারযোগ্য।
                        </p>
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-50 dark:bg-slate-900/40 p-3 rounded-lg border border-slate-200 dark:border-slate-700 text-center font-mono text-xs">
                            {recoveryCodes.map((code, idx) => (
                                <div key={idx} className="p-1.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold">
                                    {code}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Step 3: Confirm with 6-digit code */}
                    <form onSubmit={submit} className="mt-8 space-y-4">
                        <div className="flex items-center space-x-2">
                            <span className="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center text-sm">৩</span>
                            <h3 className="font-semibold text-slate-900 dark:text-white text-base">
                                কোড দিয়ে সক্রিয়করণ নিশ্চিত করুন
                            </h3>
                        </div>

                        <div>
                            <label htmlFor="code" className="block text-xs font-medium text-slate-700 dark:text-slate-300">
                                প্রমাণীকরণ অ্যাপে প্রদর্শিত ৬-সংখ্যার কোড লিখুন:
                            </label>
                            <input
                                id="code"
                                type="text"
                                maxLength={6}
                                placeholder="123456"
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value.replace(/\D/g, ''))}
                                className="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-center text-2xl font-mono tracking-widest py-3 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm"
                                autoFocus
                                required
                            />
                            {errors.code && (
                                <p className="mt-1 text-xs text-red-600 dark:text-red-400 text-center">{errors.code}</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing || data.code.length !== 6}
                            className="w-full py-3 px-4 rounded-xl text-white font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 transition shadow-lg shadow-emerald-600/30 text-sm"
                        >
                            {processing ? 'যাচাই করা হচ্ছে...' : '২-ধাপ যাচাইকরণ সক্রিয় করুন'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
