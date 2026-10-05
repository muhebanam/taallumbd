<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScholarVerificationController extends Controller
{
    /**
     * Show verification details and checklist for a scholar.
     */
    public function show(Teacher $teacher)
    {
        $teacher->loadMissing('verifiedBy:id,name,email');

        return Inertia::render('Admin/Teachers/Verification', [
            'teacher' => $teacher,
            'defaultChecklist' => [
                'dawra_certificate' => 'দাওরায়ে হাদীস / তাকমীল ফিল হাদীস সনদপত্র যাচাই',
                'ijazah_accreditation' => 'স্বীকৃত উস্তাযদের থেকে হাদীস/কুরআনের ইজাজাহ যাচাই',
                'institutional_identity' => 'জাতীয় পরিচয়পত্র / প্রাতিষ্ঠানিক প্রত্যয়ন যাচাই',
                'fatwa_qualification' => 'ইফতা বোর্ড / উলামা পরিষদের সুপারিশ ও সাক্ষাৎকার',
            ],
        ]);
    }

    /**
     * Update verification checklist and save notes.
     */
    public function updateChecklist(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'checklist' => 'required|array',
            'notes' => 'nullable|string|max:2000',
        ]);

        $teacher->verification_checklist = $validated['checklist'];
        $teacher->verification_notes = $validated['notes'] ?? $teacher->verification_notes;
        $teacher->save();

        return back()->with('success', 'যাচাই চেকলিস্ট সফলভাবে আপডেট হয়েছে।');
    }

    /**
     * Upload private verification document.
     */
    public function uploadDocument(Request $request, Teacher $teacher)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'title' => 'required|string|max:100',
        ]);

        $path = $request->file('document')->store('scholar_verifications/private', 'local');

        $docs = $teacher->verification_documents ?? [];
        $docs[] = [
            'id' => uniqid('doc_'),
            'title' => $request->input('title'),
            'path' => $path,
            'filename' => $request->file('document')->getClientOriginalName(),
            'uploaded_at' => now()->toIso8601String(),
            'uploaded_by' => $request->user()->name,
        ];

        $teacher->verification_documents = $docs;
        $teacher->save();

        return back()->with('success', 'নথি সফলভাবে আপলোড হয়েছে।');
    }

    /**
     * Confirm scholar verification.
     */
    public function verify(Request $request, Teacher $teacher)
    {
        $teacher->is_verified = true;
        $teacher->verified_at = now();
        $teacher->verified_by = $request->user()->id;
        $teacher->save();

        return back()->with('success', "«{$teacher->name}»-কে ভেরিফায়েড স্কলার হিসেবে অনুমোদন দেওয়া হয়েছে।");
    }

    /**
     * Revoke scholar verification.
     */
    public function unverify(Teacher $teacher)
    {
        $teacher->is_verified = false;
        $teacher->verified_at = null;
        $teacher->verified_by = null;
        $teacher->save();

        return back()->with('success', "«{$teacher->name}»-এর ভেরিফিকেশন প্রত্যাহার করা হয়েছে।");
    }
}
