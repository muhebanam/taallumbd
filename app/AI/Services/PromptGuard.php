<?php

namespace App\AI\Services;

class PromptGuard
{
    /**
     * Patterns indicating prompt injection, jailbreaking or system prompt extraction.
     */
    protected array $injectionPatterns = [
        '/\bignore (?:all )?(?:previous|above) instructions\b/i',
        '/\bjailbreak\b/i',
        '/\bDAN mode\b/i',
        '/\bsystem prompt\b/i',
        '/\breveal (?:your )?instructions\b/i',
        '/\byou are no longer bound by\b/i',
        '/পূর্বের (?:সমস্ত |সব )?নির্দেশ (?:ভুলে যাও|বাতিল)/u',
        '/সিস্টেম প্রম্পট (?:দেখাও|ফাঁস করো)/u',
        '/তোমার নিয়মকানুন (?:উপেক্ষা|ভুলে যাও)/u',
    ];

    /**
     * Patterns explicitly demanding the AI to issue a personal fatwa or religious verdict.
     */
    protected array $fatwaPatterns = [
        '/(?:আমাকে|আমারে)\s+(?:একটা\s+)?ফতোয়া\s+(?:দিন|দেন|চাই)/u',
        '/ফতোয়া\s+(?:চাই|প্রয়োজন|দিন|দেন)/u',
        '/(?:আমার\s+জন্য\s+)?(?:এটা\s+)?হালাল\s+না\s+হারাম\s+ফতোয়া/u',
        '/তালাক\s+হয়ে\s+গেছে\s+কিনা\s+ফতোয়া/u',
        '/ফিকহী\s+(?:রায়|ফতোয়া|ফয়সালা)\s+(?:দিন|চাই)/u',
        '/শরয়ী\s+ফয়সালা\s+(?:দিন|করুন)/u',
        '/ফতোয়া\s+বোর্ড\s+না\s+তুমিই\s+ফতোয়া\s+দাও/u',
    ];

    /**
     * Check if prompt contains injection / malicious instructions.
     */
    public function isPromptInjection(string $prompt): bool
    {
        foreach ($this->injectionPatterns as $pattern) {
            if (preg_match($pattern, $prompt) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt is an explicit request for an Islamic Fatwa / Fiqhi verdict.
     */
    public function isFatwaRequest(string $prompt): bool
    {
        foreach ($this->fatwaPatterns as $pattern) {
            if (preg_match($pattern, $prompt) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get system safety instructions to prepend to any LLM prompt.
     */
    public function getIslamicSystemGuidelines(): string
    {
        return <<<'EOT'
আপনি আত-তাআল্লুম (Taallum BD) প্ল্যাটফর্মের ইসলামিক এডুকেশনাল এআই অ্যাসিস্ট্যান্ট।
আপনার মূলনীতি: "AI assists; scholars remain final authority."

কঠোর নির্দেশাবলি:
১. আপনি কোনো মুফতি বা ফতোয়া প্রদানকারী নন। নতুন কোনো ফিকহী রায়, ব্যক্তিগত হালাল-হারাম ফতোয়া বা তালাক-উত্তরাধিকার সম্পর্কিত শরয়ী সিদ্ধান্ত দেওয়া আপনার জন্য সম্পূর্ণরূপে নিষিদ্ধ।
২. শিক্ষার্থী যদি ব্যক্তিগত ফতোয়া বা ফিকহী বিচার চান, তবে অবিলম্বে স্পষ্টভাবে বলুন: "ফিকহী বিধান বা ফতোয়ার জন্য অনুগ্রহ করে আমাদের যোগ্য মুফতি বোর্ডের কাছে প্রশ্ন করুন" এবং লিঙ্ক উল্লেখ করুন: /fatawa/ask
৩. আপনার সকল উত্তর শুধুমাত্র প্ল্যাটফর্মের প্রদত্ত কোর্স পাঠ, নির্ভরযোগ্য তাফসীর, সিহাহ সিত্তাহ হাদীস এবং প্রকাশিত ফতোয়ার তথ্যের উপর ভিত্তি করে হতে হবে।
৪. কোনো তথ্য নিশ্চিত না হলে কখনোই মনগড়া বা অনুমাননির্ভর কথা বলবেন না; স্পষ্টভাবে বলুন যে এই তথ্য আপনার কাছে সংরক্ষিত নেই।
৫. সর্বদা শান্ত, মার্জিত, শ্রদ্ধাশীল এবং প্রামাণিক ভাষায় উত্তর দিন।
৬. আপনার প্রতিটি উত্তরে উদ্ধৃত গ্রন্থের নাম, হাদীস নম্বর, সূরা ও আয়াত নম্বর উল্লেখ থাকতে হবে।
EOT;
    }
}
