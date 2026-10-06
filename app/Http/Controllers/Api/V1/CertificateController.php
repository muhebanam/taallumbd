<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * List certificates earned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with(['course:id,title,slug,thumbnail'])
            ->latest('issue_date')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'certificate_no' => $c->certificate_no,
                'course' => [
                    'id' => $c->course?->id,
                    'title' => $c->course?->title,
                    'slug' => $c->course?->slug,
                    'thumbnail' => $c->course?->thumbnail ? asset('storage/'.$c->course->thumbnail) : null,
                ],
                'issue_date' => $c->issue_date?->format('Y-m-d'),
                'is_revoked' => (bool) $c->isRevoked(),
                'verify_url' => url('/verify/'.$c->certificate_no),
            ]);

        return response()->json([
            'success' => true,
            'certificates' => $certificates,
        ]);
    }

    /**
     * Public certificate verification endpoint with audit trail.
     */
    public function verify(Request $request, string $code): JsonResponse
    {
        $certificate = Certificate::where('uuid', $code)
            ->orWhere('certificate_no', $code)
            ->with([
                'user:id,name',
                'course:id,title,slug,instructor_id,certified_by_scholar_id',
                'course.instructor:id,name',
                'course.certifiedByScholar:id,name,designation',
            ])
            ->first();

        $status = 'not_found';
        if ($certificate) {
            $status = $certificate->isRevoked() ? 'revoked' : 'valid';
        }

        CertificateVerification::create([
            'certificate_id' => $certificate?->id,
            'identifier_searched' => $code,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'status' => $status,
            'verified_at' => now(),
        ]);

        if (! $certificate) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'এই কোডের কোনো সনদপত্র পাওয়া যায়নি।',
            ], 404);
        }

        if ($status === 'revoked') {
            return response()->json([
                'success' => false,
                'status' => 'revoked',
                'message' => 'এই সনদপত্রটি বাতিল করা হয়েছে।',
                'certificate' => [
                    'certificate_no' => $certificate->certificate_no,
                    'student_name' => $certificate->user?->name,
                    'course_title' => $certificate->course?->title,
                    'revoked_reason' => $certificate->revoked_reason,
                    'revoked_at' => $certificate->revoked_at?->toIso8601String(),
                ],
            ], 410);
        }

        return response()->json([
            'success' => true,
            'status' => 'valid',
            'message' => 'সনদপত্রটি বৈধ ও যাচাইকৃত।',
            'certificate' => [
                'id' => $certificate->id,
                'uuid' => $certificate->uuid,
                'certificate_no' => $certificate->certificate_no,
                'student_name' => $certificate->user?->name,
                'course_title' => $certificate->course?->title,
                'instructor_name' => $certificate->course?->instructor?->name,
                'certified_by' => $certificate->course?->certifiedByScholar?->name,
                'issue_date' => $certificate->issue_date?->format('d M Y'),
                'verify_url' => url('/verify/'.$certificate->certificate_no),
            ],
        ]);
    }
}
