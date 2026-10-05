<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateVerification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificateVerificationController extends Controller
{
    /**
     * Public certificate verification page with audit logging and revocation support.
     * Accessible by anyone via QR code or verification link.
     */
    public function verify(Request $request, string $identifier)
    {
        $certificate = Certificate::where('uuid', $identifier)
            ->orWhere('certificate_no', $identifier)
            ->with([
                'user:id,name',
                'course:id,title,slug,instructor_id,is_certified,certified_by_scholar_id',
                'course.instructor:id,name',
                'course.certifiedByScholar:id,name,designation',
                'revokedBy:id,name',
            ])
            ->first();

        $status = 'not_found';
        if ($certificate) {
            $status = $certificate->isRevoked() ? 'revoked' : 'valid';
        }

        // Log Verification Attempt
        CertificateVerification::create([
            'certificate_id' => $certificate?->id,
            'identifier_searched' => $identifier,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'status' => $status,
            'verified_at' => now(),
        ]);

        return Inertia::render('Certificate/Verify', [
            'certificate' => $certificate,
            'identifier' => $identifier,
            'isValid' => $status === 'valid',
            'isRevoked' => $status === 'revoked',
            'revokedReason' => $certificate?->revoked_reason,
            'revokedAt' => $certificate?->revoked_at?->format('d M Y, h:i A'),
        ]);
    }

    /**
     * Admin action to revoke a compromised or fraudulent certificate.
     */
    public function revoke(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $certificate->revoked_at = now();
        $certificate->revoked_reason = $validated['reason'];
        $certificate->revoked_by = $request->user()->id;
        $certificate->save();

        return back()->with('success', 'সার্টিফিকেটটি সফলভাবে প্রত্যাহার (Revoke) করা হয়েছে।');
    }
}
