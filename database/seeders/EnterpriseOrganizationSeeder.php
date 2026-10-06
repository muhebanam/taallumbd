<?php

namespace Database\Seeders;

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
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EnterpriseOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds for Enterprise Multi-Tenant LMS.
     */
    public function run(): void
    {
        // ----------------- Organization 1: Darul Uloom Dhaka -----------------
        $org1 = Organization::updateOrCreate(
            ['subdomain' => 'darululoom'],
            [
                'name' => 'দারুল উলূম ঢাকা ইসলামিয়া মাদ্রাসা',
                'slug' => 'darul-uloom-dhaka',
                'custom_domain' => 'lms.darululoom.edu.bd',
                'type' => 'madrasah',
                'email' => 'contact@darululoom.edu.bd',
                'phone' => '+8801711223344',
                'address' => 'যাত্রাবাড়ী, ঢাকা-১২০৪, বাংলাদেশ',
                'branding' => [
                    'logo_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=120&q=80',
                    'seal_url' => 'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&w=120&q=80',
                    'primary_color' => '#065f46',
                    'secondary_color' => '#047857',
                    'motto' => 'আল-কুরআন ও সুন্নাহর আলোকে ইলম ও আমলের সমন্বয়',
                ],
                'plan' => 'enterprise_madrasah',
                'seat_limit' => 100,
                'used_seats' => 0,
                'status' => 'active',
                'subscription_ends_at' => now()->addYear(),
            ]
        );

        // Bind tenant 1 context
        TenantContext::setTenant($org1);

        // Users for Org 1
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@darululoom.edu.bd'],
            [
                'name' => 'মুফতি জুবায়ের আহমদ (মুহতামিম)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher@darululoom.edu.bd'],
            [
                'name' => 'মাওলানা মাহমুদুল হাসান (মুদাররিস)',
                'password' => Hash::make('password123'),
                'role' => 'instructor',
            ]
        );

        $guardian1 = User::firstOrCreate(
            ['email' => 'guardian@darululoom.edu.bd'],
            [
                'name' => 'মাওলানা রফিকুল ইসলাম (অভিভাবক)',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        $student1 = User::firstOrCreate(
            ['email' => 'student1@darululoom.edu.bd'],
            [
                'name' => 'আব্দুর রহমান',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        $student2 = User::firstOrCreate(
            ['email' => 'student2@darululoom.edu.bd'],
            [
                'name' => 'আব্দুল্লাহ আল কাফি',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        // Memberships in Org 1
        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'user_id' => $admin1->id, 'role' => 'org_admin'],
            ['id_number' => 'ADM-01', 'status' => 'active', 'joined_at' => now()]
        );
        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'user_id' => $teacher1->id, 'role' => 'teacher'],
            ['id_number' => 'TCH-01', 'status' => 'active', 'joined_at' => now()]
        );
        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'user_id' => $guardian1->id, 'role' => 'guardian'],
            ['id_number' => 'GRD-01', 'status' => 'active', 'joined_at' => now()]
        );
        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'user_id' => $student1->id, 'role' => 'student'],
            ['id_number' => 'দ-১০১', 'guardian_user_id' => $guardian1->id, 'status' => 'active', 'joined_at' => now()]
        );
        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'user_id' => $student2->id, 'role' => 'student'],
            ['id_number' => 'দ-১০২', 'guardian_user_id' => null, 'status' => 'active', 'joined_at' => now()]
        );

        $org1->syncUsedSeats();

        // Subscription for Org 1
        OrganizationSubscription::firstOrCreate(
            ['organization_id' => $org1->id],
            [
                'plan_name' => 'enterprise_madrasah',
                'seat_count' => 100,
                'price_per_seat' => 45.00,
                'total_amount' => 4500.00,
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'status' => 'active',
            ]
        );

        // Cohort 1: Hifz Halaqa
        $cohort1 = Cohort::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'slug' => 'hifz-halaqa-alif'],
            [
                'name' => 'হিফজুল কুরআন হালাকা (গ্রুপ আলিফ)',
                'academic_year' => '1447-1448 AH',
                'head_teacher_id' => $teacher1->id,
                'room_number' => '১০১ (মসজিদ হল)',
                'description' => 'তাহফিজুল কুরআনুল কারীম দৈনিক তাজবিদ ও হিফজ সবক/আমোখতা হালাকা।',
                'status' => 'active',
            ]
        );

        // Assign cohort members
        CohortMember::updateOrCreate(
            ['cohort_id' => $cohort1->id, 'user_id' => $student1->id],
            ['role' => 'student', 'roll_number' => '১']
        );
        CohortMember::updateOrCreate(
            ['cohort_id' => $cohort1->id, 'user_id' => $student2->id],
            ['role' => 'student', 'roll_number' => '২']
        );

        // Attendance records for Org 1
        Attendance::withoutGlobalScopes()->where('cohort_id', $cohort1->id)->delete();
        Attendance::withoutGlobalScopes()->create([
            'cohort_id' => $cohort1->id,
            'user_id' => $student1->id,
            'date' => now()->toDateString(),
            'session_name' => 'fajr_halqa',
            'organization_id' => $org1->id,
            'marked_by' => $teacher1->id,
            'status' => 'present',
            'remarks' => 'সবক ও আমোখতা শোনানো হয়েছে',
        ]);
        Attendance::withoutGlobalScopes()->create([
            'cohort_id' => $cohort1->id,
            'user_id' => $student2->id,
            'date' => now()->toDateString(),
            'session_name' => 'fajr_halqa',
            'organization_id' => $org1->id,
            'marked_by' => $teacher1->id,
            'status' => 'present',
            'remarks' => 'মাশাআল্লাহ চমৎকার তাজবিদ',
        ]);

        // Exam 1: Hifz & Tajweed
        $exam1 = Exam::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org1->id, 'title' => 'হিফজুল কুরআন অর্ধ-বার্ষিক পরীক্ষা ১৪৪৭'],
            [
                'cohort_id' => $cohort1->id,
                'created_by' => $teacher1->id,
                'exam_type' => 'oral_hifz',
                'duration_minutes' => 60,
                'total_marks' => 100,
                'pass_marks' => 50,
                'question_bank' => [
                    ['id' => 1, 'question' => 'সূরা মুলক সম্পূর্ণ মুখস্থ পাঠ (তাজবিদসহ)', 'points' => 50],
                    ['id' => 2, 'question' => 'সূরা ওয়াকিয়া পাঠ ও মাখরাজ পরীক্ষা', 'points' => 50],
                ],
                'status' => 'published',
            ]
        );

        // Exam Submissions
        ExamSubmission::updateOrCreate(
            ['exam_id' => $exam1->id, 'user_id' => $student1->id],
            [
                'answers' => ['1' => 'সমাপ্ত', '2' => 'সমাপ্ত'],
                'auto_score' => 0,
                'manual_score' => 95,
                'total_score' => 95,
                'percentage' => 95.0,
                'grade' => 'মুমতায (ممتاز - চমৎকার)',
                'status' => 'graded',
                'graded_by' => $teacher1->id,
                'feedback' => 'মাশাআল্লাহ! অত্যন্ত নিখুঁত তেলাওয়াত ও সুর।',
            ]
        );
        ExamSubmission::updateOrCreate(
            ['exam_id' => $exam1->id, 'user_id' => $student2->id],
            [
                'answers' => ['1' => 'সমাপ্ত', '2' => 'সমাপ্ত'],
                'auto_score' => 0,
                'manual_score' => 82,
                'total_score' => 82,
                'percentage' => 82.0,
                'grade' => 'জায়্যিদ জিদ্দান (جيد جداً - অতি উত্তম)',
                'status' => 'graded',
                'graded_by' => $teacher1->id,
                'feedback' => 'ভালো হয়েছে। গুন্নাহর দিকে আরও নজর দিতে হবে।',
            ]
        );

        // GradeBooks (Report Cards)
        GradeBook::withoutGlobalScopes()->updateOrCreate(
            ['cohort_id' => $cohort1->id, 'user_id' => $student1->id, 'term' => 'ষান্মাসিক পরীক্ষা ১৪৪৭'],
            [
                'organization_id' => $org1->id,
                'scores_breakdown' => [['exam' => $exam1->title, 'score' => 95, 'total' => 100]],
                'total_marks' => 100,
                'obtained_marks' => 95,
                'overall_percentage' => 95.0,
                'overall_grade' => 'মুমতায (ممتاز - চমৎকার)',
                'position_in_class' => 1,
                'remarks' => 'শ্রেণিতে ১ম স্থান অধিকার করেছে।',
                'is_published' => true,
            ]
        );
        GradeBook::withoutGlobalScopes()->updateOrCreate(
            ['cohort_id' => $cohort1->id, 'user_id' => $student2->id, 'term' => 'ষান্মাসিক পরীক্ষা ১৪৪৭'],
            [
                'organization_id' => $org1->id,
                'scores_breakdown' => [['exam' => $exam1->title, 'score' => 82, 'total' => 100]],
                'total_marks' => 100,
                'obtained_marks' => 82,
                'overall_percentage' => 82.0,
                'overall_grade' => 'জায়্যিদ জিদ্দান (جيد جداً - অতি উত্তম)',
                'position_in_class' => 2,
                'remarks' => 'শ্রেণিতে ২য় স্থান অধিকার করেছে।',
                'is_published' => true,
            ]
        );

        // Branded Certificate for Student 1
        OrganizationCertificate::withoutGlobalScopes()->updateOrCreate(
            ['certificate_number' => 'TAALLUM-'.$org1->id.'-1447-HIFZ01'],
            [
                'organization_id' => $org1->id,
                'user_id' => $student1->id,
                'cohort_id' => $cohort1->id,
                'title' => 'হিফজুল কুরআন সমাপন সনদপত্র',
                'recipient_name' => $student1->name,
                'issued_date' => now()->toDateString(),
                'custom_metadata' => [
                    'org_name' => $org1->name,
                    'signers' => 'মুফতি জুবায়ের আহমদ (মুহতামিম)',
                    'primary_color' => '#065f46',
                ],
            ]
        );

        // ----------------- Organization 2: Markazus Shariah -----------------
        $org2 = Organization::updateOrCreate(
            ['subdomain' => 'markaz'],
            [
                'name' => 'মারকাযুশ শরীয়াহ ইসলামিক রিসার্চ একাডেমি',
                'slug' => 'markazus-shariah-academy',
                'custom_domain' => 'academy.markaz.edu.bd',
                'type' => 'islamic_institute',
                'email' => 'info@markaz.edu.bd',
                'phone' => '+8801811998877',
                'address' => 'উত্তরা, ঢাকা-১২৩০, বাংলাদেশ',
                'branding' => [
                    'logo_url' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=120&q=80',
                    'primary_color' => '#1e3a8a',
                    'secondary_color' => '#1d4ed8',
                    'motto' => 'সমকালীন প্রেক্ষাপটে ইসলামী অর্থায়ন ও ফিকহ গবেষণা',
                ],
                'plan' => 'enterprise_starter',
                'seat_limit' => 50,
                'used_seats' => 0,
                'status' => 'active',
                'subscription_ends_at' => now()->addMonths(6),
            ]
        );

        // Bind tenant 2 context
        TenantContext::setTenant($org2);

        $admin2 = User::firstOrCreate(
            ['email' => 'admin@markaz.edu.bd'],
            [
                'name' => 'ড. আব্দুল্লাহ জাহেদ (পরিচালক)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        OrganizationMember::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org2->id, 'user_id' => $admin2->id, 'role' => 'org_admin'],
            ['id_number' => 'DIR-01', 'status' => 'active', 'joined_at' => now()]
        );

        $cohort2 = Cohort::withoutGlobalScopes()->updateOrCreate(
            ['organization_id' => $org2->id, 'slug' => 'islamic-banking-batch-01'],
            [
                'name' => 'ইসলামিক ব্যাংকিং ও সুকুক ডিপ্লোমা ব্যাচ-১',
                'academic_year' => '২০২৬ সেশন',
                'head_teacher_id' => $admin2->id,
                'room_number' => 'সেমিনার হল-২',
                'description' => 'মুরাবাহা, মুশারাকা ও ইসলামিক ফিনান্স চুক্তি বিষয়ক এক্সক্লুসিভ ডিপ্লোমা।',
                'status' => 'active',
            ]
        );

        $org2->syncUsedSeats();

        // Clear Tenant Context after seed
        TenantContext::clearTenant();
    }
}
