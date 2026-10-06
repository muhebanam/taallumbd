<?php

namespace App\Traits;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /**
     * Polymorphic relation to content translations.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    /**
     * Retrieve translated value for a field, falling back gracefully.
     */
    public function getTranslation(string $field, ?string $locale = null, bool $fallback = true): ?string
    {
        $targetLocale = $locale ?: app()->getLocale();

        // 1. If base locale matches default (bn) and requested, return original attribute directly
        if ($targetLocale === 'bn' && ! empty($this->attributes[$field])) {
            return (string) $this->attributes[$field];
        }

        // 2. Check loaded translations relation if already eagerly loaded
        if ($this->relationLoaded('translations')) {
            $matched = $this->translations
                ->first(fn ($t) => $t->locale === $targetLocale && $t->field === $field);

            if ($matched && ! empty($matched->value)) {
                return $matched->value;
            }
        } else {
            // Lazy load single translation record
            $translation = $this->translations()
                ->where('locale', $targetLocale)
                ->where('field', $field)
                ->value('value');

            if (! empty($translation)) {
                return $translation;
            }
        }

        // 3. Fallback logic: check default locale translation, then base attribute
        if ($fallback) {
            if ($targetLocale !== 'bn') {
                $bnFallback = $this->translations()
                    ->where('locale', 'bn')
                    ->where('field', $field)
                    ->value('value');

                if (! empty($bnFallback)) {
                    return $bnFallback;
                }
            }

            return isset($this->attributes[$field]) ? (string) $this->attributes[$field] : null;
        }

        return null;
    }

    /**
     * Set or update translation for a field and locale.
     */
    public function setTranslation(string $field, string $locale, ?string $value): self
    {
        if ($value === null || $value === '') {
            $this->translations()
                ->where('locale', $locale)
                ->where('field', $field)
                ->delete();

            return $this;
        }

        $this->translations()->updateOrCreate(
            [
                'locale' => $locale,
                'field' => $field,
            ],
            [
                'value' => $value,
            ]
        );

        return $this;
    }

    /**
     * Helper to get localized attribute value.
     */
    public function getTranslated(string $field, ?string $locale = null): ?string
    {
        return $this->getTranslation($field, $locale);
    }
}
