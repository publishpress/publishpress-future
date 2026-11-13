<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

/**
 * Shared helper for PublishPress Cart action runners: reads an `expression`
 * field and resolves {{...}} variables through the execution context.
 * Requires the using class to expose an $executionContext property.
 */
trait NcsCartResolverTrait
{
    private function resolveExpressionField(array $nodeSettings, string $fieldName): string
    {
        $value = $nodeSettings[$fieldName] ?? '';

        if (is_array($value)) {
            $value = $value['expression'] ?? '';
        }

        return trim($this->executionContext->resolveExpressionsInText((string)$value));
    }
}
