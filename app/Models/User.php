<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'phone',
        'preferred_locale', 'preferred_currency',
        'referral_code', 'referred_by_id', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content',
        'reputation_points', 'reputation_level', 'community_muted_until', 'is_banned',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'community_muted_until' => 'datetime',
            'is_banned' => 'boolean',
            'reputation_points' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (Schema::hasColumn('users', 'referral_code')) {
                if (empty($user->referral_code)) {
                    $user->referral_code = strtoupper(Str::random(8));
                }
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor' || $this->isAdmin();
    }

    public function isScholarReviewer(): bool
    {
        return $this->role === 'scholar_reviewer' || $this->isAdmin();
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        return in_array($this->role, $roles, true);
    }

    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withPivot(['status', 'progress']);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function teacherWallet()
    {
        return $this->hasOne(TeacherWallet::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function followedTeachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_followers', 'user_id', 'teacher_id')->withTimestamps();
    }

    public function teacherQuestions()
    {
        return $this->hasMany(Fatwa::class, 'question_user_id');
    }

    public function teacherReviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isEnrolled(Course|int|string $course): bool
    {
        $courseId = $course instanceof Course ? $course->id : (int) $course;

        return $this->enrollments()->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])->exists();
    }

    public function isEnrolledIn(Course|int|string $course): bool
    {
        return $this->isEnrolled($course);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function notificationChannelEnabled(string $type, string $channel): bool
    {
        return $this->notificationPreferences()
            ->where('notification_type', $type)
            ->where('channel', $channel)
            ->value('enabled') ?? true;
    }

    public function learningEvents()
    {
        return $this->hasMany(LearningEvent::class);
    }

    public function learningPathEnrollments()
    {
        return $this->hasMany(LearningPathEnrollment::class);
    }

    public function reputationLogs()
    {
        return $this->hasMany(ReputationPoint::class)->latest();
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class)->latest('awarded_at');
    }

    public function studyGroupMemberships()
    {
        return $this->hasMany(StudyGroupMember::class);
    }

    public function studyGroups()
    {
        return $this->belongsToMany(StudyGroup::class, 'study_group_members')
            ->withPivot(['role', 'status', 'weekly_goal_progress', 'weekly_goal_completed', 'joined_at'])
            ->withTimestamps();
    }

    public function sessionRegistrations()
    {
        return $this->hasMany(ScholarSessionRegistration::class);
    }

    public function isMutedInCommunity(): bool
    {
        return (bool) ($this->community_muted_until && $this->community_muted_until->isFuture());
    }
}
