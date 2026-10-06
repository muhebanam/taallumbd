<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Cohort;
use App\Models\CohortMember;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\GradeBook;
use App\Models\Organization;
use App\Models\OrganizationCertificate;
use App\Models\OrganizationMember;
use App\Models\OrganizationSubscription;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnterpriseLmsService
{
    /**
     * Add a member to the organization with seat-limit check.
     *
     * @throws ValidationException
     */
    public function addMember(
        Organization $org,
        array $userData,
        string $role = 'student',
        ?string $idNumber = null,
        ?int $guardianUserId = null
    ): OrganizationMember {
        if (! $org->hasAvailableSeats(1)) {
            throw ValidationException::withMessages([
                'seat_limit' => ["আপনার প্রতিষ্ঠানের সিট সীমা ({$org->seat_limit}) পূর্ণ হয়ে গেছে। নতুন সদস্য যোগ করতে সিট বৃদ্ধি করুন।"],
            ]);
        }

        return DB::transaction(function () use ($org, $userData, $role, $idNumber, $guardianUserId) {
            // Find or create user
            $user = null;
            if (! empty($userData['email'])) {
                $user = User::where('email', $userData['email'])->first();
            }

            if (! $user) {
                $plainPassword = $userData['password'] ?? Str::random(10);
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'] ?? null,
                    'password' => Hash::make($plainPassword),
                    'role' => $role === 'teacher' ? 'instructor' : 'student',
                ]);
            }

            $member = OrganizationMember::withoutGlobalScopes()->updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'user_id' => $user->id,
                    'role' => $role,
                ],
                [
                    'id_number' => $idNumber,
                    'guardian_user_id' => $guardianUserId,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            $org->syncUsedSeats();

            return $member;
        });
    }

    /**
     * Bulk import members from CSV with validation and seat limit protection.
     * Expected CSV headers: name,email,phone,role,id_number,guardian_email
     *
     * @throws ValidationException
     */
    public function bulkImportMembers(Organization $org, string $csvContent, string $defaultRole = 'student'): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (empty($lines)) {
            return ['imported' => 0, 'errors' => ['CSV ফাইলটি খালি।']];
        }

        $header = str_getcsv(array_shift($lines));
        $headerMap = [];
        foreach ($header as $idx => $colName) {
            $headerMap[strtolower(trim($colName))] = $idx;
        }

        $validRows = [];
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);
            $name = isset($headerMap['name']) ? trim($row[$headerMap['name']] ?? '') : '';
            $email = isset($headerMap['email']) ? trim($row[$headerMap['email']] ?? '') : '';
            $phone = isset($headerMap['phone']) ? trim($row[$headerMap['phone']] ?? '') : null;
            $role = isset($headerMap['role']) ? trim($row[$headerMap['role']] ?? '') : $defaultRole;
            $idNumber = isset($headerMap['id_number']) ? trim($row[$headerMap['id_number']] ?? '') : (isset($headerMap['roll']) ? trim($row[$headerMap['roll']] ?? '') : null);
            $guardianEmail = isset($headerMap['guardian_email']) ? trim($row[$headerMap['guardian_email']] ?? '') : null;

            if (empty($name) || empty($email)) {
                $errors[] = 'লাইন '.($lineNum + 2).': নাম অথবা ইমেইল অনুপস্থিত।';

                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'লাইন '.($lineNum + 2).": অকার্যকর ইমেইল '{$email}'।";

                continue;
            }

            $validRows[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => in_array($role, ['org_admin', 'teacher', 'student', 'guardian'], true) ? $role : $defaultRole,
                'id_number' => $idNumber,
                'guardian_email' => $guardianEmail,
            ];
        }

        $availableSeats = $org->seat_limit - $org->used_seats;
        if (count($validRows) > $availableSeats) {
            throw ValidationException::withMessages([
                'seat_limit' => ['আপনার প্রতিষ্ঠানে পর্যাপ্ত সিট নেই। প্রয়োজন: '.count($validRows).", অবশিষ্ট সিট: {$availableSeats}। অনুগ্রহ করে সাবস্ক্রিপশন প্ল্যান আপগ্রেড করুন।"],
            ]);
        }

        $imported = 0;
        DB::transaction(function () use ($org, $validRows, &$imported) {
            foreach ($validRows as $item) {
                $guardianId = null;
                if (! empty($item['guardian_email'])) {
                    $guardian = User::firstOrCreate(
                        ['email' => $item['guardian_email']],
                        [
                            'name' => 'Guardian of '.$item['name'],
                            'password' => Hash::make(Str::random(12)),
                            'role' => 'student',
                        ]
                    );
                    $guardianId = $guardian->id;

                    OrganizationMember::withoutGlobalScopes()->firstOrCreate([
                        'organization_id' => $org->id,
                        'user_id' => $guardian->id,
                        'role' => 'guardian',
                    ], [
                        'status' => 'active',
                        'joined_at' => now(),
                    ]);
                }

                $this->addMember(
                    $org,
                    [
                        'name' => $item['name'],
                        'email' => $item['email'],
                        'phone' => $item['phone'],
                        'password' => Str::random(10),
                    ],
                    $item['role'],
                    $item['id_number'],
                    $guardianId
                );

                $imported++;
            }
        });

        $org->syncUsedSeats();

        return [
            'imported' => $imported,
            'skipped' => count($lines) - count($validRows),
            'errors' => $errors,
        ];
    }

    /**
     * Create a cohort / halaqa.
     */
    public function createCohort(Organization $org, array $data): Cohort
    {
        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Cohort::withoutGlobalScopes()->where('organization_id', $org->id)->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return Cohort::create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'slug' => $slug,
            'academic_year' => $data['academic_year'] ?? '1447-1448 AH',
            'head_teacher_id' => $data['head_teacher_id'] ?? null,
            'room_number' => $data['room_number'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Assign a user (student or teacher) to a cohort.
     */
    public function assignCohortMember(Cohort $cohort, int $userId, string $role = 'student', ?string $rollNumber = null): CohortMember
    {
        return CohortMember::updateOrCreate(
            [
                'cohort_id' => $cohort->id,
                'user_id' => $userId,
            ],
            [
                'role' => $role,
                'roll_number' => $rollNumber,
            ]
        );
    }

    /**
     * Map a course to a cohort.
     */
    public function assignCourseToCohort(Cohort $cohort, int $courseId, ?int $teacherId = null, bool $isMandatory = true): void
    {
        $cohort->courses()->syncWithoutDetaching([
            $courseId => [
                'assigned_teacher_id' => $teacherId,
                'is_mandatory' => $isMandatory,
                'start_date' => now(),
            ],
        ]);
    }

    /**
     * Record batch attendance for a cohort session.
     */
    public function recordAttendanceBatch(
        Organization $org,
        int $cohortId,
        string $date,
        string $sessionName,
        array $records,
        int $markedByUserId
    ): int {
        $count = 0;
        DB::transaction(function () use ($org, $cohortId, $date, $sessionName, $records, $markedByUserId, &$count) {
            foreach ($records as $record) {
                if (empty($record['user_id'])) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'cohort_id' => $cohortId,
                        'user_id' => $record['user_id'],
                        'date' => $date,
                        'session_name' => $sessionName,
                    ],
                    [
                        'organization_id' => $org->id,
                        'marked_by' => $markedByUserId,
                        'status' => $record['status'] ?? 'present',
                        'remarks' => $record['remarks'] ?? null,
                    ]
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * Create an exam with question bank.
     */
    public function createExam(Organization $org, array $data, int $createdById): Exam
    {
        return Exam::create([
            'organization_id' => $org->id,
            'cohort_id' => $data['cohort_id'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'created_by' => $createdById,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'exam_type' => $data['exam_type'] ?? 'quiz_mcq',
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'total_marks' => $data['total_marks'] ?? 100,
            'pass_marks' => $data['pass_marks'] ?? 40,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'question_bank' => $data['question_bank'] ?? [],
            'randomize_questions' => $data['randomize_questions'] ?? true,
            'status' => $data['status'] ?? 'published',
        ]);
    }

    /**
     * Evaluate exam submission with automated MCQ score calculation + manual scores.
     */
    public function evaluateSubmission(
        Exam $exam,
        int $studentId,
        array $studentAnswers,
        ?float $manualScore = null,
        ?int $gradedBy = null,
        ?string $feedback = null
    ): ExamSubmission {
        $questions = $exam->question_bank ?? [];
        $autoScore = 0.0;

        foreach ($questions as $q) {
            $qId = $q['id'] ?? null;
            if ($qId && isset($studentAnswers[$qId])) {
                $studentAnswer = $studentAnswers[$qId];
                $correctAnswer = $q['correct_answer'] ?? null;
                $points = (float) ($q['points'] ?? 1.0);

                if ($correctAnswer !== null && (string) $studentAnswer === (string) $correctAnswer) {
                    $autoScore += $points;
                }
            }
        }

        $finalManual = $manualScore !== null ? (float) $manualScore : 0.0;
        $totalScore = $autoScore + $finalManual;
        $totalMarks = $exam->total_marks > 0 ? $exam->total_marks : 100;
        $percentage = round(($totalScore / $totalMarks) * 100, 2);
        $grade = $this->calculateIslamicGrade($percentage);

        return ExamSubmission::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $studentId,
            ],
            [
                'answers' => $studentAnswers,
                'submitted_at' => now(),
                'auto_score' => $autoScore,
                'manual_score' => $finalManual,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'grade' => $grade,
                'status' => $manualScore !== null ? 'graded' : 'submitted',
                'graded_by' => $gradedBy,
                'feedback' => $feedback,
            ]
        );
    }

    /**
     * Map percentage to traditional Islamic madrasah grade scale.
     */
    public function calculateIslamicGrade(float $percentage): string
    {
        if ($percentage >= 90.0) {
            return 'মুমতায (ممتاز - চমৎকার)';
        }
        if ($percentage >= 80.0) {
            return 'জায়্যিদ জিদ্দান (جيد جداً - অতি উত্তম)';
        }
        if ($percentage >= 65.0) {
            return 'জায়্যিদ (جيد - উত্তম)';
        }
        if ($percentage >= 50.0) {
            return 'মাকবুল (مقبول - সন্তোষজনক)';
        }

        return 'রাসিব (راسب - অনুত্তীর্ণ)';
    }

    /**
     * Generate report cards (grade books) for an entire cohort for a term.
     */
    public function generateCohortReportCards(Organization $org, int $cohortId, string $term): Collection
    {
        $cohort = Cohort::with(['students'])->findOrFail($cohortId);
        $exams = Exam::where('cohort_id', $cohortId)->with('submissions')->get();

        $studentsData = [];

        foreach ($cohort->students as $student) {
            $studentSubmissions = [];
            $totalPossible = 0.0;
            $totalObtained = 0.0;

            foreach ($exams as $exam) {
                $sub = $exam->submissions->firstWhere('user_id', $student->id);
                $obtained = $sub ? (float) $sub->total_score : 0.0;
                $studentSubmissions[] = [
                    'exam_id' => $exam->id,
                    'title' => $exam->title,
                    'total_marks' => (float) $exam->total_marks,
                    'obtained_marks' => $obtained,
                    'grade' => $sub ? $sub->grade : 'অনুপস্থিত',
                ];
                $totalPossible += (float) $exam->total_marks;
                $totalObtained += $obtained;
            }

            $percentage = $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 2) : 0.0;
            $grade = $this->calculateIslamicGrade($percentage);

            $studentsData[$student->id] = [
                'student' => $student,
                'scores_breakdown' => $studentSubmissions,
                'total_marks' => $totalPossible,
                'obtained_marks' => $totalObtained,
                'percentage' => $percentage,
                'grade' => $grade,
            ];
        }

        // Calculate positions in class (rank by obtained marks descending)
        uasort($studentsData, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        $rank = 1;
        $results = collect();

        DB::transaction(function () use ($org, $cohortId, $term, $studentsData, &$rank, &$results) {
            foreach ($studentsData as $studentId => $data) {
                $gradeBook = GradeBook::updateOrCreate(
                    [
                        'cohort_id' => $cohortId,
                        'user_id' => $studentId,
                        'term' => $term,
                    ],
                    [
                        'organization_id' => $org->id,
                        'scores_breakdown' => $data['scores_breakdown'],
                        'total_marks' => $data['total_marks'],
                        'obtained_marks' => $data['obtained_marks'],
                        'overall_percentage' => $data['percentage'],
                        'overall_grade' => $data['grade'],
                        'position_in_class' => $rank,
                        'remarks' => "মেধাক্রম: {$rank}",
                        'is_published' => true,
                    ]
                );
                $results->push($gradeBook);
                $rank++;
            }
        });

        return $results;
    }

    /**
     * Issue a branded certificate to a student.
     */
    public function issueCertificate(
        Organization $org,
        int $studentId,
        string $title,
        ?int $cohortId = null,
        ?int $courseId = null,
        array $metadata = []
    ): OrganizationCertificate {
        $student = User::findOrFail($studentId);
        $certNumber = 'TAALLUM-'.$org->id.'-'.now()->year.'-'.strtoupper(Str::random(6));

        $branding = $org->branding ?? [];
        $mergedMetadata = array_merge([
            'org_name' => $org->name,
            'org_logo' => $branding['logo_url'] ?? null,
            'org_seal' => $branding['seal_url'] ?? null,
            'primary_color' => $branding['primary_color'] ?? '#065f46',
            'issued_by' => 'তাআল্লুম বিডি মাল্টি-টেন্যান্ট ইনস্টিটিউট সিস্টেম',
        ], $metadata);

        return OrganizationCertificate::create([
            'organization_id' => $org->id,
            'user_id' => $student->id,
            'cohort_id' => $cohortId,
            'course_id' => $courseId,
            'certificate_number' => $certNumber,
            'title' => $title,
            'recipient_name' => $student->name,
            'issued_date' => now()->toDateString(),
            'custom_metadata' => $mergedMetadata,
            'pdf_path' => null,
        ]);
    }

    /**
     * Update or renew Enterprise Subscription seats (Phase 10 seat-based billing).
     */
    public function updateSeatPlan(
        Organization $org,
        string $planName,
        int $seatCount,
        float $pricePerSeat = 50.00,
        string $billingCycle = 'monthly'
    ): OrganizationSubscription {
        if ($seatCount < $org->used_seats) {
            throw new DomainException("বর্তমান সক্রিয় সদস্য সংখ্যা ({$org->used_seats}) থেকে সিট সংখ্যা কম নির্ধারণ করা সম্ভব নয়।");
        }

        $multiplier = $billingCycle === 'yearly' ? 10 : 1; // 2 months free on yearly
        $totalAmount = $seatCount * $pricePerSeat * $multiplier;
        $endsAt = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription = OrganizationSubscription::create([
            'organization_id' => $org->id,
            'plan_name' => $planName,
            'seat_count' => $seatCount,
            'price_per_seat' => $pricePerSeat,
            'total_amount' => $totalAmount,
            'billing_cycle' => $billingCycle,
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'status' => 'active',
        ]);

        $org->update([
            'plan' => $planName,
            'seat_limit' => $seatCount,
            'status' => 'active',
            'subscription_ends_at' => $endsAt,
        ]);

        return $subscription;
    }
}
