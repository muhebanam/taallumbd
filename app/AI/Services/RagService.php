<?php

namespace App\AI\Services;

use App\AI\Contracts\EmbeddingClient;
use App\Models\AiContentChunk;
use App\Models\Article;
use App\Models\Ayah;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\Lesson;

class RagService
{
    public function __construct(
        protected EmbeddingClient $embeddingClient
    ) {}

    /**
     * Search relevant content chunks for a user query.
     */
    public function search(string $query, int $limit = 4, ?string $onlyType = null): array
    {
        if (trim($query) === '') {
            return [];
        }

        $queryEmbedding = $this->embeddingClient->embedText($query);

        // If embeddings are supported, compute cosine similarity
        if (! empty($queryEmbedding)) {
            $chunksQuery = AiContentChunk::query()
                ->when($onlyType, fn ($q) => $q->where('chunkable_type', $onlyType))
                ->whereNotNull('embedding');

            $allChunks = $chunksQuery->take(200)->get();

            if ($allChunks->isNotEmpty()) {
                $scored = $allChunks->map(function ($chunk) use ($queryEmbedding) {
                    $score = $this->cosineSimilarity($queryEmbedding, (array) $chunk->embedding);

                    return [
                        'id' => $chunk->id,
                        'title' => $chunk->title,
                        'reference' => $chunk->source_reference,
                        'content' => $chunk->content,
                        'url' => $chunk->source_url,
                        'score' => $score,
                    ];
                });

                return $scored->sortByDesc('score')->take($limit)->values()->all();
            }
        }

        // Fallback: Text/keyword relevance search
        $terms = array_filter(explode(' ', $query), fn ($t) => mb_strlen($t) >= 2);
        if (empty($terms)) {
            $terms = [$query];
        }

        $fallbackQuery = AiContentChunk::query()
            ->when($onlyType, fn ($q) => $q->where('chunkable_type', $onlyType))
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('title', 'like', "%{$t}%")
                        ->orWhere('content', 'like', "%{$t}%")
                        ->orWhere('source_reference', 'like', "%{$t}%");
                }
            });

        return $fallbackQuery->limit($limit)->get()->map(function ($chunk) {
            return [
                'id' => $chunk->id,
                'title' => $chunk->title,
                'reference' => $chunk->source_reference,
                'content' => $chunk->content,
                'url' => $chunk->source_url,
                'score' => 0.5,
            ];
        })->all();
    }

    /**
     * Index a single lesson.
     */
    public function indexLesson(Lesson $lesson): AiContentChunk
    {
        $text = "{$lesson->title}\n".strip_tags($lesson->content ?? '')."\n".strip_tags($lesson->lecture_sheet ?? '');
        $url = $lesson->course ? route('courses.show', $lesson->course->slug ?? $lesson->course_id) : null;
        $ref = 'পাঠ: '.$lesson->title.($lesson->course ? ' ('.$lesson->course->title.')' : '');

        return $this->saveChunk(
            type: Lesson::class,
            id: $lesson->id,
            title: $lesson->title,
            content: mb_substr($text, 0, 1500),
            reference: $ref,
            url: $url
        );
    }

    /**
     * Index a single published article.
     */
    public function indexArticle(Article $article): AiContentChunk
    {
        $text = "{$article->title}\n".strip_tags($article->excerpt ?? '')."\n".strip_tags($article->body ?? '');
        $url = route('articles.show', $article->slug ?? $article->id);
        $ref = 'প্রবন্ধ: '.$article->title;

        return $this->saveChunk(
            type: Article::class,
            id: $article->id,
            title: $article->title,
            content: mb_substr($text, 0, 1500),
            reference: $ref,
            url: $url
        );
    }

    /**
     * Index a published fatwa.
     */
    public function indexFatwa(Fatwa $fatwa): AiContentChunk
    {
        $text = "প্রশ্ন: {$fatwa->question_title}\nবিবরণ: ".strip_tags($fatwa->question_body)."\nউত্তর: ".strip_tags($fatwa->answer_body ?? '');
        $url = route('fatawa.show', $fatwa->id);
        $ref = 'ফতোয়া নং '.$fatwa->id.': '.$fatwa->question_title;

        return $this->saveChunk(
            type: Fatwa::class,
            id: $fatwa->id,
            title: $fatwa->question_title,
            content: mb_substr($text, 0, 1500),
            reference: $ref,
            url: $url
        );
    }

    /**
     * Index a hadith.
     */
    public function indexHadith(Hadith $hadith): AiContentChunk
    {
        $bookName = $hadith->book?->name_bangla ?? 'হাদিস';
        $text = "{$bookName} নং {$hadith->number}\nবর্ণনাকারী: {$hadith->narrator}\nবাংলা: {$hadith->text_bangla}\nআরবি: {$hadith->text_arabic}";
        $url = url('/hadith/'.($hadith->book?->slug ?? 'sahih-bukhari')."?number={$hadith->number}");
        $ref = "{$bookName}: হাদিস নং {$hadith->number}";

        return $this->saveChunk(
            type: Hadith::class,
            id: $hadith->id,
            title: "{$bookName} - {$hadith->number}",
            content: mb_substr($text, 0, 1500),
            reference: $ref,
            url: $url
        );
    }

    /**
     * Index a Quran ayah.
     */
    public function indexAyah(Ayah $ayah): AiContentChunk
    {
        $surahName = $ayah->surah?->name_bangla ?? ('সূরা নং '.$ayah->surah_id);
        $translation = $ayah->banglaTranslation?->text ?? '';
        $text = "{$surahName} [আয়াত {$ayah->number}]\nআরবি: {$ayah->text_uthmani}\nঅর্থ: {$translation}";
        $url = url('/quran/'.($ayah->surah?->number ?? $ayah->surah_id)."?ayah={$ayah->number}");
        $ref = "আল-কুরআন: {$surahName} [{$ayah->surah_id}:{$ayah->number}]";

        return $this->saveChunk(
            type: Ayah::class,
            id: $ayah->id,
            title: "{$surahName} - আয়াত {$ayah->number}",
            content: mb_substr($text, 0, 1500),
            reference: $ref,
            url: $url
        );
    }

    /**
     * Save or update chunk with embedding.
     */
    protected function saveChunk(string $type, int $id, string $title, string $content, string $reference, ?string $url): AiContentChunk
    {
        $embedding = $this->embeddingClient->embedText($content);

        return AiContentChunk::updateOrCreate(
            [
                'chunkable_type' => $type,
                'chunkable_id' => $id,
            ],
            [
                'title' => $title,
                'content' => $content,
                'source_reference' => $reference,
                'source_url' => $url,
                'embedding' => ! empty($embedding) ? $embedding : null,
                'token_count' => (int) round(mb_strlen($content) / 4),
            ]
        );
    }

    /**
     * Cosine similarity between two float vectors.
     */
    protected function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = count($vecA);
        if ($count === 0 || $count !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];
            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
