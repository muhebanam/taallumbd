<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificateVerificationController extends Controller
{
    /**
     * Public certificate verification page.
     * Accessible by anyone via QR code or verification link.
     */
    public function verify(string $identifier)
    {
        $certificate = Certificate::where('uuid', $identifier)
            ->orWhere('certificate_no', $identifier)
            ->with([
                'user:id,name',
                'course:id,title,slug,instructor_id',
                'course.instructor:id,name',
            ])
            ->first();

        return Inertia::render('Certificate/Verify', [
            'certificate' => $certificate,
            'identifier' => $identifier,
            'isValid' => !is_null($certificate),
        ]);
    }
}
