import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * Role tabs are a VISUAL affordance only — they do not gate anything.
 * The backend (AuthenticatedSessionController::store) always redirects
 * by the authenticated user's real `role` column, regardless of which
 * tab was selected here. That's why each tab's subtext says so.
 */
const ROLE_TABS = [
    {
        key: 'student',
        label: 'শিক্ষার্থী হিসেবে',
        subtext: 'কোর্স চালিয়ে যান, কুইজ দিন, সার্টিফিকেট নিন',
        icon: (props) => (
            <svg {...props} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4 2 9l10 5 10-5-10-5Z" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M6 11.5V17c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" />
            </svg>
        ),
    },
    {
        key: 'instructor',
        label: 'শিক্ষক হিসেবে',
        subtext: 'কোর্স, কুইজ ও শিক্ষার্থী পরিচালনা করুন',
        icon: (props) => (
            <svg {...props} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 19.5V6a2 2 0 0 1 2-2h11.5A1.5 1.5 0 0 1 19 5.5V17H6a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h13" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M8 7h8M8 10.5h8" />
            </svg>
        ),
    },
    {
        key: 'admin',
        label: 'অ্যাডমিন হিসেবে',
        subtext: 'পুরো প্ল্যাটফর্ম নিয়ন্ত্রণ করুন',
        icon: (props) => (
            <svg {...props} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 3 4.5 6v5.5c0 4.6 3.2 8.4 7.5 9.5 4.3-1.1 7.5-4.9 7.5-9.5V6L12 3Z" />
                <path strokeLinecap="round" strokeLinejoin="round" d="m9 12 2 2 4-4" />
            </svg>
        ),
    },
];

const SLIDES = [
    'সহীহ ইলম, সহজ পদ্ধতিতে',
    'কুরআন, হাদীস ও ফিকহ শিখুন এক প্ল্যাটফর্মে',
    'অভিজ্ঞ মুফতী ও উস্তাযদের তত্ত্বাবধানে',
    'যেকোনো সময়, যেকোনো স্থান থেকে',
];

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });
    const [activeTab, setActiveTab] = useState('student');
    const [slide, setSlide] = useState(0);

    useEffect(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const t = setInterval(() => setSlide((s) => (s + 1) % SLIDES.length), 4000);
        return () => clearInterval(t);
    }, []);

    const submit = (e) => { e.preventDefault(); post('/login'); };
    const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand';
    const active = ROLE_TABS.find((t) => t.key === activeTab);

    return (
        <div className="flex min-h-screen flex-col lg:flex-row">
            <Head title="লগইন" />

            {/* Left — brand / illustration panel */}
            <div className="hero-pattern relative flex shrink-0 flex-col justify-between overflow-hidden px-8 py-10 text-white lg:w-[44%] lg:px-14 lg:py-14">
                <Link href="/" className="relative z-10 flex w-fit items-center gap-2 text-sm text-white/70 transition hover:text-brand-cream">
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7 7-7M3 12h18" /></svg>
                    ওয়েবসাইটে ফিরুন
                </Link>

                {/* ambient rotating star lattice — signature element, echoes the homepage hero */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -right-24 -top-24 h-[420px] w-[420px] opacity-[0.08] motion-safe:animate-[spin_90s_linear_infinite] lg:h-[560px] lg:w-[560px]"
                    style={{
                        backgroundImage:
                            "url(\"data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23FFF99A' stroke-width='1.5'%3E%3Cpath d='M32 4l8 20 20 8-20 8-8 20-8-20-20-8 20-8z'/%3E%3C/g%3E%3C/svg%3E\")",
                        backgroundSize: '64px 64px',
                    }}
                />

                <div className="relative z-10 my-auto max-w-sm">
                    <img src="/images/logo.png" alt="আত-তাআল্লুম" className="h-16 w-16 rounded-full bg-white/10 object-contain" />
                    <h1 className="mt-6 text-3xl font-bold text-brand-cream">আত-তাআল্লুম</h1>
                    <p className="mt-2 text-white/80">দ্বীন শেখার অনলাইন শিক্ষা প্ল্যাটফর্ম</p>

                    <div className="mt-10 h-14">
                        {SLIDES.map((text, i) => (
                            <p
                                key={text}
                                className={`absolute text-lg font-medium leading-snug text-white/90 transition-all duration-700 ${i === slide ? 'translate-y-0 opacity-100' : 'translate-y-2 opacity-0'}`}
                            >
                                {text}
                            </p>
                        ))}
                    </div>
                    <div className="mt-4 flex gap-1.5">
                        {SLIDES.map((_, i) => (
                            <span key={i} className={`h-1.5 rounded-full transition-all ${i === slide ? 'w-6 bg-brand-cream' : 'w-1.5 bg-white/25'}`} />
                        ))}
                    </div>
                </div>

                <p className="relative z-10 text-xs text-white/40">© {new Date().getFullYear()} আত-তাআল্লুম</p>
            </div>

            {/* Right — login card */}
            <div className="flex flex-1 items-center justify-center bg-brand-light px-4 py-12">
                <div className="w-full max-w-md">
                    <h2 className="text-2xl font-bold text-brand-deep">লগইন করুন</h2>
                    <p className="mt-1 text-sm text-brand-text/60">আপনার ভূমিকা বেছে নিন — লগইনের পর সঠিক ড্যাশবোর্ডে নিয়ে যাওয়া হবে।</p>

                    {/* Role tabs — cosmetic only; real role always comes from the account itself */}
                    <div className="mt-5 grid grid-cols-3 gap-2">
                        {ROLE_TABS.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveTab(tab.key)}
                                className={`flex flex-col items-center gap-1.5 rounded-xl border px-2 py-3 text-xs font-medium transition ${
                                    activeTab === tab.key
                                        ? 'border-brand bg-brand text-brand-cream shadow-card'
                                        : 'border-brand/15 bg-white text-brand-text/70 hover:border-brand/30'
                                }`}
                            >
                                <tab.icon className="h-5 w-5" />
                                {tab.label}
                            </button>
                        ))}
                    </div>
                    <p className="mt-2 text-xs text-brand-text/50">{active?.subtext}</p>

                    <form onSubmit={submit} className="card mt-6 space-y-4 p-6">
                        <div>
                            <label className="text-sm font-medium">ইমেইল</label>
                            <input type="email" className={field} value={data.email} onChange={(e) => setData('email', e.target.value)} autoFocus />
                            {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                        </div>
                        <div>
                            <div className="flex items-center justify-between">
                                <label className="text-sm font-medium">পাসওয়ার্ড</label>
                                <span title="শীঘ্রই আসছে" className="cursor-not-allowed text-xs text-brand-text/35">পাসওয়ার্ড ভুলে গেছেন?</span>
                            </div>
                            <input type="password" className={field} value={data.password} onChange={(e) => setData('password', e.target.value)} />
                            {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                        </div>
                        <label className="flex items-center gap-2 text-sm">
                            <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} />
                            আমাকে মনে রাখুন
                        </label>
                        <button type="submit" disabled={processing} className="btn-primary w-full">লগইন</button>
                    </form>

                    <p className="mt-4 text-center text-sm text-brand-text/70">
                        অ্যাকাউন্ট নেই? <Link href="/register" className="font-semibold text-brand hover:underline">রেজিস্ট্রেশন করুন</Link>
                    </p>
                </div>
            </div>
        </div>
    );
}
