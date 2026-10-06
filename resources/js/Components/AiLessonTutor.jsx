import React, { useState, useEffect, useRef } from 'react';

export default function AiLessonTutor({ lessonId, lessonTitle }) {
    const [messages, setMessages] = useState([
        {
            role: 'assistant',
            text: `আসসালামু আলাইকুম! আমি আত-তাআল্লুমের এআই কোর্স সহকারী। "${lessonTitle}" পাঠের বিষয়ে যেকোনো প্রশ্ন করতে পারেন। আমি শুধুমাত্র কোর্স পাঠ্য ও নির্ভরযোগ্য ইসলামিক সূত্র থেকে উত্তর দেব।`,
            sources: [],
            disclaimer: 'সতর্কতা: এটি কৃত্রিম বুদ্ধিমত্তা দ্বারা প্রস্তুতকৃত প্রাথমিক তথ্য। এটি কোনো শরয়ী ফতোয়া নয়।',
        }
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [remainingQuota, setRemainingQuota] = useState(null);
    const [reportingId, setReportingId] = useState(null);
    const [reportReason, setReportReason] = useState('');
    const [reportSuccess, setReportSuccess] = useState('');
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages, loading]);

    // Fetch initial quota
    useEffect(() => {
        fetch('/api/v1/ai/quota', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setRemainingQuota(data.user_remaining);
                }
            })
            .catch(() => {});
    }, []);

    const handleSend = async (e) => {
        e?.preventDefault();
        if (!input.trim() || loading) return;

        const userMsg = input.trim();
        setInput('');
        setMessages(prev => [...prev, { role: 'user', text: userMsg }]);
        setLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/dashboard/lessons/${lessonId}/ai-chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    lesson_id: lessonId,
                    question: userMsg,
                })
            });

            const json = await res.json();
            if (json.success && json.data) {
                setMessages(prev => [...prev, {
                    role: 'assistant',
                    text: json.data.text,
                    sources: json.data.sources || [],
                    isRedirectToFatwa: json.data.is_redirect_to_fatwa,
                    redirectUrl: json.data.redirect_url,
                    disclaimer: json.data.disclaimer,
                    interactionId: json.data.sources?.[0]?.interaction_id,
                }]);
                if (json.quota_remaining !== undefined) {
                    setRemainingQuota(json.quota_remaining);
                }
            } else {
                setMessages(prev => [...prev, {
                    role: 'assistant',
                    text: json.message || 'দুঃখিত, সংযোগে সমস্যা হয়েছে। কিছুক্ষণ পর চেষ্টা করুন।',
                    sources: [],
                    disclaimer: '',
                }]);
            }
        } catch (err) {
            setMessages(prev => [...prev, {
                role: 'assistant',
                text: 'সার্ভারে সংযোগ স্থাপন সম্ভব হয়নি। অনুগ্রহ করে কিছুক্ষণ পর চেষ্টা করুন।',
                sources: [],
                disclaimer: '',
            }]);
        } finally {
            setLoading(false);
        }
    };

    const handleFeedback = async (interactionId, rating) => {
        if (!interactionId) return;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch(`/dashboard/ai/interactions/${interactionId}/feedback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ rating })
            });
            alert('আপনার মতামতের জন্য ধন্যবাদ!');
        } catch (err) {}
    };

    const handleReport = async (e) => {
        e.preventDefault();
        if (!reportingId) return;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/dashboard/ai/interactions/${reportingId}/flag`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ reason: reportReason })
            });
            const data = await res.json();
            setReportSuccess(data.message || 'স্কলার রিভিউ কিউতে পাঠানো হয়েছে।');
            setTimeout(() => {
                setReportingId(null);
                setReportReason('');
                setReportSuccess('');
            }, 2500);
        } catch (err) {}
    };

    return (
        <div className="flex flex-col h-[520px] rounded-2xl border border-emerald-900/10 bg-slate-50/50 overflow-hidden">
            {/* Header / Governance Banner */}
            <div className="bg-gradient-to-r from-[#102526] to-[#1A2E2F] px-4 py-3 text-white flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-300 font-bold text-sm">
                        🤖
                    </span>
                    <div>
                        <h4 className="text-xs font-bold leading-tight flex items-center gap-1.5">
                            <span>এআই কোর্স সহকারী</span>
                            <span className="text-[10px] font-normal px-1.5 py-0.5 rounded bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                                তত্ত্বাবধানে আলেমগণ
                            </span>
                        </h4>
                        <p className="text-[10px] text-slate-300">
                            মূলনীতি: AI assists; scholars remain final authority
                        </p>
                    </div>
                </div>

                {remainingQuota !== null && (
                    <div className="text-right">
                        <span className="inline-block text-[11px] font-semibold bg-white/10 px-2 py-0.5 rounded-full text-emerald-200">
                            আজকের বাকি কোটা: {remainingQuota}
                        </span>
                    </div>
                )}
            </div>

            {/* Privacy Warning */}
            <div className="bg-amber-50 border-b border-amber-200/60 px-3 py-1.5 text-[11px] text-amber-800 flex items-center justify-between">
                <span>🔒 কোনো ব্যক্তিগত তথ্য (নাম, ফোন, পরিচয়পত্র) লিখবেন না।</span>
                <span className="text-[10px] text-amber-700/80 font-medium">স্বয়ংক্রিয় PII ফিল্টার সক্রিয়</span>
            </div>

            {/* Message Area */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {messages.map((msg, idx) => (
                    <div
                        key={idx}
                        className={`flex flex-col ${msg.role === 'user' ? 'items-end' : 'items-start'}`}
                    >
                        <div
                            className={`max-w-[85%] rounded-2xl px-4 py-3 text-xs leading-relaxed shadow-sm ${
                                msg.role === 'user'
                                    ? 'bg-[#1A2E2F] text-white rounded-br-sm'
                                    : 'bg-white text-slate-800 border border-slate-200 rounded-bl-sm'
                            }`}
                        >
                            <p className="whitespace-pre-wrap">{msg.text}</p>

                            {/* Fatwa Redirect Notice & CTA Button */}
                            {msg.isRedirectToFatwa && (
                                <div className="mt-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                                    <p className="font-semibold text-[11px] mb-1.5">
                                        ⚖️ এটি একটি ফিকহী/শরয়ী মাসআলা। মুফতি বোর্ডের সাথে পরামর্শ করুন:
                                    </p>
                                    <a
                                        href={msg.redirectUrl || '/fatawa/ask'}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1 text-[11px] font-bold bg-[#1A2E2F] text-white px-3 py-1.5 rounded-lg hover:bg-emerald-800 transition"
                                    >
                                        মুফতি বোর্ডে প্রশ্ন জিজ্ঞাসা করুন →
                                    </a>
                                </div>
                            )}

                            {/* Citations / Sources */}
                            {msg.sources && msg.sources.length > 0 && (
                                <div className="mt-2.5 pt-2 border-t border-slate-100">
                                    <span className="text-[10px] font-bold text-slate-500 block mb-1">
                                        উদ্ধৃত ইসলামিক ও পাঠ্য উৎস:
                                    </span>
                                    <div className="flex flex-wrap gap-1.5">
                                        {msg.sources.map((src, sIdx) => (
                                            src.url ? (
                                                <a
                                                    key={sIdx}
                                                    href={src.url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-1 text-[10px] bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 px-2 py-0.5 rounded border border-slate-200 transition"
                                                >
                                                    <span>📖</span>
                                                    <span>{src.reference || src.title}</span>
                                                </a>
                                            ) : (
                                                <span
                                                    key={sIdx}
                                                    className="inline-flex items-center gap-1 text-[10px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200"
                                                >
                                                    <span>📖</span>
                                                    <span>{src.reference || src.title}</span>
                                                </span>
                                            )
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Safety Disclaimer */}
                            {msg.disclaimer && (
                                <p className="mt-2 pt-1 text-[10px] text-slate-400 italic">
                                    {msg.disclaimer}
                                </p>
                            )}
                        </div>

                        {/* Feedback & Report Buttons for Assistant Responses */}
                        {msg.role === 'assistant' && msg.interactionId && (
                            <div className="flex items-center gap-2 mt-1 px-1 text-[10px] text-slate-400">
                                <span>উপকারী লেগেছে?</span>
                                <button
                                    type="button"
                                    onClick={() => handleFeedback(msg.interactionId, 1)}
                                    className="hover:text-emerald-600 transition"
                                    title="হ্যাঁ, উপকারী"
                                >
                                    👍
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleFeedback(msg.interactionId, -1)}
                                    className="hover:text-rose-600 transition"
                                    title="না"
                                >
                                    👎
                                </button>
                                <span className="text-slate-300">|</span>
                                <button
                                    type="button"
                                    onClick={() => setReportingId(msg.interactionId)}
                                    className="hover:text-amber-600 underline transition"
                                >
                                    রিপোর্ট / স্কলার রিভিউ
                                </button>
                            </div>
                        )}
                    </div>
                ))}

                {loading && (
                    <div className="flex items-center gap-2 text-xs text-slate-500 bg-white p-3 rounded-2xl border border-slate-200 w-fit">
                        <div className="h-3 w-3 animate-spin rounded-full border-2 border-emerald-600 border-t-transparent" />
                        <span>প্রামাণ্য উৎস অনুসন্ধান ও উত্তর প্রস্তুত হচ্ছে...</span>
                    </div>
                )}

                <div ref={messagesEndRef} />
            </div>

            {/* Flag / Report Modal Inline */}
            {reportingId && (
                <div className="p-3 bg-amber-50 border-t border-amber-200 text-xs">
                    <form onSubmit={handleReport} className="space-y-2">
                        <p className="font-semibold text-amber-900">
                            বিজ্ঞ আলেমদের পরীক্ষার জন্য এই উত্তরে কোনো অসঙ্গতি বা ভুলের কারণ লিখুন:
                        </p>
                        <input
                            type="text"
                            value={reportReason}
                            onChange={e => setReportReason(e.target.value)}
                            placeholder="যেমন: উদ্ধৃতি ভুল, পরিভাষা অস্পষ্ট ইত্যাদি..."
                            required
                            className="w-full text-xs rounded-lg border-amber-300 focus:border-amber-500 focus:ring-amber-500"
                        />
                        <div className="flex items-center gap-2">
                            <button
                                type="submit"
                                className="px-3 py-1 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 text-xs transition"
                            >
                                স্কলার কিউতে জমা দিন
                            </button>
                            <button
                                type="button"
                                onClick={() => setReportingId(null)}
                                className="px-3 py-1 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 text-xs transition"
                            >
                                বাতিল
                            </button>
                            {reportSuccess && (
                                <span className="text-emerald-700 font-semibold">{reportSuccess}</span>
                            )}
                        </div>
                    </form>
                </div>
            )}

            {/* Input Bar */}
            <form onSubmit={handleSend} className="p-3 bg-white border-t border-slate-200 flex gap-2">
                <input
                    type="text"
                    value={input}
                    onChange={e => setInput(e.target.value)}
                    placeholder="এই পাঠ সম্পর্কে প্রশ্ন লিখুন..."
                    disabled={loading}
                    className="flex-1 rounded-xl border-slate-200 text-xs focus:border-[#1A2E2F] focus:ring-[#1A2E2F]"
                />
                <button
                    type="submit"
                    disabled={loading || !input.trim()}
                    className="rounded-xl bg-[#1A2E2F] px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900 disabled:opacity-50 transition"
                >
                    পাঠান
                </button>
            </form>
        </div>
    );
}
