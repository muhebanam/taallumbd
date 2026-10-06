<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'phone',
        'referral_code', 'referred_by_id', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
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

    public function isEnrolled(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])->exists();
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
}
