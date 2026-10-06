<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'level',
        'icon',
        'duration',
        'status',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'learning_path_courses')
            ->withPivot('sort_order', 'prerequisite_course_id')
            ->orderBy('learning_path_courses.sort_order');
    }

    public function enrollments()
    {
        return $this->hasMany(LearningPathEnrollment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Calculate user's progress along this learning path.
     */
    public function calculateProgress(?User $user): array
    {
        $courses = $this->courses()->with(['instructor'])->get();
        $totalCount = $courses->count();

        if (! $user || $totalCount === 0) {
            return [
                'is_enrolled' => false,
                'total_courses' => $totalCount,
                'completed_courses' => 0,
                'progress_percentage' => 0,
                'is_completed' => false,
                'can_claim_certificate' => false,
                'has_certificate' => false,
                'certificate' => null,
                'steps' => $courses->map(function ($course, $idx) {
                    return [
                        'course' => $course,
                        'sort_order' => $course->pivot->sort_order ?? ($idx + 1),
                        'prerequisite_course_id' => $course->pivot->prerequisite_course_id,
                        'is_completed' => false,
                        'is_unlocked' => $idx === 0,
                    ];
                })->all(),
            ];
        }

        // Get all completed course IDs for user
        $completedCourseIds = Enrollment::where('user_id', $user->id)
            ->whereIn('course_id', $courses->pluck('id'))
            ->where('status', 'completed')
            ->pluck('course_id')
            ->all();

        // Also check certificates as proof of completion
        $certifiedCourseIds = Certificate::where('user_id', $user->id)
            ->whereIn('course_id', $courses->pluck('id'))
            ->pluck('course_id')
            ->all();

        $allCompletedSet = array_flip(array_unique(array_merge($completedCourseIds, $certifiedCourseIds)));

        $completedCount = 0;
        $steps = [];

        foreach ($courses as $index => $course) {
            $isCompleted = isset($allCompletedSet[$course->id]);
            if ($isCompleted) {
                $completedCount++;
            }

            $prereqId = $course->pivot->prerequisite_course_id;
            // Unlocked if: it's the first course, or there's no prereq, or prereq is completed
            $isUnlocked = ($index === 0)
                || (! $prereqId)
                || (isset($allCompletedSet[$prereqId]));

            $steps[] = [
                'course' => $course,
                'sort_order' => $course->pivot->sort_order ?? ($index + 1),
                'prerequisite_course_id' => $prereqId,
                'is_completed' => $isCompleted,
                'is_unlocked' => $isUnlocked,
            ];
        }

        $percentage = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;
        $isCompleted = $totalCount > 0 && ($completedCount === $totalCount);

        // Check enrollment record
        $enrollment = LearningPathEnrollment::where('user_id', $user->id)
            ->where('learning_path_id', $this->id)
            ->first();

        // Check if certificate already exists
        $certificate = Certificate::where('user_id', $user->id)
            ->where('learning_path_id', $this->id)
            ->first();

        // Sync enrollment record progress if enrolled
        if ($enrollment) {
            $enrollment->update([
                'completed_courses_count' => $completedCount,
                'total_courses_count' => $totalCount,
                'progress_percentage' => $percentage,
                'status' => $isCompleted ? 'completed' : ($percentage > 0 ? 'in_progress' : 'enrolled'),
                'completed_at' => $isCompleted ? ($enrollment->completed_at ?? now()) : null,
                'certificate_id' => $certificate?->id ?? $enrollment->certificate_id,
            ]);
        }

        return [
            'is_enrolled' => $enrollment !== null,
            'total_courses' => $totalCount,
            'completed_courses' => $completedCount,
            'progress_percentage' => $percentage,
            'is_completed' => $isCompleted,
            'can_claim_certificate' => $isCompleted && ! $certificate,
            'has_certificate' => $certificate !== null,
            'certificate' => $certificate,
            'steps' => $steps,
        ];
    }

    /**
     * Enroll user in this learning path.
     */
    public function enrollUser(User $user): LearningPathEnrollment
    {
        $courses = $this->courses()->get();
        $totalCount = $courses->count();

        $enrollment = LearningPathEnrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'learning_path_id' => $this->id,
            ],
            [
                'status' => 'enrolled',
                'completed_courses_count' => 0,
                'total_courses_count' => $totalCount,
                'progress_percentage' => 0,
                'enrolled_at' => now(),
            ]
        );

        // Auto-enroll user in the courses of this learning path if free/open
        foreach ($courses as $course) {
            if ($course->is_free) {
                Enrollment::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ], [
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'progress' => 0,
                ]);
            }
        }

        // Recalculate progress immediately
        $this->calculateProgress($user);

        return $enrollment->fresh();
    }

    /**
     * Complete and issue path certificate for user.
     */
    public function awardCertificate(User $user): Certificate
    {
        $progress = $this->calculateProgress($user);

        if (! $progress['is_completed']) {
            throw new Exception('লার্নিং পাথের সকল কোর্স সম্পন্ন না করে সার্টিফিকেট দাবি করা যাবে না।');
        }

        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('learning_path_id', $this->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certNo = 'LP-'.date('Y').'-'.strtoupper(Str::random(8));

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => null,
            'learning_path_id' => $this->id,
            'certificate_no' => $certNo,
            'issued_at' => now(),
        ]);

        LearningPathEnrollment::where('user_id', $user->id)
            ->where('learning_path_id', $this->id)
            ->update([
                'status' => 'completed',
                'progress_percentage' => 100,
                'completed_at' => now(),
                'certificate_id' => $certificate->id,
            ]);

        return $certificate;
    }
}
