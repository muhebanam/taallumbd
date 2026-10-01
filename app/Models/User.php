<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'phone'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isInstructor(): bool { return $this->role === 'instructor'; }

    public function courses() { return $this->hasMany(Course::class, 'instructor_id'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function enrolledCourses() { return $this->belongsToMany(Course::class, 'enrollments')->withPivot(['status', 'progress']); }
    public function lessonProgress() { return $this->hasMany(LessonProgress::class); }
    public function quizAttempts() { return $this->hasMany(QuizAttempt::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function articles() { return $this->hasMany(Article::class); }
    public function assignmentSubmissions() { return $this->hasMany(AssignmentSubmission::class); }
    public function orders() { return $this->hasMany(Order::class); }

    public function teacher() { return $this->hasOne(Teacher::class); }
    public function followedTeachers() { return $this->belongsToMany(Teacher::class, 'teacher_followers', 'user_id', 'teacher_id')->withTimestamps(); }
    public function teacherQuestions() { return $this->hasMany(Fatwa::class, 'question_user_id'); }
    public function teacherReviews() { return $this->hasMany(Review::class); }

    public function isEnrolled(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])->exists();
    }
}
