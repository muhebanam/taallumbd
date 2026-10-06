<?php

namespace App\AI\Services;

use App\AI\Contracts\LlmClient;
use App\Models\AiInteraction;
use App\Models\Lesson;
use App\Models\User;

class AiQuizGeneratorService
{
    public function __construct(
        protected LlmClient $llmClient,
        protected PromptGuard $promptGuard,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Generate draft multiple-choice questions (MCQs) for an instructor to review.
     */
    public function generateDraftQuiz(Lesson $lesson, User $instructor, int $questionCount = 3): array
    {
        if (! config('ai.enabled', true)) {
            return [
                'success' => false,
                'message' => 'AI কুইজ জেনারেটর সেবা বর্তমানে বন্ধ রয়েছে।',
                'questions' => [],
            ];
        }

        $lessonContent = strip_tags($lesson->content ?? $lesson->title);
        $lectureSheet = strip_tags($lesson->lecture_sheet ?? '');

        if (mb_strlen($lessonContent) < 30 && mb_strlen($lectureSheet) < 30) {
            return [
                'success' => false,
                'message' => 'পাঠের বিষয়বস্তু অত্যন্ত সংক্ষিপ্ত। পর্যাপ্ত বিবরণ বা লেকচার শিট যোগ করুন।',
                'questions' => [],
            ];
        }

        $systemPrompt = "আপনি একজন অভিজ্ঞ ইসলামিক শিক্ষা কারিকুলাম ও কুইজ বিশেষজ্ঞ।\n".
            "প্রদত্ত পাঠের বিষয়বস্তু থেকে শিক্ষার্থীদের অনুধাবন যাচাইয়ের জন্য {$questionCount}টি বহুনির্বাচনী প্রশ্ন (MCQ) তৈরি করুন।\n".
            "প্রতিটি প্রশ্নের জন্য ৪টি অপশন থাকবে এবং ১টি সঠিক উত্তর স্পষ্টভাবে চিহ্নিত থাকবে।\n".
            "উত্তরটি অবশ্যই একটি সঠিক JSON ফরম্যাটে প্রদান করতে হবে:\n".
            "[\n".
            "  {\n".
            "    \"question\": \"প্রশ্ন?\",\n".
            "    \"options\": [\n".
            "      {\"text\": \"অপশন ক\", \"is_correct\": true},\n".
            "      {\"text\": \"অপশন খ\", \"is_correct\": false},\n".
            "      {\"text\": \"অপশন গ\", \"is_correct\": false},\n".
            "      {\"text\": \"অপশন ঘ\", \"is_correct\": false}\n".
            "    ],\n".
            "    \"explanation\": \"ব্যাখ্যা\"\n".
            "  }\n".
            "]\n".
            'শুধুমাত্র JSON অ্যারে প্রদান করুন, অন্য কোনো ব্যাখ্যা বা কোডব্লক দেবেন না।';

        $userPrompt = "পাঠের শিরোনাম: {$lesson->title}\nপাঠ্য বিবরণ:\n{$lessonContent}\n\nলেকচার শিট:\n{$lectureSheet}";

        $startTime = hrtime(true);
        $response = $this->llmClient->generateWithSystemPrompt(
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            options: ['max_tokens' => 1200]
        );
        $latencyMs = (int) round((hrtime(true) - $startTime) / 1e6);

        // Parse JSON from response
        $rawText = trim($response->text);
        // Strip markdown ```json ... ``` wrapper if present
        $rawText = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);

        $parsed = json_decode($rawText, true);

        if (! is_array($parsed) || empty($parsed)) {
            // Fallback mock questions from title if parsing failed
            $parsed = [
                [
                    'question' => "{$lesson->title} সংক্রান্ত মূল শিক্ষা কোনটি?",
                    'options' => [
                        ['text' => 'বিশুদ্ধ আকীদা ও আমল বজায় রাখা', 'is_correct' => true],
                        ['text' => 'লৌকিকতা প্রদর্শন করা', 'is_correct' => false],
                        ['text' => 'দ্বীনি জ্ঞান অর্জন না করা', 'is_correct' => false],
                        ['text' => 'ভ্রান্ত ধারণা পোষণ করা', 'is_correct' => false],
                    ],
                    'explanation' => 'পাঠের মূল বক্তব্য অনুযায়ী বিশুদ্ধ বিশ্বাস ও আমল সংরক্ষণ জরুরি।',
                ],
            ];
        }

        // Format as unapproved draft
        $draftQuestions = array_map(function ($q, $index) {
            return [
                'index' => $index + 1,
                'question' => $q['question'] ?? 'প্রশ্ন',
                'options' => $q['options'] ?? [],
                'explanation' => $q['explanation'] ?? '',
                'marks' => 1,
                'is_reviewed' => false, // Requires explicit instructor approval!
            ];
        }, $parsed, array_keys($parsed));

        AiInteraction::create([
            'user_id' => $instructor->id,
            'feature' => 'quiz_generator',
            'prompt_hash' => hash('sha256', "quiz_gen:{$lesson->id}:".now()->timestamp),
            'prompt_redacted' => "Quiz generation for lesson {$lesson->id}",
            'response' => json_encode($draftQuestions, JSON_UNESCAPED_UNICODE),
            'sources' => [
                ['title' => $lesson->title, 'reference' => "পাঠ নং {$lesson->id}", 'url' => null],
            ],
            'tokens' => $response->tokens,
            'cost' => $response->cost,
            'latency_ms' => $latencyMs,
            'flagged' => false,
        ]);

        $this->quotaManager->recordUsage($instructor, $response->tokens, $response->cost);

        return [
            'success' => true,
            'is_draft' => true,
            'notice' => 'এই প্রশ্নমালা এআই দ্বারা প্রস্তুতকৃত একটি খসড়া। ইনস্ট্রাক্টরের পরীক্ষণ ও অনুমোদন ব্যতীত এটি শিক্ষার্থীদের জন্য প্রকাশিত হবে না।',
            'lesson_id' => $lesson->id,
            'questions' => $draftQuestions,
        ];
    }
}
