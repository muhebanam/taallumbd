<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Teacher;

class SeoService
{
    /**
     * Generate Organization schema.
     */
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => 'আত-তাআল্লুম ডিজিটাল একাডেমি',
            'alternateName' => 'Taallum BD',
            'url' => url('/'),
            'logo' => url('/images/logo.png'),
            'description' => 'উলামায়ে কেরামের পরিচালনায় প্রাতিষ্ঠানিক সিলেবাসে আধুনিক অনলাইন ইসলামী একাডেমি।',
            'sameAs' => [
                'https://facebook.com/taallumbd',
                'https://youtube.com/@taallumbd',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+8801700000000',
                'contactType' => 'customer service',
                'availableLanguage' => ['Bengali', 'Arabic', 'English'],
            ],
        ];
    }

    /**
     * Generate Course schema.
     */
    public function course(Course $course): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => $course->short_description ?: strip_tags($course->description),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'আত-তাআল্লুম',
                'sameAs' => url('/'),
            ],
            'instructor' => [
                '@type' => 'Person',
                'name' => $course->instructor?->name ?? 'আত-তাআল্লুম অনুষদ',
            ],
            'offers' => [
                '@type' => 'Offer',
                'category' => $course->is_free ? 'Free' : 'Paid',
                'price' => $course->is_free ? '0.00' : (string) $course->price,
                'priceCurrency' => 'BDT',
                'availability' => 'https://schema.org/InStock',
                'url' => url('/courses/'.$course->slug),
            ],
            'hasCourseInstance' => [
                '@type' => 'CourseInstance',
                'courseMode' => 'Online',
                'inLanguage' => 'bn',
            ],
        ];
    }

    /**
     * Generate Person schema for Scholar.
     */
    public function scholar(Teacher $teacher): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $teacher->name,
            'jobTitle' => $teacher->designation,
            'description' => $teacher->short_bio ?: strip_tags($teacher->bio),
            'image' => $teacher->avatar ? (str_starts_with($teacher->avatar, 'http') ? $teacher->avatar : url('/storage/'.$teacher->avatar)) : url('/images/teachers/avatar-default.jpg'),
            'worksFor' => [
                '@type' => 'EducationalOrganization',
                'name' => 'আত-তাআল্লুম একাডেমি',
            ],
            'url' => url('/scholars/'.$teacher->slug),
            'knowsAbout' => $teacher->specialties ?? ['Islamic Studies', 'Quran', 'Hadith', 'Fiqh'],
        ];
    }

    /**
     * Generate Article schema.
     */
    public function article(Article $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt ?: strip_tags(substr($article->body, 0, 200)),
            'image' => $article->thumbnail ? (str_starts_with($article->thumbnail, 'http') ? $article->thumbnail : url('/storage/'.$article->thumbnail)) : url('/images/covers/cover_default.jpg'),
            'datePublished' => $article->published_at?->toIso8601String() ?? $article->created_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author?->name ?? 'আত-তাআল্লুম সম্পাদকীয় পরিষদ',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'আত-তাআল্লুম',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/images/logo.png'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url('/articles/'.$article->slug),
            ],
        ];
    }

    /**
     * Generate QAPage schema for Fatwa.
     */
    public function qaPage(Fatwa $fatwa): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'QAPage',
            'mainEntity' => [
                '@type' => 'Question',
                'name' => $fatwa->question_title,
                'text' => strip_tags($fatwa->question_body),
                'dateCreated' => $fatwa->created_at->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $fatwa->questioner_name ?: 'প্রশ্নকারী',
                ],
                'answerCount' => $fatwa->answer_body ? 1 : 0,
                'acceptedAnswer' => $fatwa->answer_body ? [
                    '@type' => 'Answer',
                    'text' => strip_tags($fatwa->answer_body),
                    'dateCreated' => $fatwa->answered_at?->toIso8601String() ?? $fatwa->updated_at->toIso8601String(),
                    'url' => url('/fatawa/'.$fatwa->id),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $fatwa->teacher?->name ?? ($fatwa->mufti?->name ?? 'আত-তাআল্লুম দারুল ইফতা'),
                    ],
                ] : null,
            ],
        ];
    }

    /**
     * Generate BreadcrumbList schema.
     */
    public function breadcrumbs(array $crumbs): array
    {
        $itemList = [];
        foreach ($crumbs as $idx => $crumb) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList,
        ];
    }
}
