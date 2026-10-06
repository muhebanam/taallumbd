import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';

export default function TwoFactorChallenge() {
    const [useRecovery, setUseRecovery] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        recovery_code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('two-factor.verify'), {
            onError: () => reset(),
        });
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <Head title="২-ধাপ প্রমাণীকরণ যাচাই — আত-তাআল্লুম" />

            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <div className="flex justify-center">
                    <div className="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                        ت
                    </div>
                </div>
                <h2 className="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    ২-ধাপ নিরাপত্তা যাচাইকরণ (2FA)
                </h2>
                <p className="mt-2 text-center text-xs text-slate-600 dark:text-slate-400">
                    {useRecovery
                        ? 'আপনার সংরক্ষিত ৮-সংখ্যার রিকভারি কোডগুলোর একটি প্রদান করুন।'
                        : 'আপনার অথেনটিকেশন অ্যাপে প্রদর্শিত ৬-সংখ্যার যাচাইকরণ কোডটি লিখুন।'}
                </p>
            </div>

            <div className="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
                <div className="bg-white dark:bg-slate-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-slate-200 dark:border-slate-700">
                    <form onSubmit={submit} className="space-y-6">
                        {!useRecovery ? (
                            <div>
                                <label htmlFor="code" className="block text-xs font-medium text-slate-700 dark:text-slate-300 text-center">
                                    ৬-সংখ্যার কোড (TOTP)
                                </label>
                                <input
                                    id="code"
                                    type="text"
                                    maxLength={6}
                                    placeholder="123456"
                                    value={data.code}
                                    onChange={(e) => setData('code', e.target.value.replace(/\D/g, ''))}
                                    className="mt-2 block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-center text-3xl font-mono tracking-widest py-3 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm"
                                    autoFocus
                                    required
                                />
                                {errors.code && (
                                    <p className="mt-2 text-xs text-red-600 dark:text-red-400 text-center">{errors.code}</p>
                                )}
                            </div>
                        ) : (
                            <div>
                                <label htmlFor="recovery_code" className="block text-xs font-medium text-slate-700 dark:text-slate-300 text-center">
                                    জরুরি রিকভারি কোড (যেমন: ABCD-1234)
                                </label>
                                <input
                                    id="recovery_code"
                                    type="text"
                                    placeholder="XXXX-XXXX"
                                    value={data.recovery_code}
                                    onChange={(e) => setData('recovery_code', e.target.value.toUpperCase())}
                                    className="mt-2 block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-center text-xl font-mono tracking-widest py-3 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm"
                                    autoFocus
                                    required
                                />
                                {errors.recovery_code && (
                                    <p className="mt-2 text-xs text-red-600 dark:text-red-400 text-center">{errors.recovery_code}</p>
                                )}
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={processing || (!useRecovery && data.code.length !== 6) || (useRecovery && !data.recovery_code)}
                            className="w-full py-3 px-4 rounded-xl text-white font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 transition shadow-lg shadow-emerald-600/30 text-sm"
                        >
                            {processing ? 'যাচাই করা হচ্ছে...' : 'প্রবেশ করুন'}
                        </button>

                        <div className="flex items-center justify-between text-xs pt-2">
                            <button
                                type="button"
                                onClick={() => {
                                    setUseRecovery(!useRecovery);
                                    reset();
                                }}
                                className="text-emerald-600 dark:text-emerald-400 hover:underline font-medium"
                            >
                                {useRecovery ? '← অথেনটিকেশন কোড ব্যবহার করুন' : 'অথেনটিকেটর কাজ করছে না? রিকভারি কোড ব্যবহার করুন'}
                            </button>
                        </div>
                    </form>

                    <div className="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700/60 text-center">
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            অন্য অ্যাকাউন্টে লগইন করতে লগআউট করুন
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
