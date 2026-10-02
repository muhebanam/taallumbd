<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'designation', 'headline', 'short_bio', 'bio',
        'avatar', 'cover_photo', 'location', 'email', 'phone', 'website',
        'facebook_url', 'youtube_url', 'linkedin_url', 'twitter_url',
        'instagram_url', 'telegram_url', 'specialties', 'knowledge_path',
        'expertise_map', 'qualifications', 'experiences', 'office_hours',
        'consultation_enabled', 'consultation_note', 'status', 'featured',
        'is_verified', 'verified_at', 'allow_follow', 'show_email',
        'show_phone', 'sort_order'
    ];

    protected $casts = [
        'specialties' => 'array',
        'knowledge_path' => 'array',
        'expertise_map' => 'array',
        'qualifications' => 'array',
        'experiences' => 'array',
        'office_hours' => 'array',
        'consultation_enabled' => 'boolean',
        'featured' => 'boolean',
        'is_verified' => 'boolean',
        'allow_follow' => 'boolean',
        'show_email' => 'boolean',
        'show_phone' => 'boolean',
        'verified_at' => 'datetime',
        'sort_order' => 'integer'
    ];

    protected $appends = [
        'avatar_url',
        'cover_photo_url',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'teacher_followers', 'teacher_id', 'user_id')->withTimestamps();
    }

    public function teacherQuestions()
    {
        return $this->hasMany(Fatwa::class, 'teacher_id');
    }

    public function teacherReviews()
    {
        return $this->hasMany(Review::class, 'teacher_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id', 'user_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id', 'user_id');
    }

    public function fatawa()
    {
        return $this->hasMany(Fatwa::class, 'answered_by', 'user_id');
    }

    public function publications()
    {
        return $this->hasMany(Publication::class, 'user_id', 'user_id');
    }

    // Scopes
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeFeatured($q)
    {
        return $q->where('featured', true);
    }

    public function scopeVerified($q)
    {
        return $q->where('is_verified', true);
    }

    public function scopeSearch($q, ?string $search)
    {
        if (!$search) return $q;
        return $q->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('headline', 'like', "%{$search}%");
        });
    }

    public function scopeSpecialty($q, ?string $specialty)
    {
        if (!$specialty || $specialty === 'সবাই') return $q;
        return $q->whereJsonContains('specialties', $specialty);
    }

    // Accessors
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http') || str_starts_with($this->avatar, '/')) {
                return $this->avatar;
            }
            return '/storage/' . ltrim($this->avatar, '/');
        }
        $bg = '102526';
        $color = 'fff99a';
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background={$bg}&color={$color}&size=150";
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        if ($this->cover_photo) {
            if (str_starts_with($this->cover_photo, 'http') || str_starts_with($this->cover_photo, '/')) {
                return $this->cover_photo;
            }
            return '/storage/' . ltrim($this->cover_photo, '/');
        }
        return null;
    }

    public function getFollowersCountAttribute(): int
    {
        if (array_key_exists('followers_count', $this->attributes)) {
            return (int) $this->attributes['followers_count'];
        }

        return $this->followers()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        if (array_key_exists('average_rating', $this->attributes) && $this->attributes['average_rating'] !== null) {
            return round((float) $this->attributes['average_rating'], 1);
        }

        $avg = $this->teacherReviews()->where('status', 'approved')->avg('rating');
        return $avg ? round((float)$avg, 1) : 0.0;
    }

    public function getProfileCompletionPercentageAttribute(): int
    {
        $fields = [
            'name' => 10,
            'designation' => 10,
            'headline' => 10,
            'avatar' => 10,
            'cover_photo' => 5,
            'short_bio' => 10,
            'bio' => 10,
            'specialties' => 10,
            'expertise_map' => 5,
            'qualifications' => 10,
            'experiences' => 5,
            'knowledge_path' => 5,
        ];

        $score = 0;
        foreach ($fields as $field => $weight) {
            if ($this->{$field} && (!is_array($this->{$field}) || count($this->{$field}) > 0)) {
                $score += $weight;
            }
        }
        // If facebook_url, youtube_url, or other social link exists
        $hasSocial = $this->facebook_url || $this->youtube_url || $this->linkedin_url || $this->twitter_url || $this->instagram_url || $this->telegram_url || $this->website;
        if ($hasSocial) {
            $score += 5;
        }

        return min($score, 100);
    }

    public function getProfileCompletionSuggestionsAttribute(): array
    {
        $suggestions = [];
        if (!$this->avatar) $suggestions[] = 'প্রোফাইল ছবি যুক্ত করুন';
        if (!$this->cover_photo) $suggestions[] = 'কভার ছবি যুক্ত করুন';
        if (!$this->designation) $suggestions[] = 'পদবি / পরিচয় যুক্ত করুন';
        if (!$this->headline) $suggestions[] = 'সংক্ষিপ্ত হেডলাইন যুক্ত করুন';
        if (!$this->short_bio) $suggestions[] = 'সংক্ষিপ্ত পরিচিতি লিখুন';
        if (!$this->bio) $suggestions[] = 'বিস্তারিত বায়ো লিখুন';
        if (empty($this->specialties)) $suggestions[] = 'বিশেষজ্ঞতা বা পড়ানোর বিষয়সমূহ যুক্ত করুন';
        if (empty($this->expertise_map)) $suggestions[] = 'বিশেষজ্ঞতার বিস্তারিত ক্ষেত্র (Expertise Map) যুক্ত করুন';
        if (empty($this->qualifications)) $suggestions[] = 'শিক্ষাগত যোগ্যতা যুক্ত করুন';
        if (empty($this->experiences)) $suggestions[] = 'কাজের অভিজ্ঞতা যুক্ত করুন';
        if (empty($this->knowledge_path)) $suggestions[] = 'শিক্ষার্থীরা কী শিখবে (Knowledge Path) যুক্ত করুন';
        
        $hasSocial = $this->facebook_url || $this->youtube_url || $this->linkedin_url || $this->twitter_url || $this->instagram_url || $this->telegram_url || $this->website;
        if (!$hasSocial) $suggestions[] = 'সোশ্যাল মিডিয়া লিংক যুক্ত করুন';

        return $suggestions;
    }
}
