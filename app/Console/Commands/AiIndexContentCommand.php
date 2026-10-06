<?php

namespace App\Console\Commands;

use App\AI\Services\RagService;
use App\Models\Article;
use App\Models\Ayah;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\Lesson;
use Illuminate\Console\Command;

class AiIndexContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taallum:ai-index {--type=all : Type of content to index (lessons, articles, fatawa, hadith, quran, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index and chunk platform Islamic content into AI Content Chunks for RAG retrieval';

    /**
     * Execute the console command.
     */
    public function handle(RagService $rag): int
    {
        $type = $this->option('type');
        $this->info("Starting AI content chunking & RAG indexing (Type: {$type})...");

        // 1. Lessons
        if ($type === 'all' || $type === 'lessons') {
            $lessons = Lesson::with('course')->get();
            $this->output->progressStart($lessons->count());
            foreach ($lessons as $lesson) {
                $rag->indexLesson($lesson);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->line(" <info>Indexed {$lessons->count()} lessons.</info>");
        }

        // 2. Articles
        if ($type === 'all' || $type === 'articles') {
            $articles = Article::published()->get();
            $this->output->progressStart($articles->count());
            foreach ($articles as $article) {
                $rag->indexArticle($article);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->line(" <info>Indexed {$articles->count()} published articles.</info>");
        }

        // 3. Fatawa
        if ($type === 'all' || $type === 'fatawa') {
            $fatawa = Fatwa::published()->get();
            $this->output->progressStart($fatawa->count());
            foreach ($fatawa as $fatwa) {
                $rag->indexFatwa($fatwa);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->line(" <info>Indexed {$fatawa->count()} published fatawa.</info>");
        }

        // 4. Hadith
        if ($type === 'all' || $type === 'hadith') {
            $hadiths = Hadith::with('book')->limit(100)->get();
            $this->output->progressStart($hadiths->count());
            foreach ($hadiths as $hadith) {
                $rag->indexHadith($hadith);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->line(" <info>Indexed {$hadiths->count()} hadiths.</info>");
        }

        // 5. Quran
        if ($type === 'all' || $type === 'quran') {
            $ayahs = Ayah::with(['surah', 'banglaTranslation'])->limit(100)->get();
            $this->output->progressStart($ayahs->count());
            foreach ($ayahs as $ayah) {
                $rag->indexAyah($ayah);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->line(" <info>Indexed {$ayahs->count()} ayahs.</info>");
        }

        $this->info('RAG indexing successfully completed.');

        return Command::SUCCESS;
    }
}
