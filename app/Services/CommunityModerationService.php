<?php

namespace App\Services;

use App\Models\ContentReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommunityModerationService
{
    /**
     * Prohibited keywords list for auto-flagging spam, abuse, or offensive content.
     */
    protected array $prohibitedKeywords = [
        'জুয়া', 'ক্যাসিনো', 'casino', 'betting', '1xbet', 'free recharge',
        'নাস্তিক', 'কাফের বলে গালি', 'ফিতনা সৃষ্টিকারী লিংক', 'xxx', 'porn',
        'হ্যাক', 'হ্যাকিং সার্ভিস', 'bit.ly/spam',
    ];

    public function containsProhibitedKeywords(string $text): bool
    {
        $lowercase = mb_strtolower($text, 'UTF-8');

        foreach ($this->prohibitedKeywords as $keyword) {
            if (str_contains($lowercase, mb_strtolower($keyword, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Automatically scan and flag content if violating terms.
     */
    public function scanAndFlag(Model $content): bool
    {
        $text = '';
        if (isset($content->title)) {
            $text .= $content->title.' ';
        }
        if (isset($content->body)) {
            $text .= $content->body;
        }

        if ($this->containsProhibitedKeywords($text)) {
            // Auto hide/flag content
            if (isset($content->status)) {
                $content->status = 'hidden';
                $content->save();
            }

            // Create auto report
            ContentReport::firstOrCreate([
                'reporter_id' => $content->user_id, // Author flagged by system
                'reportable_type' => get_class($content),
                'reportable_id' => $content->getKey(),
                'reason' => 'spam',
            ], [
                'details' => 'স্বয়ংক্রিয় ফিল্টারে আপত্তিকর বা স্প্যাম শব্দাবলি ধরা পড়েছে।',
                'status' => 'pending',
            ]);

            AuditLoggerService::log(
                action: 'community.content_auto_flagged',
                modelType: get_class($content),
                modelId: $content->getKey(),
                payload: [
                    'author_id' => $content->user_id,
                    'reason' => 'keyword_match',
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * User report submission.
     */
    public function report(User $reporter, Model $content, string $reason, ?string $details = null): ContentReport
    {
        $report = ContentReport::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => get_class($content),
            'reportable_id' => $content->getKey(),
            'reason' => $reason,
            'details' => $details,
            'status' => 'pending',
        ]);

        AuditLoggerService::log(
            action: 'community.content_reported',
            modelType: get_class($content),
            modelId: $content->getKey(),
            payload: [
                'reporter_id' => $reporter->id,
                'reason' => $reason,
                'details' => $details,
            ]
        );

        return $report;
    }

    /**
     * Resolve report with action.
     */
    public function resolveReport(ContentReport $report, User $reviewer, string $action, ?int $muteHours = null): ContentReport
    {
        $content = $report->reportable;
        $author = $content?->user;

        switch ($action) {
            case 'hide_content':
                if ($content && isset($content->status)) {
                    $content->update(['status' => 'hidden']);
                }
                $report->action_taken = 'বিষয়বস্তু গোপন (Hidden) করা হয়েছে';
                break;

            case 'mute_author':
                if ($author) {
                    $hours = $muteHours ?: 24;
                    $author->update([
                        'community_muted_until' => now()->addHours($hours),
                    ]);
                    $report->action_taken = "ব্যবহারকারীকে {$hours} ঘণ্টার জন্য মিউট করা হয়েছে";
                }
                if ($content && isset($content->status)) {
                    $content->update(['status' => 'hidden']);
                }
                break;

            case 'ban_author':
                if ($author) {
                    $author->update(['is_banned' => true]);
                    $report->action_taken = 'ব্যবহারকারীকে কমিউনিটি থেকে নিষিদ্ধ (Banned) করা হয়েছে';
                }
                if ($content && isset($content->status)) {
                    $content->update(['status' => 'hidden']);
                }
                break;

            case 'dismiss':
            default:
                $report->action_taken = 'রিপোর্টটি ভিত্তিহীন বিবেচনা করে খারিজ করা হয়েছে';
                break;
        }

        $report->update([
            'status' => $action === 'dismiss' ? 'dismissed' : 'resolved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        AuditLoggerService::log(
            action: 'community.report_resolved',
            modelType: ContentReport::class,
            modelId: $report->id,
            payload: [
                'reviewer_id' => $reviewer->id,
                'action' => $action,
                'action_taken' => $report->action_taken,
                'reportable_type' => $report->reportable_type,
                'reportable_id' => $report->reportable_id,
            ]
        );

        return $report;
    }
}
