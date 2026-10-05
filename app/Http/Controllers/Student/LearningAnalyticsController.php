<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LearningAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. Overall enrollment & completion progress
        $enrollments = Enrollment::where('user_id', $userId)->with('course:id,title')->get();
        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $avgProgress = $totalCourses > 0 ? (int) round($enrollments->avg('progress')) : 0;

        // 2 & 3. Single index-covered query for past 60 days events (streak & weekly time)
        $sixtyDaysAgo = now()->subDays(60)->startOfDay();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();

        $recentEventsList = LearningEvent::where('user_id', $userId)
            ->where('occurred_at', '>=', $sixtyDaysAgo)
            ->select(['id', 'event_type', 'occurred_at', 'properties', 'course_id'])
            ->get();

        $activeDatesSet = [];
        $weeklyEventsByDay = [];

        foreach ($recentEventsList as $event) {
            $dateStr = $event->occurred_at?->toDateString();
            if ($dateStr) {
                $activeDatesSet[$dateStr] = true;
                if ($event->occurred_at >= $sevenDaysAgo) {
                    $weeklyEventsByDay[$dateStr][] = $event;
                }
            }
        }

        // Streak Calculation
        $currentStreak = 0;
        $checkDate = now();

        if (! isset($activeDatesSet[$checkDate->toDateString()])) {
            $checkDate->subDay();
        }

        while (isset($activeDatesSet[$checkDate->toDateString()])) {
            $currentStreak++;
            $checkDate->subDay();
        }

        // Longest streak in past 60 days
        $longestStreak = 0;
        $tempStreak = 0;
        for ($i = 0; $i < 60; $i++) {
            $d = now()->subDays($i)->toDateString();
            if (isset($activeDatesSet[$d])) {
                $tempStreak++;
                if ($tempStreak > $longestStreak) {
                    $longestStreak = $tempStreak;
                }
            } else {
                $tempStreak = 0;
            }
        }

        // Weekly Time Spent (Past 7 days)
        $weeklyTime = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dayString = $day->toDateString();
            $dayName = $day->translatedFormat('D');

            $dayEvents = $weeklyEventsByDay[$dayString] ?? [];
            $minutes = 0;
            foreach ($dayEvents as $event) {
                $props = $event->properties ?: [];
                if (isset($props['duration_seconds'])) {
                    $minutes += round(((int) $props['duration_seconds']) / 60);
                } elseif ($event->event_type === 'lesson_completed') {
                    $minutes += 15;
                } elseif ($event->event_type === 'quiz_attempted') {
                    $minutes += 10;
                } elseif ($event->event_type === 'quran_read') {
                    $minutes += 10;
                }
            }

            $weeklyTime[] = [
                'date' => $dayString,
                'day' => $dayName,
                'minutes' => (int) $minutes,
                'events_count' => count($dayEvents),
            ];
        }

        // 4. Strengths & Weaknesses (Quiz Performance Analysis)
        $attempts = $user->quizAttempts()->with('quiz.course')->get();
        $quizStats = [];

        foreach ($attempts as $attempt) {
            $quizTitle = $attempt->quiz?->title ?: 'সাধারণ কুইজ';
            $courseTitle = $attempt->quiz?->course?->title ?: 'সাধারণ পাঠ';
            $key = $courseTitle.' - '.$quizTitle;

            if (! isset($quizStats[$key])) {
                $quizStats[$key] = [
                    'topic' => $key,
                    'course_title' => $courseTitle,
                    'attempts' => 0,
                    'passed' => 0,
                    'total_score' => 0,
                    'avg_score' => 0,
                ];
            }

            $quizStats[$key]['attempts']++;
            if ($attempt->status === 'passed') {
                $quizStats[$key]['passed']++;
            }
            $quizStats[$key]['total_score'] += (float) ($attempt->score ?? 0);
        }

        foreach ($quizStats as $key => $stat) {
            $quizStats[$key]['avg_score'] = (int) round($stat['total_score'] / max(1, $stat['attempts']));
            $quizStats[$key]['pass_rate'] = (int) round(($stat['passed'] / max(1, $stat['attempts'])) * 100);
        }

        $strengths = array_values(array_filter($quizStats, fn ($s) => $s['avg_score'] >= 75));
        $weaknesses = array_values(array_filter($quizStats, fn ($s) => $s['avg_score'] < 75));

        // 5. Recent learning activities
        $recentEvents = LearningEvent::where('user_id', $userId)
            ->with('course')
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'course_title' => $event->course?->title,
                    'properties' => $event->properties,
                    'occurred_at' => $event->occurred_at?->diffForHumans(),
                ];
            });

        return Inertia::render('Student/Analytics', [
            'metrics' => [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'avg_progress' => $avgProgress,
                'current_streak' => $currentStreak,
                'longest_streak' => max($longestStreak, $currentStreak),
                'total_study_minutes' => array_sum(array_column($weeklyTime, 'minutes')),
            ],
            'weekly_time' => $weeklyTime,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'recent_events' => $recentEvents,
        ]);
    }
}
