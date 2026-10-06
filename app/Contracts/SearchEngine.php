<?php

namespace App\Contracts;

interface SearchEngine
{
    /**
     * Search across models or a specific type.
     *
     * @param  string|null  $type  (null for unified, or 'courses', 'lessons', 'articles', 'fatawa', 'hadiths', 'ayahs', 'teachers', 'publications')
     */
    public function search(string $query, ?string $type = null, int $limit = 20, array $options = []): array;

    /**
     * Suggest search terms or autocomplete preview results.
     */
    public function suggest(string $query, int $limit = 8): array;
}
