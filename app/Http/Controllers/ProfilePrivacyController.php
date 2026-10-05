<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePrivacyController extends Controller
{
    /**
     * Export all personal learning and profile data as JSON download (GDPR/Compliance).
     */
    public function exportData(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'enrollments' => $user->enrollments()->with('course:id,title,slug')->get()->map(function ($e) {
                return [
                    'course' => $e->course?->title,
                    'status' => $e->status,
                    'progress' => $e->progress,
                    'enrolled_at' => $e->created_at?->toIso8601String(),
                    'completed_at' => $e->completed_at?->toIso8601String(),
                ];
            }),
            'quiz_attempts' => $user->quizAttempts()->with('quiz:id,title')->get()->map(function ($q) {
                return [
                    'quiz' => $q->quiz?->title,
                    'status' => $q->status,
                    'score' => $q->score,
                    'passed' => $q->status === 'passed',
                    'attempted_at' => $q->created_at?->toIso8601String(),
                ];
            }),
            'certificates' => $user->certificates()->with('course:id,title')->get()->map(function ($c) {
                return [
                    'course' => $c->course?->title,
                    'certificate_number' => $c->certificate_number,
                    'issued_at' => $c->issued_at?->toIso8601String(),
                ];
            }),
            'learning_history' => LearningEvent::where('user_id', $user->id)
                ->orderBy('occurred_at', 'desc')
                ->limit(5000)
                ->get(['event_type', 'subject_type', 'subject_id', 'course_id', 'properties', 'occurred_at']),
        ];

        $fileName = 'taallum_data_export_user_'.$user->id.'_'.now()->format('Ymd_His').'.json';

        return response()->stream(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Anonymize / delete personal learning event history.
     */
    public function deleteData(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Anonymize learning events by dissociating user_id
        $affected = LearningEvent::where('user_id', $user->id)->update(['user_id' => null]);

        return response()->json([
            'success' => true,
            'message' => "আপনার লার্নিং হিস্টোরি সফলভাবে অ্যানোনিমাইজ ও মুছে ফেলা হয়েছে ({$affected}টি রেকর্ড)।",
        ]);
    }
}
