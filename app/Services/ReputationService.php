<?php

namespace App\Services;

use App\Models\ReputationPoint;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    // Action point definitions
    public const ACTION_ACCEPTED_ANSWER = 'accepted_answer'; // +15

    public const ACTION_UPVOTE_RECEIVED = 'upvote_received'; // +5

    public const ACTION_COMMENT_UPVOTED = 'comment_upvoted'; // +2

    public const ACTION_COURSE_COMPLETED = 'course_completed'; // +50

    public const ACTION_GOAL_COMPLETED = 'goal_completed'; // +10

    public const ACTION_FIRST_DISCUSSION = 'first_discussion'; // +5

    protected array $pointValues = [
        self::ACTION_ACCEPTED_ANSWER => 15,
        self::ACTION_UPVOTE_RECEIVED => 5,
        self::ACTION_COMMENT_UPVOTED => 2,
        self::ACTION_COURSE_COMPLETED => 50,
        self::ACTION_GOAL_COMPLETED => 10,
        self::ACTION_FIRST_DISCUSSION => 5,
    ];

    /**
     * Award reputation points with anti-gaming guards.
     */
    public function awardPoints(User $user, string $action, ?Model $source = null, ?User $actor = null): ?int
    {
        // Anti-gaming: Do not allow self-awarding
        if ($actor && $actor->id === $user->id) {
            return null;
        }

        $points = $this->pointValues[$action] ?? 0;
        if ($points <= 0) {
            return null;
        }

        // Anti-gaming daily limit: Upvotes capped at 50 points per day
        if (in_array($action, [self::ACTION_UPVOTE_RECEIVED, self::ACTION_COMMENT_UPVOTED])) {
            $dailyUpvotePoints = ReputationPoint::where('user_id', $user->id)
                ->whereIn('action', [self::ACTION_UPVOTE_RECEIVED, self::ACTION_COMMENT_UPVOTED])
                ->where('created_at', '>=', now()->startOfDay())
                ->sum('points');

            if ($dailyUpvotePoints >= 50) {
                return null; // Daily cap reached
            }

            // Cap the award if it would exceed 50
            if (($dailyUpvotePoints + $points) > 50) {
                $points = 50 - $dailyUpvotePoints;
            }
        }

        // Prevent duplicate awards for single one-time actions on the same source
        if ($source && in_array($action, [self::ACTION_COURSE_COMPLETED, self::ACTION_FIRST_DISCUSSION, self::ACTION_GOAL_COMPLETED])) {
            $alreadyAwarded = ReputationPoint::where('user_id', $user->id)
                ->where('action', $action)
                ->where('source_type', get_class($source))
                ->where('source_id', $source->getKey())
                ->exists();

            if ($alreadyAwarded) {
                return null;
            }
        }

        DB::transaction(function () use ($user, $action, $points, $source, $actor) {
            ReputationPoint::create([
                'user_id' => $user->id,
                'action' => $action,
                'points' => $points,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->getKey(),
                'awarded_by_user_id' => $actor?->id,
                'created_at' => now(),
            ]);

            $newTotal = (int) ReputationPoint::where('user_id', $user->id)->sum('points');
            $newLevel = $this->calculateLevel($newTotal);

            $user->update([
                'reputation_points' => $newTotal,
                'reputation_level' => $newLevel,
            ]);

            $this->checkAndAwardBadges($user, $newTotal, $action);
        });

        return $points;
    }

    /**
     * Deduct reputation points (e.g. unvote or solution revoked).
     */
    public function deductPoints(User $user, string $action, ?Model $source = null, ?User $actor = null): ?int
    {
        $points = $this->pointValues[$action] ?? 0;
        if ($points <= 0) {
            return null;
        }

        // Find matching point log
        $logQuery = ReputationPoint::where('user_id', $user->id)
            ->where('action', $action);

        if ($source) {
            $logQuery->where('source_type', get_class($source))
                ->where('source_id', $source->getKey());
        }

        if ($actor) {
            $logQuery->where('awarded_by_user_id', $actor->id);
        }

        $log = $logQuery->latest('created_at')->first();
        if ($log) {
            $deducted = $log->points;
            $log->delete();

            $newTotal = max(0, (int) ReputationPoint::where('user_id', $user->id)->sum('points'));
            $user->update([
                'reputation_points' => $newTotal,
                'reputation_level' => $this->calculateLevel($newTotal),
            ]);

            return $deducted;
        }

        return null;
    }

    public function calculateLevel(int $points): string
    {
        return match (true) {
            $points >= 1000 => 'মুফীদুল উম্মাহ (শীর্ষ ইলমী ব্যক্তিত্ব)',
            $points >= 400 => 'মুয়াউয়িন (উম্মাহর একনিষ্ঠ সেবক)',
            $points >= 150 => 'মুজতাহিদ (সক্রিয় দ্বীনি গবেষক)',
            $points >= 50 => 'মুতাআল্লিম (জ্ঞান অন্বেষণকারী)',
            default => 'নবিশ তালিবুল ইলম',
        };
    }

    public function checkAndAwardBadges(User $user, int $totalPoints, string $triggerAction): void
    {
        $badgeDefinitions = [
            'first_discussion' => [
                'name' => 'প্রথম জিজ্ঞাসা',
                'icon' => 'MessageSquare',
                'description' => 'উন্মুক্ত কমিউনিটিতে প্রথম আলোচনা শুরু করেছেন',
                'condition' => fn () => $triggerAction === self::ACTION_FIRST_DISCUSSION,
            ],
            'scholar_verified_contributor' => [
                'name' => 'স্কলার-যাচাইকৃত অবদানকারী',
                'icon' => 'CheckCircle2',
                'description' => 'আপনার উত্তর বিজ্ঞ আলেম দ্বারা শরয়ীভাবে যাচাইকৃত হয়েছে',
                'condition' => fn () => $triggerAction === self::ACTION_ACCEPTED_ANSWER,
            ],
            'course_finisher' => [
                'name' => 'ইলমের যাত্রী',
                'icon' => 'GraduationCap',
                'description' => 'সফলভাবে একটি ইসলামিক কোর্স সম্পন্ন করেছেন',
                'condition' => fn () => $triggerAction === self::ACTION_COURSE_COMPLETED,
            ],
            'goal_crusher' => [
                'name' => 'লক্ষ্য অর্জনকারী',
                'icon' => 'Target',
                'description' => 'স্টাডি গ্রুপের সাপ্তাহিক দ্বীনি লক্ষ্য পূর্ণ করেছেন',
                'condition' => fn () => $triggerAction === self::ACTION_GOAL_COMPLETED,
            ],
            'top_contributor' => [
                'name' => 'উম্মাহর আলোকবর্তিকা',
                'icon' => 'Award',
                'description' => 'কমিউনিটিতে ১০০ এর অধিক রেপুটেশন পয়েন্ট অর্জন করেছেন',
                'condition' => fn () => $totalPoints >= 100,
            ],
        ];

        foreach ($badgeDefinitions as $slug => $def) {
            if ($def['condition']()) {
                UserBadge::firstOrCreate(
                    ['user_id' => $user->id, 'badge_slug' => $slug],
                    [
                        'name' => $def['name'],
                        'icon' => $def['icon'],
                        'description' => $def['description'],
                        'awarded_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Get Leaderboard.
     */
    public function getLeaderboard(string $period = 'all', int $limit = 10)
    {
        $query = User::select('id', 'name', 'avatar', 'role', 'reputation_points', 'reputation_level')
            ->where('is_banned', false);

        if ($period === 'weekly') {
            $recentUserIds = ReputationPoint::where('created_at', '>=', now()->startOfWeek())
                ->groupBy('user_id')
                ->selectRaw('user_id, SUM(points) as period_points')
                ->orderByDesc('period_points')
                ->limit($limit)
                ->pluck('period_points', 'user_id');

            return User::whereIn('id', $recentUserIds->keys())
                ->get()
                ->map(function ($u) use ($recentUserIds) {
                    $u->period_points = $recentUserIds[$u->id] ?? 0;

                    return $u;
                })
                ->sortByDesc('period_points')
                ->values();
        }

        return $query->orderByDesc('reputation_points')->limit($limit)->get();
    }
}
