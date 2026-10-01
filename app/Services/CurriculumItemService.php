<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CurriculumItem;
use Illuminate\Support\Facades\DB;

class CurriculumItemService
{
    public function createItemForModel(Course $course, ?CourseSection $section, $model, string $itemType, array $meta = []): CurriculumItem
    {
        $sortOrder = $meta['sort_order'] ?? (CurriculumItem::where('course_id', $course->id)->count() + 1);

        return CurriculumItem::updateOrCreate(
            [
                'course_id' => $course->id,
                'itemable_type' => get_class($model),
                'itemable_id' => $model->id,
            ],
            [
                'section_id' => $section?->id,
                'item_type' => $itemType,
                'title_snapshot' => $model->title,
                'sort_order' => $sortOrder,
                'is_preview' => $meta['is_preview'] ?? false,
                'is_required' => $meta['is_required'] ?? true,
                'drip_type' => $meta['drip_type'] ?? null,
                'drip_value' => $meta['drip_value'] ?? null,
            ]
        );
    }

    public function updateItemMeta($model, array $meta = []): ?CurriculumItem
    {
        $item = CurriculumItem::where('itemable_type', get_class($model))
            ->where('itemable_id', $model->id)
            ->first();

        if ($item) {
            $updateData = [];
            if (array_key_exists('section_id', $meta)) {
                $updateData['section_id'] = $meta['section_id'];
            }
            if (isset($meta['title'])) {
                $updateData['title_snapshot'] = $meta['title'];
            }
            if (isset($meta['sort_order'])) {
                $updateData['sort_order'] = $meta['sort_order'];
            }
            if (array_key_exists('is_preview', $meta)) {
                $updateData['is_preview'] = (bool) $meta['is_preview'];
            }
            if (array_key_exists('is_required', $meta)) {
                $updateData['is_required'] = (bool) $meta['is_required'];
            }
            if (array_key_exists('drip_type', $meta)) {
                $updateData['drip_type'] = $meta['drip_type'];
            }
            if (array_key_exists('drip_value', $meta)) {
                $updateData['drip_value'] = $meta['drip_value'];
            }

            $item->update($updateData);
        }

        return $item;
    }

    public function deleteItemForModel($model): void
    {
        CurriculumItem::where('itemable_type', get_class($model))
            ->where('itemable_id', $model->id)
            ->delete();
    }

    public function reorder(Course $course, array $orderedItems): void
    {
        $itemIds = collect($orderedItems)->pluck('id')->filter()->values();
        if ($itemIds->isNotEmpty()) {
            $foreignItemExists = CurriculumItem::whereIn('id', $itemIds)
                ->where('course_id', '!=', $course->id)
                ->exists();

            if ($foreignItemExists) {
                abort(403);
            }
        }

        DB::transaction(function () use ($orderedItems) {
            foreach ($orderedItems as $index => $itemData) {
                if (isset($itemData['id'])) {
                    CurriculumItem::where('id', $itemData['id'])->update([
                        'section_id' => $itemData['section_id'] ?? null,
                        'sort_order' => $itemData['sort_order'] ?? ($index + 1),
                    ]);
                }
            }
        });
    }
}
