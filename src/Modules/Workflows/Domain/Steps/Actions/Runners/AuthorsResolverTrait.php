<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use MultipleAuthors\Classes\Objects\Author;

/**
 * Shared helpers for PublishPress Authors action runners: resolves a
 * comma-separated list of author references (term ID, slug, or email) into
 * Author objects, tolerating expression/scalar field storage.
 */
trait AuthorsResolverTrait
{
    /**
     * Resolve an `expression` field into its trimmed string value.
     */
    private function resolveExpressionField(array $nodeSettings, string $fieldName): string
    {
        $value = $nodeSettings[$fieldName] ?? '';

        if (is_array($value)) {
            $value = $value['expression'] ?? '';
        }

        return trim($this->executionContext->resolveExpressionsInText((string)$value));
    }

    /**
     * Resolve a comma-separated list of author references into Author objects.
     *
     * @return Author[]
     */
    private function resolveAuthors(string $referenceList): array
    {
        if (! class_exists(Author::class)) {
            return [];
        }

        $authors = [];

        foreach (array_filter(array_map('trim', explode(',', $referenceList))) as $reference) {
            $author = $this->resolveSingleAuthor($reference);

            if ($author && ! empty($author->term_id)) {
                $authors[(int) $author->term_id] = $author;
            }
        }

        return array_values($authors);
    }

    private function resolveSingleAuthor(string $reference)
    {
        if (ctype_digit($reference)) {
            return Author::get_by_term_id((int) $reference);
        }

        if (strpos($reference, '@') !== false) {
            return Author::get_by_email($reference);
        }

        return Author::get_by_term_slug($reference);
    }
}
