<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Study Groups
        if (! Schema::hasTable('study_groups')) {
            Schema::create('study_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->enum('type', ['public', 'private', 'course_linked'])->default('public');
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
                $table->string('invite_code')->unique();
                $table->unsignedInteger('max_members')->default(100);
                $table->unsignedInteger('members_count')->default(1);
                $table->boolean('is_archived')->default(false);
                $table->text('weekly_goal')->nullable();
                $table->unsignedInteger('weekly_goal_target')->default(100);
                $table->date('weekly_goal_end_date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('study_group_members')) {
            Schema::create('study_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('study_group_id')->constrained('study_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('role', ['owner', 'moderator', 'member'])->default('member');
                $table->enum('status', ['active', 'pending_approval', 'banned'])->default('active');
                $table->unsignedInteger('weekly_goal_progress')->default(0);
                $table->boolean('weekly_goal_completed')->default(false);
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();

                $table->unique(['study_group_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('study_group_posts')) {
            Schema::create('study_group_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('study_group_id')->constrained('study_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->longText('body');
                $table->json('attachments')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->unsignedInteger('likes_count')->default(0);
                $table->unsignedInteger('comments_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('study_group_comments')) {
            Schema::create('study_group_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('study_group_post_id')->constrained('study_group_posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        // 2. Scholar Sessions (Upgrade LiveClass)
        Schema::table('live_classes', function (Blueprint $table) {
            try {
                $table->foreignId('course_id')->nullable()->change();
            } catch (Throwable $e) {
                // SQLite may not support inline nullability changes
            }

            if (! Schema::hasColumn('live_classes', 'instructor_id')) {
                $table->foreignId('instructor_id')->nullable()->after('course_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('live_classes', 'session_type')) {
                $table->string('session_type')->default('live_class')->after('title'); // live_class, webinar, consultation
            }
            if (! Schema::hasColumn('live_classes', 'platform')) {
                $table->string('platform')->default('zoom')->after('session_type'); // zoom, jitsi, youtube_live, google_meet, other
            }
            if (! Schema::hasColumn('live_classes', 'recording_url')) {
                $table->string('recording_url')->nullable()->after('meeting_url');
            }
            if (! Schema::hasColumn('live_classes', 'fee')) {
                $table->decimal('fee', 10, 2)->default(0)->after('duration');
            }
            if (! Schema::hasColumn('live_classes', 'currency')) {
                $table->string('currency', 3)->default('BDT')->after('fee');
            }
            if (! Schema::hasColumn('live_classes', 'max_participants')) {
                $table->unsignedInteger('max_participants')->nullable()->after('currency');
            }
            if (! Schema::hasColumn('live_classes', 'registered_count')) {
                $table->unsignedInteger('registered_count')->default(0)->after('max_participants');
            }
            if (! Schema::hasColumn('live_classes', 'status')) {
                $table->string('status')->default('scheduled')->after('registered_count'); // scheduled, live, completed, cancelled
            }
            if (! Schema::hasColumn('live_classes', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
            if (! Schema::hasColumn('live_classes', 'topics')) {
                $table->json('topics')->nullable()->after('description');
            }
            if (! Schema::hasColumn('live_classes', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('topics');
            }
        });

        // 3. Scholar Session Registrations / Bookings
        if (! Schema::hasTable('scholar_session_registrations')) {
            Schema::create('scholar_session_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_class_id')->constrained('live_classes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->decimal('fee_paid', 10, 2)->default(0);
                $table->enum('status', ['registered', 'attended', 'cancelled'])->default('registered');
                $table->text('booking_notes')->nullable();
                $table->timestamp('attended_at')->nullable();
                $table->timestamps();

                $table->unique(['live_class_id', 'user_id']);
            });
        }

        // Allow orders to link to live_class
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->foreignId('course_id')->nullable()->change();
            } catch (Throwable $e) {
                // SQLite change guard
            }

            if (! Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->default('course')->after('user_id'); // course, scholar_session
            }
            if (! Schema::hasColumn('orders', 'live_class_id')) {
                $table->foreignId('live_class_id')->nullable()->after('course_id')->constrained('live_classes')->nullOnDelete();
            }
        });

        // 4. Reputation System & Badges
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'reputation_points')) {
                $table->unsignedInteger('reputation_points')->default(0)->after('role')->index();
            }
            if (! Schema::hasColumn('users', 'reputation_level')) {
                $table->string('reputation_level')->default('নবিশ তালিবুল ইলম')->after('reputation_points');
            }
            if (! Schema::hasColumn('users', 'community_muted_until')) {
                $table->timestamp('community_muted_until')->nullable()->after('reputation_level');
            }
            if (! Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('community_muted_until');
            }
        });

        if (! Schema::hasTable('reputation_points')) {
            Schema::create('reputation_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action'); // accepted_answer, upvote_received, course_completed, goal_completed, etc.
                $table->integer('points');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->foreignId('awarded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        if (! Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('badge_slug');
                $table->string('name');
                $table->string('icon')->default('Award');
                $table->string('description');
                $table->timestamp('awarded_at');
                $table->timestamps();

                $table->unique(['user_id', 'badge_slug']);
            });
        }

        // 5. Forum Upgrades: tags, downvotes, verified answers, threaded comments
        Schema::table('forum_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_posts', 'tags')) {
                $table->json('tags')->nullable()->after('topic');
            }
            if (! Schema::hasColumn('forum_posts', 'downvotes_count')) {
                $table->unsignedInteger('downvotes_count')->default(0)->after('upvotes_count');
            }
        });

        Schema::table('forum_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_comments', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('forum_comments')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('forum_comments', 'is_scholar_verified')) {
                $table->boolean('is_scholar_verified')->default(false)->after('body');
            }
            if (! Schema::hasColumn('forum_comments', 'verified_by_scholar_id')) {
                $table->foreignId('verified_by_scholar_id')->nullable()->after('is_scholar_verified')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('forum_comments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by_scholar_id');
            }
            if (! Schema::hasColumn('forum_comments', 'upvotes_count')) {
                $table->unsignedInteger('upvotes_count')->default(0)->after('verified_at');
            }
            if (! Schema::hasColumn('forum_comments', 'downvotes_count')) {
                $table->unsignedInteger('downvotes_count')->default(0)->after('upvotes_count');
            }
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_likes', 'vote_type')) {
                $table->string('vote_type')->default('upvote')->after('forum_comment_id'); // upvote, downvote
            }
        });

        // 6. Moderation: Reports Queue
        if (! Schema::hasTable('content_reports')) {
            Schema::create('content_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
                $table->string('reportable_type');
                $table->unsignedBigInteger('reportable_id');
                $table->string('reason'); // inappropriate, false_information, harassment, spam, heresy_or_misguidance, other
                $table->text('details')->nullable();
                $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');
                $table->string('action_taken')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['reportable_type', 'reportable_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_reports');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('reputation_points');
        Schema::dropIfExists('scholar_session_registrations');
        Schema::dropIfExists('study_group_comments');
        Schema::dropIfExists('study_group_posts');
        Schema::dropIfExists('study_group_members');
        Schema::dropIfExists('study_groups');
    }
};
