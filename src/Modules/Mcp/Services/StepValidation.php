<?php

/**
 * Validate native editor setting rules without executing expressions or workflows.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Mcp\Registry;

\defined('ABSPATH') || exit;

class StepValidation
{
    private McpFacade $wordpress;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress)
    {
        $this->wordpress = $wordpress;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $settings
     * @param array<array-key, mixed> $rules
     * @param array<array-key, mixed> $slugs
     * @return mixed
     */
    public function validate(array $settings, array $rules, array $slugs)
    {
        foreach ($rules as $rule) {
            if (!$this->applies($settings, $rule)) {
                continue;
            }
            $value = $this->value($settings, $rule['field'] ?? '');
            if ($rule['rule'] === 'required' && ($value === null || $value === '' || $value === [])) {
                return Registry::error(__('Fill the required native step settings before enabling this workflow.', 'post-expirator'));
            }
            if (in_array($rule['rule'], ['validExpression', 'validVariable', 'hasVariableSyntax'], true) && is_string($value)) {
                if (substr_count($value, '{{') !== substr_count($value, '}}')) {
                    return Registry::error(__('Close every variable placeholder in step expressions.', 'post-expirator'));
                }
                if ($rule['rule'] === 'hasVariableSyntax' && !preg_match('/^\{\{[^{}]+\}\}$/D', trim($value))) {
                    return Registry::error(__('Use the native variable placeholder syntax for this field.', 'post-expirator'));
                }
                preg_match_all('/\{\{\s*([a-zA-Z][a-zA-Z0-9_]*)\./', $value, $matches);
                foreach ($matches[1] as $slug) {
                    if (!in_array($slug, ['global', 'input'], true) && !isset($slugs[$slug])) {
                        return Registry::error(__('This expression references a workflow step that does not exist.', 'post-expirator'));
                    }
                }
            }
            if ($rule['rule'] === 'dataType' && ($rule['type'] ?? '') === 'emailList' && is_string($value) && $value !== '') {
                foreach (explode(',', $value) as $email) {
                    $email = trim($email);
                    if (preg_match('/^\{\{[^{}]+\}\}$/D', $email)) {
                        continue;
                    }
                    if ($this->wordpress->validate($email, ['type' => 'string', 'format' => 'email']) instanceof \WP_Error) {
                        return Registry::error(__('Recipients need valid email addresses or native variables.', 'post-expirator'));
                    }
                }
            }
            if (($rule['rule'] === 'validOptions' || ($rule['rule'] === 'dataType' && ($rule['type'] ?? '') === 'nameValuePairList')) && $value !== null) {
                if (!is_array($value) || ($rule['rule'] === 'validOptions' && !$value)) {
                    return Registry::error(__('Provide the native list of named options or arguments.', 'post-expirator'));
                }
                $names = [];
                foreach ($value as $item) {
                    $name = is_array($item) ? ($item['name'] ?? '') : '';
                    if (
                        !is_string($name) || trim($name) === '' || isset($names[$name])
                        || ($rule['rule'] === 'validOptions' && (!is_string($item['label'] ?? null) || trim($item['label']) === ''))
                    ) {
                        return Registry::error(__('Native options and arguments need unique names and required labels.', 'post-expirator'));
                    }
                    $names[$name] = true;
                }
            }
        }
        return true;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $settings
     * @param array<array-key, mixed> $rule
     */
    private function applies(array $settings, array $rule): bool
    {
        $conditions = $rule['conditions'] ?? (isset($rule['condition']) ? [$rule['condition']] : []);
        foreach ($conditions as $condition) {
            $value = $this->value($settings, $condition['field']);
            if (
                $value === null || (($condition['operator'] ?? '') === 'in'
                ? !in_array($value, (array) $condition['value'], true) : $value != $condition['value'])
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $settings
     * @return mixed
     */
    private function value(array $settings, string $path)
    {
        $value = $settings;
        foreach (explode('.', $path) as $part) {
            $value = is_array($value) ? ($value[$part] ?? null) : null;
        }
        return $value;
    }
}
