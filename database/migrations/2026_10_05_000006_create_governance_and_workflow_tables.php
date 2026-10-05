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
        // 1. Content Reviews Table (Audit trail & Workflow transitions)
        if (! Schema::hasTable('content_reviews')) {
            Schema::create('content_reviews', function (Blueprint $table) {
                $table->id();
                $table->string('reviewable_type');
                $table->unsignedBigInteger('reviewable_id');
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reviewer_role')->default('editor'); // editor, scholar_reviewer, admin
                $table->string('from_status')->default('draft');
                $table->string('to_status');
                $table->string('decision'); // submitted, approved, rejected, changes_requested, published
                $table->text('notes')->nullable();
                $table->integer('version')->default(1);
                $table->timestamps();

                $table->index(['reviewable_type', 'reviewable_id'], 'content_reviews_type_id_idx');
                $table->index(['to_status', 'reviewer_role'], 'content_reviews_status_role_idx');
            });
        }

        // 2. Scholar verification fields on teachers table
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (! Schema::hasColumn('teachers', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('teachers', 'verification_documents')) {
                    $table->text('verification_documents')->nullable(); // JSON list of private documents
                }
                if (! Schema::hasColumn('teachers', 'verification_checklist')) {
                    $table->text('verification_checklist')->nullable(); // JSON criteria
                }
                if (! Schema::hasColumn('teachers', 'verification_notes')) {
                    $table->text('verification_notes')->nullable();
                }
            });
        }

        // 3. Certified Course fields on courses table
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (! Schema::hasColumn('courses', 'is_certified')) {
                    $table->boolean('is_certified')->default(false)->index();
                }
                if (! Schema::hasColumn('courses', 'certified_by_scholar_id')) {
                    $table->foreignId('certified_by_scholar_id')->nullable()->constrained('teachers')->nullOnDelete();
                }
                if (! Schema::hasColumn('courses', 'certified_at')) {
                    $table->timestamp('certified_at')->nullable();
                }
                if (! Schema::hasColumn('courses', 'certification_note')) {
                    $table->text('certification_note')->nullable();
                }
            });
        }

        // 4. Enhanced Certificate fields (revocation)
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (! Schema::hasColumn('certificates', 'revoked_at')) {
                    $table->timestamp('revoked_at')->nullable()->index();
                }
                if (! Schema::hasColumn('certificates', 'revoked_reason')) {
                    $table->string('revoked_reason')->nullable();
                }
                if (! Schema::hasColumn('certificates', 'revoked_by')) {
                    $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        // 5. Certificate Verification Logs table
        if (! Schema::hasTable('certificate_verifications')) {
            Schema::create('certificate_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('certificate_id')->nullable()->constrained('certificates')->nullOnDelete();
                $table->string('identifier_searched')->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('status')->default('valid'); // valid, revoked, not_found
                $table->timestamp('verified_at');
                $table->timestamps();

                $table->index(['certificate_id', 'verified_at']);
            });
        }

        // 6. Policy Pages Table (Admin-editable Terms, Privacy, Refund, Content Policy, Fatwa Disclaimer)
        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->longText('body');
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('is_published')->default(true)->index();
                $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 7. Referrals & Funnel Tracking on users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'referral_code')) {
                    $table->string('referral_code', 32)->nullable()->unique();
                }
                if (! Schema::hasColumn('users', 'referred_by_id')) {
                    $table->foreignId('referred_by_id')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('users', 'utm_source')) {
                    $table->string('utm_source', 100)->nullable();
                }
                if (! Schema::hasColumn('users', 'utm_medium')) {
                    $table->string('utm_medium', 100)->nullable();
                }
                if (! Schema::hasColumn('users', 'utm_campaign')) {
                    $table->string('utm_campaign', 100)->nullable();
                }
                if (! Schema::hasColumn('users', 'utm_term')) {
                    $table->string('utm_term', 100)->nullable();
                }
                if (! Schema::hasColumn('users', 'utm_content')) {
                    $table->string('utm_content', 100)->nullable();
                }
            });
        }

        // 8. Referrals Table
        if (! Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('referred_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('code', 32)->index();
                $table->string('reward_status')->default('pending'); // pending, rewarded, expired
                $table->timestamp('converted_at')->nullable();
                $table->timestamps();
            });
        }

        // 9. Newsletter Subscribers Table (with Double Opt-in)
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('status')->default('pending'); // pending, active, unsubscribed
                $table->string('token', 64)->unique();
                $table->timestamp('verified_at')->nullable();
                $table->string('source')->default('website');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('referrals');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['referred_by_id']);
                $table->dropColumn([
                    'referral_code', 'referred_by_id',
                    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                ]);
            });
        }

        Schema::dropIfExists('pages');
        Schema::dropIfExists('certificate_verifications');

        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropForeign(['revoked_by']);
                $table->dropColumn(['revoked_at', 'revoked_reason', 'revoked_by']);
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['certified_by_scholar_id']);
                $table->dropColumn(['is_certified', 'certified_by_scholar_id', 'certified_at', 'certification_note']);
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn(['verified_by', 'verification_documents', 'verification_checklist', 'verification_notes']);
            });
        }

        Schema::dropIfExists('content_reviews');
    }
};
