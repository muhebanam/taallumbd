import { Head, useForm } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function QuizView({ quiz, lastAttempt }) {
    const { data, setData, post, processing } = useForm({ answers: {} });

    const toggle = (questionId, optionId, multi) => {
        const current = data.answers[questionId] ?? [];
        const next = multi
            ? (current.includes(optionId) ? current.filter((id) => id !== optionId) : [...current, optionId])
            : [optionId];
        setData('answers', { ...data.answers, [questionId]: next });
    };

    const submit = (e) => { e.preventDefault(); post(`/dashboard/quizzes/${quiz.id}/submit`); };

    return (
        <DashboardLayout title={quiz.title}>
            <Head title={quiz.title} />
            {quiz.description && <p className="mb-2 text-sm text-brand-text/70">{quiz.description}</p>}
            <p className="mb-6 text-sm text-brand-text/60">পূর্ণমান: {quiz.total_marks} • পাস মার্ক: {quiz.pass_marks}
                {lastAttempt && <span className="ml-3 font-semibold">সর্বশেষ চেষ্টা: {lastAttempt.score} ({lastAttempt.status === 'passed' ? 'পাস' : 'ফেল'})</span>}
            </p>

            <form onSubmit={submit} className="space-y-4">
                {quiz.questions.map((q, qi) => {
                    const multi = q.type === 'multiple_choice';
                    return (
                        <div key={q.id} className="card p-5">
                            <p className="font-semibold text-brand-deep">{qi + 1}. {q.question} <span className="text-xs font-normal text-brand-text/50">({q.marks} নম্বর{multi ? ' • একাধিক উত্তর' : ''})</span></p>
                            <div className="mt-3 space-y-2">
                                {q.options.map((opt) => (
                                    <label key={opt.id} className={`flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm ${(data.answers[q.id] ?? []).includes(opt.id) ? 'border-brand bg-brand-light' : 'border-brand/15'}`}>
                                        <input
                                            type={multi ? 'checkbox' : 'radio'}
                                            name={`q-${q.id}`}
                                            className="text-brand focus:ring-brand"
                                            checked={(data.answers[q.id] ?? []).includes(opt.id)}
                                            onChange={() => toggle(q.id, opt.id, multi)}
                                        />
                                        {opt.option_text}
                                    </label>
                                ))}
                            </div>
                        </div>
                    );
                })}
                <button type="submit" disabled={processing} className="btn-primary w-full">উত্তর জমা দিন</button>
            </form>
        </DashboardLayout>
    );
}
