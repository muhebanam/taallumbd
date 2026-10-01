import { Head, useForm } from '@inertiajs/react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function AssignmentView({ assignment, submission }) {
    const { data, setData, post, processing, errors } = useForm({ answer_text: '', file: null });
    const submit = (e) => { e.preventDefault(); post(`/dashboard/assignments/${assignment.id}/submit`); };

    return (
        <DashboardLayout title={assignment.title}>
            <Head title={assignment.title} />
            <div className="card p-6">
                <p className="whitespace-pre-line leading-relaxed text-brand-text/85">{assignment.description}</p>
                <p className="mt-3 text-sm text-brand-text/60">
                    পূর্ণমান: {assignment.total_marks}
                    {assignment.deadline && <> • শেষ তারিখ: {new Date(assignment.deadline).toLocaleDateString('bn-BD')}</>}
                </p>
            </div>

            {submission ? (
                <div className="card mt-6 p-6">
                    <h2 className="font-bold text-brand-deep">আপনার জমা</h2>
                    {submission.answer_text && <p className="mt-2 whitespace-pre-line text-sm">{submission.answer_text}</p>}
                    <p className="mt-3 text-sm">
                        অবস্থা: <span className="font-semibold">{submission.status === 'reviewed' ? `মূল্যায়িত — প্রাপ্ত নম্বর: ${submission.marks}` : 'জমা হয়েছে, মূল্যায়নের অপেক্ষায়'}</span>
                    </p>
                    {submission.feedback && <p className="mt-2 rounded-lg bg-brand-light p-3 text-sm">💬 {submission.feedback}</p>}
                </div>
            ) : (
                <form onSubmit={submit} className="card mt-6 space-y-4 p-6">
                    <h2 className="font-bold text-brand-deep">উত্তর জমা দিন</h2>
                    <textarea rows={6} className="w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand" placeholder="লিখিত উত্তর (ঐচ্ছিক, ফাইল দিলে)" value={data.answer_text} onChange={(e) => setData('answer_text', e.target.value)} />
                    <div>
                        <label className="text-sm font-medium">ফাইল সংযুক্ত করুন (PDF/DOC/ZIP, সর্বোচ্চ ১০MB)</label>
                        <input type="file" className="mt-1 block w-full text-sm" onChange={(e) => setData('file', e.target.files[0])} />
                        {errors.file && <p className="mt-1 text-xs text-red-600">{errors.file}</p>}
                    </div>
                    <button type="submit" disabled={processing} className="btn-primary">জমা দিন</button>
                </form>
            )}
        </DashboardLayout>
    );
}
