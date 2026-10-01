import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

export default function Contact() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', email: '', phone: '', subject: '', message: '',
    });
    const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand';
    const submit = (e) => { e.preventDefault(); post('/contact', { onSuccess: () => reset() }); };

    return (
        <AppLayout>
            <Head title="যোগাযোগ" />
            <div className="mx-auto max-w-2xl px-4 py-12">
                <h1 className="text-3xl font-bold text-brand-deep">যোগাযোগ করুন</h1>
                <form onSubmit={submit} className="card mt-6 space-y-4 p-6">
                    <div>
                        <label className="text-sm font-medium">নাম *</label>
                        <input className={field} value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label className="text-sm font-medium">ইমেইল *</label>
                            <input type="email" className={field} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                            {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                        </div>
                        <div>
                            <label className="text-sm font-medium">ফোন</label>
                            <input className={field} value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                        </div>
                    </div>
                    <div>
                        <label className="text-sm font-medium">বিষয় *</label>
                        <input className={field} value={data.subject} onChange={(e) => setData('subject', e.target.value)} />
                        {errors.subject && <p className="mt-1 text-xs text-red-600">{errors.subject}</p>}
                    </div>
                    <div>
                        <label className="text-sm font-medium">বার্তা *</label>
                        <textarea rows={5} className={field} value={data.message} onChange={(e) => setData('message', e.target.value)} />
                        {errors.message && <p className="mt-1 text-xs text-red-600">{errors.message}</p>}
                    </div>
                    <button type="submit" disabled={processing} className="btn-primary w-full">বার্তা পাঠান</button>
                </form>
            </div>
        </AppLayout>
    );
}
