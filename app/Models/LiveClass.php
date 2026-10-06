<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveClass extends Model
{
    use HasFactory;

    protected $table = 'live_classes';

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'session_type', // live_class, webinar, consultation
        'platform', // zoom, jitsi, youtube_live, google_meet, other
        'meeting_url',
        'recording_url',
        'start_time',
        'duration',
        'fee',
        'currency',
        'max_participants',
        'registered_count',
        'status', // scheduled, live, completed, cancelled
        'description',
        'topics',
        'reminder_sent_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'duration' => 'integer',
        'fee' => 'decimal:2',
        'max_participants' => 'integer',
        'registered_count' => 'integer',
        'topics' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function registrations()
    {
        return $this->hasMany(ScholarSessionRegistration::class, 'live_class_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'live_class_id');
    }

    public function curriculumItem()
    {
        return $this->morphOne(CurriculumItem::class, 'itemable');
    }

    public function isFree(): bool
    {
        return (float) $this->fee <= 0;
    }

    public function isRegisteredBy(User|int|null $user): bool
    {
        if (! $user) {
            return false;
        }

        $userId = $user instanceof User ? $user->id : (int) $user;

        return $this->registrations()->where('user_id', $userId)->where('status', 'registered')->exists();
    }

    public function canAccessMeeting(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin' || $user->id === $this->instructor_id) {
            return true;
        }

        // If part of course and user enrolled in course
        if ($this->course_id && $user->isEnrolled($this->course_id)) {
            return true;
        }

        return $this->isRegisteredBy($user);
    }
}
