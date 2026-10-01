import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '', email: '', phone: '', password: '', password_confirmation: '',
    });
    const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand';
    const submit = (e) => { e.preventDefault(); post('/register'); };

    return (
        <AppLayout>
            <Head title="রেজিস্ট্রেশন" />
            <div className="mx-auto max-w-md px-4 py-16">
                <div className="card p-8">
                    <img src="/images/logo.png" alt="" className="mx-auto h-16 w-16 rounded-full object-contain" />
                    <h1 className="mt-4 text-center text-2xl font-bold text-brand-deep">রেজিস্ট্রেশন করুন</h1>
                    <form onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <label className="text-sm font-medium">নাম</label>
                            <input className={field} value={data.name} onChange={(e) => setData('name', e.target.value)} autoFocus />
                            {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="text-sm font-medium">ইমেইল</label>
                            <input type="email" className={field} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                            {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                        </div>
                        <div>
                            <label className="text-sm font-medium">ফোন (ঐচ্ছিক)</label>
                            <input className={field} value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                        </div>
                        <div>
                            <label className="text-sm font-medium">পাসওয়ার্ড</label>
                            <input type="password" className={field} value={data.password} onChange={(e) => setData('password', e.target.value)} />
                            {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                        </div>
                        <div>
                            <label className="text-sm font-medium">পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" className={field} value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} />
                        </div>
                        <button type="submit" disabled={processing} className="btn-primary w-full">রেজিস্ট্রেশন</button>
                    </form>
                    <p className="mt-4 text-center text-sm text-brand-text/70">
                        আগে থেকেই অ্যাকাউন্ট আছে? <Link href="/login" className="font-semibold text-brand hover:underline">লগইন করুন</Link>
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
