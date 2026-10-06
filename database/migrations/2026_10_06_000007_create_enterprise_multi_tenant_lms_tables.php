<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Organizations table
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 120)->unique();
            $table->string('subdomain', 64)->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('type', 32)->default('madrasah'); // madrasah, islamic_institute, mosque_maktab, school, other
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('branding')->nullable(); // logo_url, seal_url, primary_color, secondary_color, motto
            $table->string('plan', 64)->default('enterprise_starter');
            $table->unsignedInteger('seat_limit')->default(50);
            $table->unsignedInteger('used_seats')->default(0);
            $table->string('status', 32)->default('active'); // active, trial, suspended, cancelled
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Organization Members (roles: org_admin, teacher, student, guardian)
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('student'); // org_admin, teacher, student, guardian
            $table->string('id_number', 64)->nullable(); // Roll number or Staff ID
            $table->foreignId('guardian_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('active'); // active, inactive, suspended
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id', 'role']);
            $table->index(['organization_id', 'role']);
        });

        // 3. Cohorts / Classes / Halaqat (শ্রেণি বা হালাকা)
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 120);
            $table->string('academic_year', 64)->default('1447-1448 AH');
            $table->foreignId('head_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('room_number', 64)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('active'); // active, completed, archived
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });

        // 4. Cohort Members (Students & Teachers enrolled in a cohort)
        Schema::create('cohort_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('student'); // student, teacher
            $table->string('roll_number', 64)->nullable();
            $table->timestamps();

            $table->unique(['cohort_id', 'user_id']);
        });

        // 5. Cohort Course Mapping
        Schema::create('cohort_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['cohort_id', 'course_id']);
        });

        // 6. Attendance Table
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('session_name', 64)->default('daily'); // daily, fajr_halqa, morning, evening
            $table->string('status', 32)->default('present'); // present, absent, late, excused
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['cohort_id', 'user_id', 'date', 'session_name'], 'attendance_cohort_user_date_session_uq');
            $table->index(['organization_id', 'date']);
        });

        // 7. Exams & Question Bank
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('exam_type', 32)->default('quiz_mcq'); // quiz_mcq, written, oral_hifz, hybrid
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->decimal('total_marks', 8, 2)->default(100.00);
            $table->decimal('pass_marks', 8, 2)->default(40.00);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->json('question_bank')->nullable(); // array of questions, options, correct answers
            $table->boolean('randomize_questions')->default(true);
            $table->string('status', 32)->default('draft'); // draft, published, in_progress, grading, published_results
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });

        // 8. Exam Submissions & Grading
        Schema::create('exam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->json('answers')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('auto_score', 8, 2)->default(0.00);
            $table->decimal('manual_score', 8, 2)->default(0.00);
            $table->decimal('total_score', 8, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->string('grade', 32)->nullable(); // ممتاز (Mumtaz), جيد جدا (Jayyid Jiddan), جيد (Jayyid), راسب (Rasib)
            $table->string('status', 32)->default('submitted'); // submitted, graded, reviewed
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'user_id']);
        });

        // 9. Grade Books (Term Report Cards)
        Schema::create('grade_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->string('term', 120); // e.g. "ষান্মাসিক পরীক্ষা ১৪৪৭", "বার্ষিক মূল্যায়ন ২০২৬"
            $table->json('scores_breakdown')->nullable();
            $table->decimal('total_marks', 8, 2)->default(0.00);
            $table->decimal('obtained_marks', 8, 2)->default(0.00);
            $table->decimal('overall_percentage', 5, 2)->default(0.00);
            $table->string('overall_grade', 32)->default('মাকবুল');
            $table->unsignedInteger('position_in_class')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['cohort_id', 'user_id', 'term']);
            $table->index(['organization_id', 'term']);
        });

        // 10. Organization Certificates (Branded Bulk Certificates)
        Schema::create('organization_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_number', 64)->unique();
            $table->string('title');
            $table->string('recipient_name');
            $table->date('issued_date');
            $table->json('custom_metadata')->nullable(); // seal_url, signers, grades
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'issued_date']);
        });

        // 11. Organization Subscriptions (Seat-based billing)
        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('plan_name', 64)->default('enterprise_madrasah');
            $table->unsignedInteger('seat_count')->default(50);
            $table->decimal('price_per_seat', 8, 2)->default(50.00);
            $table->decimal('total_amount', 8, 2)->default(2500.00);
            $table->string('billing_cycle', 32)->default('monthly'); // monthly, quarterly, yearly
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 32)->default('active'); // active, past_due, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_subscriptions');
        Schema::dropIfExists('organization_certificates');
        Schema::dropIfExists('grade_books');
        Schema::dropIfExists('exam_submissions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('cohort_course');
        Schema::dropIfExists('cohort_members');
        Schema::dropIfExists('cohorts');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
    }
};
