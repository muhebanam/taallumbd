<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type', // public, private, course_linked
        'course_id',
        'creator_id',
        'invite_code',
        'max_members',
        'members_count',
        'is_archived',
        'weekly_goal',
        'weekly_goal_target',
        'weekly_goal_end_date',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'max_members' => 'integer',
        'members_count' => 'integer',
        'weekly_goal_target' => 'integer',
        'weekly_goal_end_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name).'-'.Str::random(5);
            }
            if (empty($group->invite_code)) {
                $group->invite_code = strtoupper(Str::random(8));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function members()
    {
        return $this->hasMany(StudyGroupMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(StudyGroupMember::class)->where('status', 'active');
    }

    public function posts()
    {
        return $this->hasMany(StudyGroupPost::class)->latest();
    }

    public function isMember(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return $this->members()->where('user_id', $userId)->where('status', 'active')->exists();
    }

    public function isOwner(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return $this->creator_id === $userId || $this->members()->where('user_id', $userId)->where('role', 'owner')->exists();
    }

    public function isModerator(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return $this->members()->where('user_id', $userId)->whereIn('role', ['owner', 'moderator'])->exists();
    }

    public function canView(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($this->type === 'public') {
            return true;
        }

        if ($this->type === 'private') {
            return $this->isMember($user);
        }

        if ($this->type === 'course_linked') {
            return $this->isMember($user) || ($this->course_id && $user->isEnrolled($this->course_id));
        }

        return false;
    }
}
