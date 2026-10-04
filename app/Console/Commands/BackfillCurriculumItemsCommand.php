<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\CourseSection;
use App\Models\CurriculumItem;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Console\Command;

class BackfillCurriculumItemsCommand extends Command
{
    protected $signature = 'curriculum:backfill-items';

    protected $description = 'Backfills existing lessons, quizzes, and assignments into curriculum_items';

    public function handle()
    {
        $this->info('Starting curriculum items backfill...');

        // 1. Backfill Lessons
        $lessons = Lesson::all();
        $this->info("Found {$lessons->count()} lessons to backfill.");
        foreach ($lessons as $lesson) {
            CurriculumItem::updateOrCreate(
                [
                    'course_id' => $lesson->course_id,
                    'itemable_type' => Lesson::class,
                    'itemable_id' => $lesson->id,
                ],
                [
                    'section_id' => $lesson->section_id,
                    'item_type' => 'lesson',
                    'title_snapshot' => $lesson->title,
                    'sort_order' => $lesson->sort_order ?? 0,
                    'is_preview' => $lesson->is_preview ?? false,
                    'is_required' => true,
                ]
            );
        }

        // 2. Backfill Quizzes
        $quizzes = Quiz::all();
        $this->info("Found {$quizzes->count()} quizzes to backfill.");
        foreach ($quizzes as $index => $quiz) {
            $sectionId = null;
            if ($quiz->lesson) {
                $sectionId = $quiz->lesson->section_id;
            }
            if (! $sectionId) {
                $firstSection = CourseSection::where('course_id', $quiz->course_id)->orderBy('sort_order')->first();
                $sectionId = $firstSection?->id;
            }

            // Get max sort_order in section
            $maxSort = CurriculumItem::where('course_id', $quiz->course_id)
                ->where('section_id', $sectionId)
                ->max('sort_order') ?? 0;

            CurriculumItem::updateOrCreate(
                [
                    'course_id' => $quiz->course_id,
                    'itemable_type' => Quiz::class,
                    'itemable_id' => $quiz->id,
                ],
                [
                    'section_id' => $sectionId,
                    'item_type' => 'quiz',
                    'title_snapshot' => $quiz->title,
                    'sort_order' => $maxSort + 1,
                    'is_preview' => false,
                    'is_required' => true,
                ]
            );
        }

        // 3. Backfill Assignments
        $assignments = Assignment::all();
        $this->info("Found {$assignments->count()} assignments to backfill.");
        foreach ($assignments as $index => $assignment) {
            $sectionId = null;
            if ($assignment->lesson) {
                $sectionId = $assignment->lesson->section_id;
            }
            if (! $sectionId) {
                $firstSection = CourseSection::where('course_id', $assignment->course_id)->orderBy('sort_order')->first();
                $sectionId = $firstSection?->id;
            }

            // Get max sort_order in section
            $maxSort = CurriculumItem::where('course_id', $assignment->course_id)
                ->where('section_id', $sectionId)
                ->max('sort_order') ?? 0;

            CurriculumItem::updateOrCreate(
                [
                    'course_id' => $assignment->course_id,
                    'itemable_type' => Assignment::class,
                    'itemable_id' => $assignment->id,
                ],
                [
                    'section_id' => $sectionId,
                    'item_type' => 'assignment',
                    'title_snapshot' => $assignment->title,
                    'sort_order' => $maxSort + 1,
                    'is_preview' => false,
                    'is_required' => true,
                ]
            );
        }

        $this->info('Curriculum items backfill completed successfully!');

        return Command::SUCCESS;
    }
}
