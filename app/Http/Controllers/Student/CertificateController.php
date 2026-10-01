<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function __construct(private ProgressService $progress) {}

    public function index(Request $request)
    {
        return Inertia::render('Student/Certificates', [
            'certificates' => $request->user()->certificates()->with('course:id,title,slug')->latest()->get(),
        ]);
    }

    public function generate(Request $request, Course $course)
    {
        $user = $request->user();
        abort_unless($this->progress->eligibleForCertificate($user, $course), 403,
            'সার্টিফিকেটের জন্য ১০০% পাঠ সম্পন্ন এবং সব কুইজে পাস করতে হবে।');
        $certificate = $this->progress->issueCertificate($user, $course);
        return redirect()->route('certificates.show', $certificate);
    }

    public function show(Request $request, Certificate $certificate)
    {
        abort_unless($certificate->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        return Inertia::render('Student/CertificateView', [
            'certificate' => $certificate->load(['user:id,name', 'course:id,title']),
        ]);
    }
}
