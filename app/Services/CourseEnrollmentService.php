<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Exception;

class CourseEnrollmentService
{
    public function validateEnrollment(User $user, Course $course): void
    {
        // 1. Already enrolled
        if ($user->isEnrolled($course)) {
            throw new Exception('আপনি ইতিমধ্যে এই কোর্সে ভর্তি আছেন।');
        }

        // 2. Coming Soon check
        if ($course->status === 'coming_soon') {
            throw new Exception('কোর্সটি শীঘ্রই আসছে। বর্তমানে ভর্তি সাময়িকভাবে বন্ধ আছে।');
        }

        // 3. Status must be published
        if ($course->status !== 'published') {
            throw new Exception('এই কোর্সটিতে বর্তমানে ভর্তি হওয়া যাবে না।');
        }

        // 4. Enrollment Window check
        if ($course->enrollment_start && $course->enrollment_start->isFuture()) {
            throw new Exception('কোর্সে ভর্তি শুরু হবে ' . $course->enrollment_start->format('Y-m-d H:i') . ' এ।');
        }

        if ($course->enrollment_end && $course->enrollment_end->isPast()) {
            throw new Exception('কোর্সে ভর্তির সময় শেষ হয়ে গেছে।');
        }

        // 5. Enrollment Limit check
        if ($course->enrollment_limit) {
            $currentEnrolled = $course->enrollments()->whereIn('status', ['active', 'completed'])->count();
            if ($currentEnrolled >= $course->enrollment_limit) {
                throw new Exception('কোর্সের আসন সংখ্যা পূর্ণ হয়ে গেছে। দুঃখিত!');
            }
        }
    }
}
