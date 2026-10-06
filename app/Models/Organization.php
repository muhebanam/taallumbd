<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'type',
        'email',
        'phone',
        'address',
        'branding',
        'plan',
        'seat_limit',
        'used_seats',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'seat_limit' => 'integer',
            'used_seats' => 'integer',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function gradeBooks(): HasMany
    {
        return $this->hasMany(GradeBook::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(OrganizationCertificate::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(OrganizationSubscription::class)->latestOfMany();
    }

    public function hasAvailableSeats(int $count = 1): bool
    {
        return ($this->used_seats + $count) <= $this->seat_limit;
    }

    public function syncUsedSeats(): int
    {
        $count = $this->members()->where('status', 'active')->count();
        $this->update(['used_seats' => $count]);

        return $count;
    }

    public function isSuspended(): bool
    {
        return in_array($this->status, ['suspended', 'cancelled'], true);
    }
}
