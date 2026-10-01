import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

const field = 'mt-1 block w-full rounded-xl border-brand/20 text-sm focus:border-brand focus:ring-brand';

export default function BecomeInstructor({ stats, existingApplication, auth }) {
    const isLoggedIn = !!auth?.user;

    const { data, setData, post, processing, errors, reset } = useForm({
        name: auth?.user?.name ?? '',
        email: auth?.user?.email ?? '',
        phone: auth?.user?.phone ?? '',
        expertise: '',
        experience: '',
        cv_link: ''
    });

    const submit = (e) => {
        e.preventDefault();
        post('/become-instructor', {
            onSuccess: () => reset('expertise', 'experience', 'cv_link')
        });
    };

    return (
        <AppLayout>
            <Head title="শিক্ষক হিসেবে যোগ দিন" />

            {/* Hero Section */}
            <section className="bg-brand-deep text-white py-16 px-4 hero-pattern relative overflow-hidden">
                <div className="mx-auto max-w-4xl text-center space-y-4">
                    <span className="rounded-full bg-brand-cream/15 px-4 py-1.5 text-xs font-semibold text-brand-cream tracking-wide">শিক্ষক প্যানেল</span>
                    <h1 className="text-3xl font-extrabold text-brand-cream md:text-5xl leading-tight">
                        জ্ঞানের আলো ছড়াতে আমাদের শিক্ষক প্যানেলে যোগ দিন
                    </h1>
                    <p className="mx-auto max-w-2xl text-sm md:text-base text-white/80 leading-relaxed">
                        আত-তাআল্লুম প্ল্যাটফর্মে ইসলামের সঠিক দাওয়াত ও শিক্ষা পৌঁছে দিতে অবদান রাখুন। আপনার অভিজ্ঞতা ও জ্ঞান ছড়িয়ে দিন হাজারো শিক্ষার্থীর মাঝে।
                    </p>
                </div>
            </section>

            {/* Dynamic Statistics Grid */}
            <section className="mx-auto max-w-7xl px-4 py-12">
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="card p-6 flex flex-col items-center justify-center text-center space-y-1">
                        <span className="text-3xl">👥</span>
                        <span className="text-2xl font-bold text-brand-deep">{stats.total_students}</span>
                        <span className="text-xs text-brand-text/60 font-semibold">নিবন্ধিত শিক্ষার্থী</span>
                    </div>
                    <div className="card p-6 flex flex-col items-center justify-center text-center space-y-1">
                        <span className="text-3xl">🎓</span>
                        <span className="text-2xl font-bold text-brand-deep">{stats.active_courses}</span>
                        <span className="text-xs text-brand-text/60 font-semibold">সক্রিয় কোর্সসমূহ</span>
                    </div>
                    <div className="card p-6 flex flex-col items-center justify-center text-center space-y-1">
                        <span className="text-3xl">🎬</span>
                        <span className="text-2xl font-bold text-brand-deep">{stats.published_lessons}</span>
                        <span className="text-xs text-brand-text/60 font-semibold">সর্বমোট পাঠ (Lessons)</span>
                    </div>
                    <div className="card p-6 flex flex-col items-center justify-center text-center space-y-1">
                        <span className="text-3xl">🤝</span>
                        <span className="text-2xl font-bold text-brand-deep">{stats.support_label}</span>
                        <span className="text-xs text-brand-text/60 font-semibold">২৪/৭ অনলাইন সাপোর্ট</span>
                    </div>
                </div>
            </section>

            {/* Form and Instructions */}
            <section className="mx-auto max-w-5xl px-4 pb-20">
                <div className="grid gap-10 md:grid-cols-5">
                    <div className="md:col-span-2 space-y-6">
                        <div className="card p-6 space-y-4 bg-brand-cream/10 border border-brand/10">
                            <h3 className="text-lg font-bold text-brand-deep">শিক্ষক হওয়ার যোগ্যতা:</h3>
                            <ul className="space-y-3 text-sm text-brand-text/80 leading-relaxed">
                                <li className="flex items-start gap-2">
                                    <span className="text-brand font-bold mt-0.5">✔</span>
                                    ইসলামী শিক্ষায় স্বীকৃত প্রতিষ্ঠান (যেমন: কওমি বা আলিয়া মাদ্রাসা/বিশ্ববিদ্যালয়) থেকে টাইটেল/ডিগ্রিধারী।
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-brand font-bold mt-0.5">✔</span>
                                    কমপক্ষে ১-২ বছরের শিক্ষকতার বাস্তব অভিজ্ঞতা।
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-brand font-bold mt-0.5">✔</span>
                                    অনলাইন বা দূরশিক্ষণ (Distance Learning) প্রক্রিয়ায় ক্লাস পরিচালনায় দক্ষতা।
                                </li>
                                <li className="flex items-start gap-2">
                                    <span className="text-brand font-bold mt-0.5">✔</span>
                                    শিক্ষার্থীদের দ্বীন শেখানোর ক্ষেত্রে বিশুদ্ধ আকিদা ও আমল রক্ষা করা।
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="md:col-span-3">
                        {existingApplication && existingApplication.status === 'pending' ? (
                            <div className="card p-8 border border-amber-200 bg-amber-50/30 text-center space-y-4">
                                <span className="text-5xl">⏳</span>
                                <h3 className="text-xl font-bold text-amber-800">আবেদনটি পেন্ডিং রয়েছে</h3>
                                <p className="text-sm text-brand-text/85 leading-relaxed max-w-md mx-auto">
                                    ইমেইল: <span className="font-semibold">{existingApplication.email}</span> দিয়ে ইতিমধ্যে একটি আবেদন জমা আছে। আমাদের টিম তথ্যসমূহ যাচাই করে আপনার সাথে দ্রুত যোগাযোগ করবে, ইনশাআল্লাহ।
                                </p>
                            </div>
                        ) : existingApplication && existingApplication.status === 'approved' ? (
                            <div className="card p-8 border border-green-200 bg-green-50/30 text-center space-y-4">
                                <span className="text-5xl">🎉</span>
                                <h3 className="text-xl font-bold text-green-800">আবেদন অনুমোদিত হয়েছে!</h3>
                                <p className="text-sm text-brand-text/85 leading-relaxed max-w-md mx-auto">
                                    অভিনন্দন! শিক্ষক প্যানেলে আপনাকে স্বাগতম। আপনি এখন ইন্সট্রাক্টর প্যানেলে গিয়ে কোর্স তৈরি ও পরিচালনা করতে পারবেন।
                                </p>
                                <div className="pt-2">
                                    <Link href="/instructor/dashboard" className="btn-primary inline-block">ইন্সট্রাক্টর ড্যাশবোর্ড</Link>
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={submit} className="card p-8 space-y-6">
                                <h3 className="text-xl font-bold text-brand-deep border-b border-brand/5 pb-3">নিবন্ধন ফরম</h3>

                                {existingApplication && existingApplication.status === 'rejected' && (
                                    <div className="p-4 rounded-xl border border-red-200 bg-red-50/50 text-red-800 text-xs leading-relaxed">
                                        ⚠️ আপনার পূর্ববর্তী আবেদনটি অনুমোদিত হয়নি। অনুগ্রহ করে প্রয়োজনীয় সংশোধনসহ পুনরায় আবেদন করুন।
                                        {existingApplication.admin_notes && (
                                            <p className="mt-1 font-semibold text-brand-text">অ্যাডমিন নোট: {existingApplication.admin_notes}</p>
                                        )}
                                    </div>
                                )}

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className="text-xs font-semibold text-brand-text/75">আপনার নাম *</label>
                                        <input className={field} value={data.name} onChange={(e) => setData('name', e.target.value)} required disabled={isLoggedIn} />
                                        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                                    </div>
                                    <div>
                                        <label className="text-xs font-semibold text-brand-text/75">ইমেইল ঠিকানা *</label>
                                        <input type="email" className={field} value={data.email} onChange={(e) => setData('email', e.target.value)} required disabled={isLoggedIn} />
                                        {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                                    </div>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className="text-xs font-semibold text-brand-text/75">মোবাইল নম্বর *</label>
                                        <input className={field} placeholder="যেমন: +8801xxxxxxxxx" value={data.phone} onChange={(e) => setData('phone', e.target.value)} required />
                                        {errors.phone && <p className="mt-1 text-xs text-red-600">{errors.phone}</p>}
                                    </div>
                                    <div>
                                        <label className="text-xs font-semibold text-brand-text/75">দক্ষতার ক্ষেত্র *</label>
                                        <input className={field} placeholder="যেমন: তাফসির, হাদিস, আরবি ভাষা" value={data.expertise} onChange={(e) => setData('expertise', e.target.value)} required />
                                        {errors.expertise && <p className="mt-1 text-xs text-red-600">{errors.expertise}</p>}
                                    </div>
                                </div>

                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">শিক্ষকতা ও দাওয়াহ কাজের বিবরণ *</label>
                                    <textarea rows={5} className={field} placeholder="আপনার মাদরাসা/বিশ্ববিদ্যালয়ের শিক্ষকতার অভিজ্ঞতা বা দাওয়াহ সংশ্লিষ্ট বিষয়ের বর্ণনা দিন।" value={data.experience} onChange={(e) => setData('experience', e.target.value)} required />
                                    {errors.experience && <p className="mt-1 text-xs text-red-600">{errors.experience}</p>}
                                </div>

                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">সিভি (CV) / পোর্টফোলিও লিংক</label>
                                    <input type="url" className={field} placeholder="যেমন: Google Drive লিংক" value={data.cv_link} onChange={(e) => setData('cv_link', e.target.value)} />
                                    {errors.cv_link && <p className="mt-1 text-xs text-red-600">{errors.cv_link}</p>}
                                </div>

                                <button type="submit" disabled={processing} className="btn-primary w-full py-3">
                                    আবেদন জমা দিন
                                </button>
                            </form>
                        )}
                    </div>
                </div>
            </section>
        </AppLayout>
    );
}
