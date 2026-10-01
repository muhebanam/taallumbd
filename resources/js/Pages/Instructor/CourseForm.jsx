import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import DashboardLayout from '../../Layouts/DashboardLayout';

const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand';

function SectionForm({ course }) {
    const { data, setData, post, processing, reset } = useForm({ title: '' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(`/instructor/courses/${course.slug}/sections`, { onSuccess: () => reset() }); }} className="flex gap-2">
            <input className="flex-1 rounded-xl border-brand/20 text-sm focus:border-brand focus:ring-brand" placeholder="নতুন সেকশনের শিরোনাম" value={data.title} onChange={(e) => setData('title', e.target.value)} />
            <button type="submit" disabled={processing} className="btn-primary !px-4 !py-2 text-sm">যোগ করুন</button>
        </form>
    );
}

function LessonForm({ course }) {
    const { data, setData, post, processing, reset } = useForm({ section_id: '', title: '', content: '', video_url: '', is_preview: false });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(`/instructor/courses/${course.slug}/lessons`, { onSuccess: () => reset() }); }} className="space-y-3">
            <select className={field} value={data.section_id} onChange={(e) => setData('section_id', e.target.value)}>
                <option value="">সেকশন নির্বাচন (ঐচ্ছিক)</option>
                {course.sections?.map((s) => <option key={s.id} value={s.id}>{s.title}</option>)}
            </select>
            <input className={field} placeholder="পাঠের শিরোনাম" value={data.title} onChange={(e) => setData('title', e.target.value)} />
            <input className={field} placeholder="ভিডিও URL (YouTube)" value={data.video_url} onChange={(e) => setData('video_url', e.target.value)} />
            <textarea rows={3} className={field} placeholder="পাঠের বিবরণ" value={data.content} onChange={(e) => setData('content', e.target.value)} />
            <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={data.is_preview} onChange={(e) => setData('is_preview', e.target.checked)} />
                ফ্রি প্রিভিউ হিসেবে উন্মুক্ত
            </label>
            <button type="submit" disabled={processing} className="btn-primary !py-2 text-sm">পাঠ যোগ করুন</button>
        </form>
    );
}

function QuizForm({ course }) {
    const empty = { question: '', type: 'single_choice', marks: 1, options: [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }] };
    const { data, setData, post, processing, reset } = useForm({ title: '', description: '', pass_marks: 1, lesson_id: '', questions: [structuredClone(empty)] });

    const setQ = (i, key, val) => {
        const qs = structuredClone(data.questions); qs[i][key] = val; setData('questions', qs);
    };
    const setOpt = (i, j, key, val) => {
        const qs = structuredClone(data.questions); qs[i].options[j][key] = val; setData('questions', qs);
    };

    return (
        <form onSubmit={(e) => { e.preventDefault(); post(`/instructor/courses/${course.slug}/quizzes`, { onSuccess: () => reset() }); }} className="space-y-3">
            <input className={field} placeholder="কুইজের শিরোনাম" value={data.title} onChange={(e) => setData('title', e.target.value)} />
            <div className="flex gap-3">
                <input type="number" min="0" className={`${field} !mt-0 w-32`} placeholder="পাস মার্ক" value={data.pass_marks} onChange={(e) => setData('pass_marks', e.target.value)} />
                <select className={`${field} !mt-0 flex-1`} value={data.lesson_id} onChange={(e) => setData('lesson_id', e.target.value)}>
                    <option value="">কোর্স-লেভেল কুইজ</option>
                    {course.sections?.flatMap((s) => s.lessons).map((l) => <option key={l.id} value={l.id}>{l.title}</option>)}
                </select>
            </div>
            {data.questions.map((q, i) => (
                <div key={i} className="rounded-xl border border-brand/15 p-3">
                    <input className={field} placeholder={`প্রশ্ন ${i + 1}`} value={q.question} onChange={(e) => setQ(i, 'question', e.target.value)} />
                    <div className="mt-2 flex gap-2">
                        <select className="rounded-lg border-brand/20 text-sm" value={q.type} onChange={(e) => setQ(i, 'type', e.target.value)}>
                            <option value="single_choice">একক উত্তর</option>
                            <option value="multiple_choice">একাধিক উত্তর</option>
                            <option value="true_false">সত্য/মিথ্যা</option>
                        </select>
                        <input type="number" min="1" className="w-24 rounded-lg border-brand/20 text-sm" value={q.marks} onChange={(e) => setQ(i, 'marks', Number(e.target.value))} />
                    </div>
                    {q.options.map((opt, j) => (
                        <div key={j} className="mt-2 flex items-center gap-2">
                            <input type="checkbox" title="সঠিক উত্তর" className="rounded border-brand/30 text-brand focus:ring-brand" checked={opt.is_correct} onChange={(e) => setOpt(i, j, 'is_correct', e.target.checked)} />
                            <input className="flex-1 rounded-lg border-brand/20 text-sm" placeholder={`অপশন ${j + 1}`} value={opt.option_text} onChange={(e) => setOpt(i, j, 'option_text', e.target.value)} />
                        </div>
                    ))}
                    <button type="button" className="mt-2 text-sm font-semibold text-brand hover:underline" onClick={() => { const qs = structuredClone(data.questions); qs[i].options.push({ option_text: '', is_correct: false }); setData('questions', qs); }}>+ অপশন</button>
                </div>
            ))}
            <div className="flex gap-3">
                <button type="button" className="text-sm font-semibold text-brand hover:underline" onClick={() => setData('questions', [...structuredClone(data.questions), structuredClone(empty)])}>+ প্রশ্ন যোগ করুন</button>
            </div>
            <button type="submit" disabled={processing} className="btn-primary !py-2 text-sm">কুইজ সংরক্ষণ করুন</button>
        </form>
    );
}

function AssignmentForm({ course }) {
    const { data, setData, post, processing, reset } = useForm({ title: '', description: '', deadline: '', total_marks: 10, lesson_id: '' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(`/instructor/courses/${course.slug}/assignments`, { onSuccess: () => reset() }); }} className="space-y-3">
            <input className={field} placeholder="অ্যাসাইনমেন্টের শিরোনাম" value={data.title} onChange={(e) => setData('title', e.target.value)} />
            <textarea rows={3} className={field} placeholder="বিবরণ" value={data.description} onChange={(e) => setData('description', e.target.value)} />
            <div className="flex gap-3">
                <input type="date" className={`${field} !mt-0`} value={data.deadline} onChange={(e) => setData('deadline', e.target.value)} />
                <input type="number" min="0" className={`${field} !mt-0 w-32`} placeholder="পূর্ণমান" value={data.total_marks} onChange={(e) => setData('total_marks', e.target.value)} />
            </div>
            <button type="submit" disabled={processing} className="btn-primary !py-2 text-sm">অ্যাসাইনমেন্ট যোগ করুন</button>
        </form>
    );
}

export default function CourseForm({ categories, course }) {
    const editing = !!course;
    const { data, setData, post, put, processing, errors } = useForm({
        title: course?.title ?? '', category_id: course?.category_id ?? '',
        short_description: course?.short_description ?? '', description: course?.description ?? '',
        price: course?.price ?? 0, is_free: course ? !!course.is_free : true,
        level: course?.level ?? '', duration: course?.duration ?? '',
    });
    const [tab, setTab] = useState('info');

    const submit = (e) => {
        e.preventDefault();
        editing ? put(`/instructor/courses/${course.slug}`) : post('/instructor/courses');
    };

    const tabs = editing
        ? [['info', 'কোর্স তথ্য'], ['sections', 'সেকশন'], ['lessons', 'পাঠ'], ['quizzes', 'কুইজ'], ['assignments', 'অ্যাসাইনমেন্ট']]
        : [['info', 'কোর্স তথ্য']];

    return (
        <DashboardLayout title={editing ? `সম্পাদনা: ${course.title}` : 'নতুন কোর্স তৈরি করুন'}>
            <Head title={editing ? 'কোর্স সম্পাদনা' : 'নতুন কোর্স'} />
            {!editing && <p className="mb-4 rounded-xl bg-brand-cream/40 p-3 text-sm text-brand-deep">কোর্স জমা দিলে অ্যাডমিন অনুমোদনের পর প্রকাশিত হবে।</p>}

            <div className="mb-4 flex flex-wrap gap-2">
                {tabs.map(([key, label]) => (
                    <button key={key} type="button" onClick={() => setTab(key)} className={`rounded-full px-4 py-1.5 text-sm font-medium ${tab === key ? 'bg-brand text-brand-cream' : 'bg-white hover:bg-brand-light'}`}>{label}</button>
                ))}
            </div>

            {tab === 'info' && (
                <form onSubmit={submit} className="card space-y-4 p-6">
                    <div>
                        <label className="text-sm font-medium">শিরোনাম *</label>
                        <input className={field} value={data.title} onChange={(e) => setData('title', e.target.value)} />
                        {errors.title && <p className="mt-1 text-xs text-red-600">{errors.title}</p>}
                    </div>
                    <div>
                        <label className="text-sm font-medium">ক্যাটাগরি</label>
                        <select className={field} value={data.category_id ?? ''} onChange={(e) => setData('category_id', e.target.value)}>
                            <option value="">নির্বাচন করুন</option>
                            {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="text-sm font-medium">সংক্ষিপ্ত বিবরণ *</label>
                        <textarea rows={2} className={field} value={data.short_description} onChange={(e) => setData('short_description', e.target.value)} />
                        {errors.short_description && <p className="mt-1 text-xs text-red-600">{errors.short_description}</p>}
                    </div>
                    <div>
                        <label className="text-sm font-medium">বিস্তারিত বিবরণ *</label>
                        <textarea rows={6} className={field} value={data.description} onChange={(e) => setData('description', e.target.value)} />
                        {errors.description && <p className="mt-1 text-xs text-red-600">{errors.description}</p>}
                    </div>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label className="text-sm font-medium">মূল্য (৳)</label>
                            <input type="number" min="0" className={field} value={data.price} onChange={(e) => setData('price', e.target.value)} />
                        </div>
                        <div>
                            <label className="text-sm font-medium">লেভেল</label>
                            <input className={field} placeholder="যেমন: শুরু থেকে" value={data.level} onChange={(e) => setData('level', e.target.value)} />
                        </div>
                        <div>
                            <label className="text-sm font-medium">সময়কাল</label>
                            <input className={field} placeholder="যেমন: ৮ সপ্তাহ" value={data.duration} onChange={(e) => setData('duration', e.target.value)} />
                        </div>
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={data.is_free} onChange={(e) => setData('is_free', e.target.checked)} />
                        ফ্রি কোর্স
                    </label>
                    <button type="submit" disabled={processing} className="btn-primary">{editing ? 'আপডেট করুন' : 'অনুমোদনের জন্য জমা দিন'}</button>
                </form>
            )}

            {editing && tab === 'sections' && (
                <div className="card space-y-4 p-6">
                    <SectionForm course={course} />
                    <ul className="space-y-2 text-sm">
                        {course.sections?.map((s, i) => <li key={s.id} className="rounded-lg bg-brand-light px-3 py-2">অধ্যায় {i + 1}: {s.title} ({s.lessons?.length ?? 0} পাঠ)</li>)}
                    </ul>
                </div>
            )}
            {editing && tab === 'lessons' && <div className="card p-6"><LessonForm course={course} /></div>}
            {editing && tab === 'quizzes' && (
                <div className="card space-y-4 p-6">
                    <QuizForm course={course} />
                    {course.quizzes?.length > 0 && (
                        <ul className="space-y-2 text-sm">
                            {course.quizzes.map((q) => <li key={q.id} className="rounded-lg bg-brand-light px-3 py-2">📝 {q.title} — {q.questions?.length ?? 0} প্রশ্ন</li>)}
                        </ul>
                    )}
                </div>
            )}
            {editing && tab === 'assignments' && (
                <div className="card space-y-4 p-6">
                    <AssignmentForm course={course} />
                    {course.assignments?.length > 0 && (
                        <ul className="space-y-2 text-sm">
                            {course.assignments.map((a) => <li key={a.id} className="rounded-lg bg-brand-light px-3 py-2">📋 {a.title}</li>)}
                        </ul>
                    )}
                </div>
            )}
        </DashboardLayout>
    );
}
