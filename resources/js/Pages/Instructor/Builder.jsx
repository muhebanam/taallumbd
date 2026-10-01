import { Head, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import DashboardLayout from '../../Layouts/DashboardLayout';

const field = 'mt-1 w-full rounded-xl border-brand/20 focus:border-brand focus:ring-brand text-sm';

export default function Builder({ categories, course }) {
    const editing = !!course;
    const courseBase = course ? `/instructor/courses/${course.slug}` : '';
    const [tab, setTab] = useState('info'); // info, curriculum, preview
    
    // Course Info Form
    const { data: infoData, setData: setInfoData, post: postInfo, put: putInfo, processing: infoProcessing, errors: infoErrors } = useForm({
        title: course?.title ?? '',
        category_id: course?.category_id ?? '',
        short_description: course?.short_description ?? '',
        description: course?.description ?? '',
        price: course?.price ?? 0,
        is_free: course ? !!course.is_free : true,
        level: course?.level ?? '',
        duration: course?.duration ?? '',
        status: course?.status ?? 'pending',
        enrollment_limit: course?.enrollment_limit ?? '',
        enrollment_start: course?.enrollment_start ? new Date(course.enrollment_start).toISOString().slice(0, 16) : '',
        enrollment_end: course?.enrollment_end ? new Date(course.enrollment_end).toISOString().slice(0, 16) : '',
        thumbnail: null,
        completion_requirements: course?.completion_requirements ?? {
            lessons_required: true,
            quizzes_required: true,
            assignments_required: false
        }
    });

    // Modals & Form State for Curriculum
    const [activeModal, setActiveModal] = useState(null); // section, lesson, quiz, assignment, resource, live_class
    const [editingItem, setEditingItem] = useState(null); // item to edit
    const [targetSection, setTargetSection] = useState(null); // section to add item to

    // Local forms
    const [sectionTitle, setSectionTitle] = useState('');
    const [lessonForm, setLessonForm] = useState({ title: '', content: '', video_url: '', is_preview: false });
    const [quizForm, setQuizForm] = useState({
        title: '',
        description: '',
        pass_marks: 10,
        questions: [{ question: '', type: 'single_choice', marks: 5, options: [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }] }]
    });
    const [assignmentForm, setAssignmentForm] = useState({ title: '', description: '', deadline: '', total_marks: 20 });
    const [resourceForm, setResourceForm] = useState({ title: '', url: '', file: null });
    const [liveForm, setLiveForm] = useState({ title: '', meeting_url: '', start_time: '', duration: 60 });

    const submitInfo = (e) => {
        e.preventDefault();
        if (editing) {
            putInfo(`/instructor/courses/${course.slug}`);
        } else {
            postInfo('/instructor/courses');
        }
    };

    // Helper to trigger item creation
    const openCreateModal = (type, section) => {
        setTargetSection(section);
        setEditingItem(null);
        if (type === 'section') {
            setSectionTitle('');
        } else if (type === 'lesson') {
            setLessonForm({ title: '', content: '', video_url: '', is_preview: false });
        } else if (type === 'quiz') {
            setQuizForm({
                title: '',
                description: '',
                pass_marks: 10,
                questions: [{ question: '', type: 'single_choice', marks: 5, options: [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }] }]
            });
        } else if (type === 'assignment') {
            setAssignmentForm({ title: '', description: '', deadline: '', total_marks: 20 });
        } else if (type === 'resource') {
            setResourceForm({ title: '', url: '', file: null });
        } else if (type === 'live_class') {
            setLiveForm({ title: '', meeting_url: '', start_time: '', duration: 60 });
        }
        setActiveModal(type);
    };

    // Helper to trigger item edit
    const openEditModal = (type, item, section = null) => {
        setEditingItem(item);
        setTargetSection(section);
        if (type === 'section') {
            setSectionTitle(item.title);
        } else if (type === 'lesson') {
            setLessonForm({ title: item.title, content: item.content ?? '', video_url: item.video_url ?? '', is_preview: !!item.is_preview });
        } else if (type === 'quiz') {
            setQuizForm({
                title: item.title,
                description: item.description ?? '',
                pass_marks: item.pass_marks ?? 10,
                questions: item.questions?.length ? item.questions.map(q => ({
                    id: q.id,
                    question: q.question,
                    type: q.type,
                    marks: q.marks,
                    options: q.options?.map(opt => ({ id: opt.id, option_text: opt.option_text, is_correct: !!opt.is_correct })) ?? []
                })) : [{ question: '', type: 'single_choice', marks: 5, options: [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }] }]
            });
        } else if (type === 'assignment') {
            setAssignmentForm({
                title: item.title,
                description: item.description ?? '',
                deadline: item.deadline ? new Date(item.deadline).toISOString().slice(0, 10) : '',
                total_marks: item.total_marks ?? 20
            });
        } else if (type === 'resource') {
            setResourceForm({ title: item.title, url: item.url ?? '', file: null });
        } else if (type === 'live_class') {
            setLiveForm({
                title: item.title,
                meeting_url: item.meeting_url ?? '',
                start_time: item.start_time ? new Date(item.start_time).toISOString().slice(0, 16) : '',
                duration: item.duration ?? 60
            });
        }
        setActiveModal(type);
    };

    // Submit Sections
    const submitSection = (e) => {
        e.preventDefault();
        if (editingItem) {
            router.put(`/instructor/courses/${course.slug}/sections/${editingItem.id}`, { title: sectionTitle }, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`/instructor/courses/${course.slug}/sections`, { title: sectionTitle }, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Submit Lessons
    const submitLesson = (e) => {
        e.preventDefault();
        if (editingItem) {
            router.put(`${courseBase}/lessons/${editingItem.id}`, lessonForm, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`${courseBase}/sections/${targetSection.id}/lessons`, lessonForm, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Submit Quizzes
    const submitQuiz = (e) => {
        e.preventDefault();
        if (editingItem) {
            router.put(`${courseBase}/quizzes/${editingItem.id}`, quizForm, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`${courseBase}/sections/${targetSection.id}/quizzes`, quizForm, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Submit Assignments
    const submitAssignment = (e) => {
        e.preventDefault();
        if (editingItem) {
            router.put(`${courseBase}/assignments/${editingItem.id}`, assignmentForm, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`${courseBase}/sections/${targetSection.id}/assignments`, assignmentForm, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Submit Resources
    const submitResource = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('title', resourceForm.title);
        if (resourceForm.url) formData.append('url', resourceForm.url);
        if (resourceForm.file) formData.append('file', resourceForm.file);

        if (editingItem) {
            // Laravel PUT route file upload workaround via POST + _method
            formData.append('_method', 'PUT');
            router.post(`${courseBase}/resources/${editingItem.id}`, formData, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`${courseBase}/sections/${targetSection.id}/resources`, formData, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Submit Live Classes
    const submitLiveClass = (e) => {
        e.preventDefault();
        if (editingItem) {
            router.put(`${courseBase}/live-classes/${editingItem.id}`, liveForm, {
                onSuccess: () => setActiveModal(null)
            });
        } else {
            router.post(`${courseBase}/sections/${targetSection.id}/live-classes`, liveForm, {
                onSuccess: () => setActiveModal(null)
            });
        }
    };

    // Reordering handler
    const moveItem = (section, itemIndex, direction) => {
        const items = [...section.curriculum_items];
        if (direction === 'up' && itemIndex > 0) {
            const temp = items[itemIndex];
            items[itemIndex] = items[itemIndex - 1];
            items[itemIndex - 1] = temp;
        } else if (direction === 'down' && itemIndex < items.length - 1) {
            const temp = items[itemIndex];
            items[itemIndex] = items[itemIndex + 1];
            items[itemIndex + 1] = temp;
        } else {
            return;
        }

        // Map updated ordering structure for backend reorder request
        const payload = items.map((item, idx) => ({
            id: item.id,
            section_id: section.id,
            sort_order: idx + 1
        }));

        router.post(`/instructor/courses/${course.slug}/curriculum/reorder`, { items: payload });
    };

    // Delete item
    const deleteItem = (type, id) => {
        if (confirm('আপনি কি নিশ্চিতভাবে এটি মুছে ফেলতে চান?')) {
            let routeName = '';
            if (type === 'section') routeName = `${courseBase}/sections/${id}`;
            else if (type === 'lesson') routeName = `${courseBase}/lessons/${id}`;
            else if (type === 'quiz') routeName = `${courseBase}/quizzes/${id}`;
            else if (type === 'assignment') routeName = `${courseBase}/assignments/${id}`;
            else if (type === 'resource') routeName = `${courseBase}/resources/${id}`;
            else if (type === 'live_class') routeName = `${courseBase}/live-classes/${id}`;

            router.delete(routeName);
        }
    };

    // Checklist logic for preview
    const totalSections = course?.sections?.length ?? 0;
    const totalLessons = course?.sections?.reduce((acc, s) => acc + (s.curriculum_items?.filter(i => i.item_type === 'lesson').length ?? 0), 0) ?? 0;
    const isFreeOrHasPrice = infoData.is_free || infoData.price > 0;
    const hasThumbnail = !!course?.thumbnail || !!infoData.thumbnail;

    return (
        <DashboardLayout title={editing ? `সম্পাদনা: ${course.title}` : 'নতুন কোর্স তৈরি করুন'}>
            <Head title={editing ? 'কোর্স বিল্ডার' : 'নতুন কোর্স'} />

            {/* Navigation Tabs */}
            <div className="mb-6 flex border-b border-brand/10 pb-px">
                <button onClick={() => setTab('info')} className={`border-b-2 px-6 py-3 text-sm font-semibold transition ${tab === 'info' ? 'border-brand text-brand' : 'border-transparent text-brand-text/60 hover:text-brand'}`}>
                    📝 কোর্স তথ্য (Basic Info)
                </button>
                {editing && (
                    <>
                        <button onClick={() => setTab('curriculum')} className={`border-b-2 px-6 py-3 text-sm font-semibold transition ${tab === 'curriculum' ? 'border-brand text-brand' : 'border-transparent text-brand-text/60 hover:text-brand'}`}>
                            🗂️ কারিকুলাম বিল্ডার (Curriculum)
                        </button>
                        <button onClick={() => setTab('preview')} className={`border-b-2 px-6 py-3 text-sm font-semibold transition ${tab === 'preview' ? 'border-brand text-brand' : 'border-transparent text-brand-text/60 hover:text-brand'}`}>
                            👁️ প্রিভিউ ও প্রকাশ (Checklist)
                        </button>
                    </>
                )}
            </div>

            {/* TAB 1: BASIC INFO */}
            {tab === 'info' && (
                <form onSubmit={submitInfo} className="grid gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-6">
                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">সাধারণ তথ্য</h3>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">কোর্সের শিরোনাম *</label>
                                <input className={field} value={infoData.title} onChange={(e) => setInfoData('title', e.target.value)} required />
                                {infoErrors.title && <p className="mt-1 text-xs text-red-600">{infoErrors.title}</p>}
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">সংক্ষিপ্ত বিবরণ *</label>
                                <textarea rows={2} className={field} value={infoData.short_description} onChange={(e) => setInfoData('short_description', e.target.value)} required />
                                {infoErrors.short_description && <p className="mt-1 text-xs text-red-600">{infoErrors.short_description}</p>}
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">বিস্তারিত পরিচিতি *</label>
                                <textarea rows={8} className={field} value={infoData.description} onChange={(e) => setInfoData('description', e.target.value)} required />
                                {infoErrors.description && <p className="mt-1 text-xs text-red-600">{infoErrors.description}</p>}
                            </div>
                        </div>

                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">মূল্য ও সীমাবদ্ধতা</h3>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">মূল্য (৳)</label>
                                    <input type="number" min="0" className={field} value={infoData.price} onChange={(e) => setInfoData('price', e.target.value)} disabled={infoData.is_free} />
                                </div>
                                <div className="flex items-center pt-6">
                                    <label className="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                                        <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={infoData.is_free} onChange={(e) => setInfoData('is_free', e.target.checked)} />
                                        ফ্রি কোর্স হিসেবে উন্মুক্ত করুন
                                    </label>
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">লেভেল</label>
                                    <input className={field} placeholder="যেমন: শুরু থেকে" value={infoData.level} onChange={(e) => setInfoData('level', e.target.value)} />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">সময়কাল</label>
                                    <input className={field} placeholder="যেমন: ৮ সপ্তাহ" value={infoData.duration} onChange={(e) => setInfoData('duration', e.target.value)} />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">কোর্স ক্যাটাগরি</label>
                                    <select className={field} value={infoData.category_id} onChange={(e) => setInfoData('category_id', e.target.value)}>
                                        <option value="">নির্বাচন করুন</option>
                                        {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">ভর্তির নিয়ম ও সময়সীমা</h3>
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">ভর্তি সীমা (আসন সংখ্যা)</label>
                                    <input type="number" min="1" className={field} placeholder="সীমাহীন" value={infoData.enrollment_limit} onChange={(e) => setInfoData('enrollment_limit', e.target.value)} />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">ভর্তি শুরু</label>
                                    <input type="datetime-local" className={field} value={infoData.enrollment_start} onChange={(e) => setInfoData('enrollment_start', e.target.value)} />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-brand-text/75">ভর্তি শেষ</label>
                                    <input type="datetime-local" className={field} value={infoData.enrollment_end} onChange={(e) => setInfoData('enrollment_end', e.target.value)} />
                                </div>
                            </div>
                        </div>

                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">কভার ইমেজ</h3>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">থাম্বনেইল ছবি</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    className="mt-1 block w-full text-sm text-brand-text/70 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-light file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand hover:file:bg-brand/20"
                                    onChange={(e) => setInfoData('thumbnail', e.target.files?.[0] ?? null)}
                                />
                                <p className="mt-2 text-xs text-brand-text/50">
                                    {course?.thumbnail ? 'বর্তমান থাম্বনেইল আছে' : 'এখনও থাম্বনেইল দেওয়া হয়নি'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">প্রকাশের তথ্য</h3>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">স্ট্যাটাস (Status)</label>
                                <select
                                    className={field}
                                    value={infoData.status}
                                    onChange={(e) => setInfoData('status', e.target.value)}
                                    disabled={!editing}
                                >
                                    <option value="draft">খসড়া (Draft)</option>
                                    <option value="pending">অনুমোদনের জন্য পেন্ডিং (Pending)</option>
                                    <option value="published">প্রকাশিত (Published)</option>
                                    <option value="coming_soon">শীঘ্রই আসছে (Coming Soon)</option>
                                </select>
                                {!editing && (
                                    <p className="mt-1 text-xs text-brand-text/50">নতুন কোর্স তৈরি হলে এটি স্বয়ংক্রিয়ভাবে pending থাকবে।</p>
                                )}
                            </div>
                            <button type="submit" disabled={infoProcessing} className="btn-primary w-full py-3">
                                {editing ? 'তথ্য আপডেট করুন' : 'কোর্স তৈরি করুন'}
                            </button>
                        </div>

                        <div className="card p-6 space-y-4">
                            <h3 className="text-lg font-bold text-brand-deep border-b border-brand/5 pb-2">সার্টিফিকেট রিকোয়ারমেন্টস</h3>
                            <div className="space-y-3">
                                <label className="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                                    <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={infoData.completion_requirements.lessons_required} onChange={(e) => setInfoData('completion_requirements', { ...infoData.completion_requirements, lessons_required: e.target.checked })} />
                                    সকল পাঠ সম্পন্ন করা বাধ্যতামূলক
                                </label>
                                <label className="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                                    <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={infoData.completion_requirements.quizzes_required} onChange={(e) => setInfoData('completion_requirements', { ...infoData.completion_requirements, quizzes_required: e.target.checked })} />
                                    সকল কুইজ সম্পন্ন করা বাধ্যতামূলক
                                </label>
                                <label className="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                                    <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={infoData.completion_requirements.assignments_required} onChange={(e) => setInfoData('completion_requirements', { ...infoData.completion_requirements, assignments_required: e.target.checked })} />
                                    অ্যাসাইনমেন্ট সম্পন্ন করা বাধ্যতামূলক
                                </label>
                                <label className="flex items-center gap-2 text-sm font-semibold cursor-not-allowed opacity-50">
                                    <input type="checkbox" disabled className="rounded border-brand/30 text-brand focus:ring-brand" />
                                    ম্যানুয়াল অনুমোদন প্রয়োজন <span className="rounded bg-amber-100 text-amber-800 text-[10px] px-1 font-bold ml-1">Coming Soon</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            )}

            {/* TAB 2: CURRICULUM BUILDER */}
            {tab === 'curriculum' && editing && (
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <h3 className="text-xl font-bold text-brand-deep">কোর্স কারিকুলাম</h3>
                        <button onClick={() => openCreateModal('section')} className="btn-primary !px-4 !py-2 text-sm">
                            + নতুন অধ্যায় যোগ করুন
                        </button>
                    </div>

                    <div className="space-y-4">
                        {course.sections?.map((section, sIdx) => (
                            <div key={section.id} className="card overflow-hidden border border-brand/10">
                                {/* Section Header */}
                                <div className="bg-brand-light flex items-center justify-between px-5 py-3">
                                    <span className="font-bold text-brand-deep">অধ্যায় {sIdx + 1}: {section.title}</span>
                                    <div className="flex items-center gap-2">
                                        {/* Add Content Dropdown */}
                                        <select onChange={(e) => {
                                            if (e.target.value) {
                                                openCreateModal(e.target.value, section);
                                                e.target.value = '';
                                            }
                                        }} className="rounded-lg border-brand/20 bg-white text-xs font-semibold py-1 px-3">
                                            <option value="">+ কন্টেন্ট যোগ করুন</option>
                                            <option value="lesson">🎬 নতুন পাঠ (Lesson)</option>
                                            <option value="quiz">📝 নতুন কুইজ (Quiz)</option>
                                            <option value="assignment">📋 নতুন অ্যাসাইনমেন্ট (Assignment)</option>
                                            <option value="resource">📄 নতুন রিসোর্স (Resource)</option>
                                            <option value="live_class">🎥 নতুন লাইভ ক্লাস (Live Class)</option>
                                        </select>
                                        
                                        <button onClick={() => openEditModal('section', section)} className="text-brand hover:text-brand-deep text-xs font-bold">সম্পাদনা</button>
                                        <button onClick={() => deleteItem('section', section.id)} className="text-red-600 hover:text-red-700 text-xs font-bold ml-1">মুছুন</button>
                                    </div>
                                </div>

                                {/* Section Items */}
                                <ul className="divide-y divide-brand/5 bg-white">
                                    {section.curriculum_items?.map((item, itemIdx) => {
                                        const typeIcons = { lesson: '🎬', quiz: '📝', assignment: '📋', resource: '📄', live_class: '🎥' };
                                        const typeLabels = { lesson: 'পাঠ', quiz: 'কুইজ', assignment: 'অ্যাসাইনমেন্ট', resource: 'রিসোর্স', live_class: 'লাইভ ক্লাস' };
                                        
                                        return (
                                            <li key={item.id} className="flex items-center justify-between px-5 py-3 text-sm hover:bg-brand-cream/10">
                                                <div className="flex items-center gap-3">
                                                    <span className="text-lg">{typeIcons[item.item_type] ?? '🎬'}</span>
                                                    <div>
                                                        <span className="font-semibold text-brand-deep">{item.title_snapshot}</span>
                                                        <span className="text-xs text-brand-text/50 font-normal ml-2">({typeLabels[item.item_type]})</span>
                                                        {item.is_preview && <span className="ml-2 rounded-md bg-green-100 text-green-800 text-[10px] px-1 font-bold">ফ্রি প্রিভিউ</span>}
                                                    </div>
                                                </div>
                                                
                                                <div className="flex items-center gap-3">
                                                    {/* Move Up/Down Controls */}
                                                    <button onClick={() => moveItem(section, itemIdx, 'up')} disabled={itemIdx === 0} className="text-brand disabled:opacity-30 text-xs hover:underline">▲</button>
                                                    <button onClick={() => moveItem(section, itemIdx, 'down')} disabled={itemIdx === section.curriculum_items.length - 1} className="text-brand disabled:opacity-30 text-xs hover:underline">▼</button>
                                                    
                                                    <button onClick={() => openEditModal(item.item_type, item.itemable, section)} className="text-brand hover:underline font-medium text-xs ml-3">সম্পাদনা</button>
                                                    <button onClick={() => deleteItem(item.item_type, item.itemable_id)} className="text-red-600 hover:underline font-medium text-xs">মুছুন</button>
                                                </div>
                                            </li>
                                        );
                                    })}
                                    {(!section.curriculum_items || section.curriculum_items.length === 0) && (
                                        <li className="px-5 py-4 text-sm text-brand-text/40 italic text-center">অধ্যায়ে কোনো কন্টেন্ট নেই।</li>
                                    )}
                                </ul>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* TAB 3: PREVIEW & PUBLISH */}
            {tab === 'preview' && editing && (
                <div className="card p-6 max-w-2xl mx-auto space-y-6">
                    <h3 className="text-xl font-bold text-brand-deep border-b border-brand/5 pb-2">কোর্স প্রকাশের চেকলিস্ট (Checklist)</h3>
                    
                    <div className="space-y-4">
                        <div className="flex items-center justify-between p-3 rounded-xl bg-brand-light">
                            <span className="text-sm font-semibold">অধ্যায় সংখ্যা:</span>
                            <span className={`text-sm font-bold ${totalSections > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {totalSections > 0 ? `✔ ${totalSections} টি অধ্যায়` : '✖ একটি অধ্যায়ও নেই'}
                            </span>
                        </div>

                        <div className="flex items-center justify-between p-3 rounded-xl bg-brand-light">
                            <span className="text-sm font-semibold">মোট পাঠ (Lessons) সংখ্যা:</span>
                            <span className={`text-sm font-bold ${totalLessons > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {totalLessons > 0 ? `✔ ${totalLessons} টি পাঠ` : '✖ একটি পাঠও নেই'}
                            </span>
                        </div>

                        <div className="flex items-center justify-between p-3 rounded-xl bg-brand-light">
                            <span className="text-sm font-semibold">কোর্সের মূল্য সেটিং:</span>
                            <span className={`text-sm font-bold ${isFreeOrHasPrice ? 'text-green-600' : 'text-red-600'}`}>
                                {isFreeOrHasPrice ? '✔ সঠিক আছে' : '✖ মূল্য নির্ধারণ করা হয়নি'}
                            </span>
                        </div>

                        <div className="flex items-center justify-between p-3 rounded-xl bg-brand-light">
                            <span className="text-sm font-semibold">কভার থাম্বনেইল:</span>
                            <span className={`text-sm font-bold ${hasThumbnail ? 'text-green-600' : 'text-red-600'}`}>
                                {hasThumbnail ? '✔ থাম্বনেইল দেওয়া আছে' : '✖ থাম্বনেইল নেই'}
                            </span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-brand/10 p-4 bg-brand-cream/20">
                        <h4 className="font-bold text-brand-deep mb-1">প্রকাশ সংক্রান্ত ঘোষণা:</h4>
                        <p className="text-xs text-brand-text/80 leading-relaxed">
                            কোর্সের স্ট্যাটাস আপডেট করার পর আপনার পরিবর্তনসমূহ সেভ করুন। কোর্সটি "Published" বা "Coming Soon" করা হলে তা পাবলিক পেজে প্রকাশিত হয়ে যাবে। "Draft" অবস্থায় কোর্সটি কেবল ইনস্ট্রাকটর ও অ্যাডমিন দেখতে পাবেন।
                        </p>
                    </div>
                </div>
            )}

            {/* MODALS */}

            {/* Section Modal */}
            {activeModal === 'section' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={submitSection} className="card max-w-md w-full p-6 space-y-4">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'অধ্যায় সম্পাদনা' : 'নতুন অধ্যায় যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">অধ্যায়ের নাম</label>
                            <input className={field} value={sectionTitle} onChange={(e) => setSectionTitle(e.target.value)} required />
                        </div>
                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Lesson Modal */}
            {activeModal === 'lesson' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 overflow-y-auto">
                    <form onSubmit={submitLesson} className="card max-w-lg w-full p-6 space-y-4 my-8">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'পাঠ সম্পাদনা' : 'নতুন পাঠ যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">পাঠের শিরোনাম *</label>
                            <input className={field} value={lessonForm.title} onChange={(e) => setLessonForm({ ...lessonForm, title: e.target.value })} required />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">ভিডিও URL (YouTube/Vimeo)</label>
                            <input className={field} value={lessonForm.video_url} onChange={(e) => setLessonForm({ ...lessonForm, video_url: e.target.value })} />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">পাঠের বিবরণ</label>
                            <textarea rows={4} className={field} value={lessonForm.content} onChange={(e) => setLessonForm({ ...lessonForm, content: e.target.value })} />
                        </div>
                        <div>
                            <label className="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                                <input type="checkbox" className="rounded border-brand/30 text-brand focus:ring-brand" checked={lessonForm.is_preview} onChange={(e) => setLessonForm({ ...lessonForm, is_preview: e.target.checked })} />
                                ফ্রি প্রিভিউ হিসেবে উন্মুক্ত (Preview Lesson)
                            </label>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-brand/5 pt-3">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">পাঠ সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Quiz Modal */}
            {activeModal === 'quiz' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 overflow-y-auto">
                    <form onSubmit={submitQuiz} className="card max-w-xl w-full p-6 space-y-4 my-8">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'কুইজ সম্পাদনা' : 'নতুন কুইজ যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">কুইজের শিরোনাম *</label>
                            <input className={field} value={quizForm.title} onChange={(e) => setQuizForm({ ...quizForm, title: e.target.value })} required />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">পাস মার্কস *</label>
                                <input type="number" min="0" className={field} value={quizForm.pass_marks} onChange={(e) => setQuizForm({ ...quizForm, pass_marks: Number(e.target.value) })} required />
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">কুইজের বিবরণ</label>
                                <input className={field} value={quizForm.description} onChange={(e) => setQuizForm({ ...quizForm, description: e.target.value })} />
                            </div>
                        </div>

                        {/* Questions list */}
                        <div className="space-y-4 max-h-72 overflow-y-auto border border-brand/10 p-3 rounded-xl bg-brand-light/20">
                            <h4 className="font-bold text-sm text-brand-deep">প্রশ্নসমূহ:</h4>
                            {quizForm.questions.map((q, qIdx) => (
                                <div key={qIdx} className="p-3 bg-white rounded-lg border border-brand/5 space-y-3">
                                    <div className="flex gap-2 items-center">
                                        <input className={field} placeholder={`প্রশ্ন ${qIdx + 1}`} value={q.question} onChange={(e) => {
                                            const qs = [...quizForm.questions];
                                            qs[qIdx].question = e.target.value;
                                            setQuizForm({ ...quizForm, questions: qs });
                                        }} required />
                                        <button type="button" onClick={() => {
                                            const qs = quizForm.questions.filter((_, idx) => idx !== qIdx);
                                            setQuizForm({ ...quizForm, questions: qs });
                                        }} className="text-red-500 hover:text-red-700 text-xs font-bold">মুছুন</button>
                                    </div>
                                    <div className="flex gap-4">
                                        <select className="rounded-lg border-brand/20 text-xs" value={q.type} onChange={(e) => {
                                            const qs = [...quizForm.questions];
                                            qs[qIdx].type = e.target.value;
                                            setQuizForm({ ...quizForm, questions: qs });
                                        }}>
                                            <option value="single_choice">একক উত্তর (Single Choice)</option>
                                            <option value="multiple_choice">একাধিক উত্তর (Multiple Choice)</option>
                                            <option value="true_false">সত্য/মিথ্যা (True/False)</option>
                                        </select>
                                        <input type="number" className="w-20 rounded-lg border-brand/20 text-xs" placeholder="মান" value={q.marks} onChange={(e) => {
                                            const qs = [...quizForm.questions];
                                            qs[qIdx].marks = Number(e.target.value);
                                            setQuizForm({ ...quizForm, questions: qs });
                                        }} required />
                                    </div>

                                    {/* Options list */}
                                    <div className="space-y-2 pl-4">
                                        {q.options.map((opt, optIdx) => (
                                            <div key={optIdx} className="flex items-center gap-2">
                                                <input type="checkbox" checked={opt.is_correct} onChange={(e) => {
                                                    const qs = [...quizForm.questions];
                                                    if (q.type === 'single_choice' || q.type === 'true_false') {
                                                        qs[qIdx].options = qs[qIdx].options.map((o, idx) => ({ ...o, is_correct: idx === optIdx }));
                                                    } else {
                                                        qs[qIdx].options[optIdx].is_correct = e.target.checked;
                                                    }
                                                    setQuizForm({ ...quizForm, questions: qs });
                                                }} />
                                                <input className="flex-1 rounded-lg border-brand/10 text-xs py-1 px-2" placeholder={`অপশন ${optIdx + 1}`} value={opt.option_text} onChange={(e) => {
                                                    const qs = [...quizForm.questions];
                                                    qs[qIdx].options[optIdx].option_text = e.target.value;
                                                    setQuizForm({ ...quizForm, questions: qs });
                                                }} required />
                                            </div>
                                        ))}
                                        <button type="button" onClick={() => {
                                            const qs = [...quizForm.questions];
                                            qs[qIdx].options.push({ option_text: '', is_correct: false });
                                            setQuizForm({ ...quizForm, questions: qs });
                                        }} className="text-brand text-xs font-semibold hover:underline">+ নতুন অপশন</button>
                                    </div>
                                </div>
                            ))}
                            <button type="button" onClick={() => {
                                setQuizForm({
                                    ...quizForm,
                                    questions: [...quizForm.questions, { question: '', type: 'single_choice', marks: 5, options: [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }] }]
                                });
                            }} className="text-brand text-xs font-semibold hover:underline">+ নতুন প্রশ্ন যোগ করুন</button>
                        </div>

                        <div className="flex justify-end gap-2 border-t border-brand/5 pt-3">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">কুইজ সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Assignment Modal */}
            {activeModal === 'assignment' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={submitAssignment} className="card max-w-md w-full p-6 space-y-4">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'অ্যাসাইনমেন্ট সম্পাদনা' : 'নতুন অ্যাসাইনমেন্ট যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">অ্যাসাইনমেন্টের শিরোনাম *</label>
                            <input className={field} value={assignmentForm.title} onChange={(e) => setAssignmentForm({ ...assignmentForm, title: e.target.value })} required />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">অ্যাসাইনমেন্টের বিবরণ *</label>
                            <textarea rows={4} className={field} value={assignmentForm.description} onChange={(e) => setAssignmentForm({ ...assignmentForm, description: e.target.value })} required />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">মোট মার্কস *</label>
                                <input type="number" min="0" className={field} value={assignmentForm.total_marks} onChange={(e) => setAssignmentForm({ ...assignmentForm, total_marks: Number(e.target.value) })} required />
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">ডেডলাইন (Deadline)</label>
                                <input type="date" className={field} value={assignmentForm.deadline} onChange={(e) => setAssignmentForm({ ...assignmentForm, deadline: e.target.value })} />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Resource Modal */}
            {activeModal === 'resource' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={submitResource} className="card max-w-md w-full p-6 space-y-4">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'রিসোর্স সম্পাদনা' : 'নতুন রিসোর্স যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">রিসোর্সের শিরোনাম *</label>
                            <input className={field} value={resourceForm.title} onChange={(e) => setResourceForm({ ...resourceForm, title: e.target.value })} required />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">ডাউনলোডযোগ্য ফাইল</label>
                            <input type="file" className="block w-full text-xs text-brand-text/60 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-light file:text-brand hover:file:bg-brand/20" onChange={(e) => setResourceForm({ ...resourceForm, file: e.target.files[0] })} />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">অথবা এক্সটার্নাল URL (External Link)</label>
                            <input className={field} value={resourceForm.url} onChange={(e) => setResourceForm({ ...resourceForm, url: e.target.value })} />
                        </div>
                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">রিসোর্স সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Live Class Modal */}
            {activeModal === 'live_class' && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={submitLiveClass} className="card max-w-md w-full p-6 space-y-4">
                        <h3 className="text-lg font-bold text-brand-deep">{editingItem ? 'লাইভ ক্লাস সম্পাদনা' : 'নতুন লাইভ ক্লাস যোগ করুন'}</h3>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">ক্লাসের শিরোনাম *</label>
                            <input className={field} value={liveForm.title} onChange={(e) => setLiveForm({ ...liveForm, title: e.target.value })} required />
                        </div>
                        <div>
                            <label className="text-xs font-semibold text-brand-text/75">মিটিং লিংক (Zoom / Google Meet)</label>
                            <input className={field} value={liveForm.meeting_url} onChange={(e) => setLiveForm({ ...liveForm, meeting_url: e.target.value })} />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">শুরুর সময় *</label>
                                <input type="datetime-local" className={field} value={liveForm.start_time} onChange={(e) => setLiveForm({ ...liveForm, start_time: e.target.value })} required />
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-brand-text/75">সময়কাল (মিনিট) *</label>
                                <input type="number" min="1" className={field} value={liveForm.duration} onChange={(e) => setLiveForm({ ...liveForm, duration: Number(e.target.value) })} required />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setActiveModal(null)} className="btn-secondary !py-2 text-xs">বাতিল</button>
                            <button type="submit" className="btn-primary !py-2 text-xs">ক্লাস সংরক্ষণ করুন</button>
                        </div>
                    </form>
                </div>
            )}
        </DashboardLayout>
    );
}
