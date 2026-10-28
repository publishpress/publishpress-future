<?php

/**
 * Register private actions with explicit schemas independently of Hub.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;

\defined('ABSPATH') || exit;

class Registry
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
     * @param array<array-key, mixed> $properties
     * @param array<array-key, mixed> $required
     * @return array<array-key, mixed>
     */
    public static function object(array $properties = [], array $required = []): array
    {
        $schema = ['type' => 'object', 'additionalProperties' => false];
        if ($properties) {
            $schema['properties'] = $properties;
        }
        if ($required) {
            $schema['required'] = $required;
        }
        return $schema;
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public static function page(): array
    {
        return ['page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10000, 'default' => 1],
            'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20]];
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public static function id(): array
    {
        return ['type' => 'integer', 'minimum' => 1];
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public static function revision(): array
    {
        return ['type' => 'string', 'pattern' => '^[a-f0-9]{64}$'];
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public static function preview(): array
    {
        return ['type' => 'boolean', 'default' => false];
    }

    /** @since 4.12.0 */
    public static function error(string $message, int $status = 400): \WP_Error
    {
        return new \WP_Error('publishpress_future_mcp_invalid_operation', $message, ['status' => $status]);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @param array<array-key, mixed> $output
     */
    public function add(string $name, string $label, string $description, array $input, array $output, callable $callback, callable $permission, bool $write = false, bool $idempotent = true): void
    {
        $this->wordpress->register($name, [
            'label' => $label, 'description' => $description, 'category' => 'publishpress',
            'input_schema' => $input, 'output_schema' => self::object($output, array_keys($output)),
            'execute_callback' => function (array $values = []) use ($callback, $input) {
                foreach ($input['properties'] ?? [] as $key => $field) {
                    if (!array_key_exists($key, $values) && array_key_exists('default', $field)) {
                        $values[$key] = $field['default'];
                    }
                }
                if (!self::strict($values, $input) || strlen($this->wordpress->encode($values)) > 1048576) {
                    return self::error(__('Use the exact input types and bounded values shown in discovery.', 'post-expirator'));
                }
                try {
                    return $callback($values);
                } catch (\Throwable $error) {
                    return self::error(__('The operation did not complete normally. Read the affected data before retrying; some changes may have been applied.', 'post-expirator'), 500);
                }
            }, 'permission_callback' => function ($values = []) use ($permission, $input) {
                if (!is_array($values) || !self::strict($values, $input)) {
                    return false;
                }
                try {
                    return $permission($values);
                } catch (\Throwable $error) {
                    return false;
                }
            },
            'meta' => ['public' => false, 'show_in_rest' => false, 'mcp' => ['public' => false],
                'annotations' => ['readonly' => !$write, 'destructive' => $write, 'idempotent' => $idempotent]],
        ]);
    }
    /**
     * WordPress schema validation permits coercion; tool inputs must use exact JSON types. @since 4.12.0
     *
     * @param mixed $value
     * @param array<array-key, mixed> $schema
     */
    public static function strict($value, array $schema, int $depth = 0): bool
    {
        if ($depth > 40) {
            return false;
        }
        if (isset($schema['anyOf']) || isset($schema['oneOf'])) {
            foreach ($schema['anyOf'] ?? $schema['oneOf'] as $alternative) {
                if (McpFacade::matchesSchema($value, $alternative) && self::strict($value, $alternative, $depth + 1)) {
                    return true;
                }
            }
            return false;
        }
        $types = (array) ($schema['type'] ?? []);
        if ($types) {
            $actual = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_float($value) ? 'number'
                : (is_string($value) ? 'string' : ($value === null ? 'null' : (is_array($value) || is_object($value) ? 'object' : 'invalid')))));
            if (is_array($value) && in_array('array', $types, true) && (!$value || array_keys($value) === range(0, count($value) - 1))) {
                $actual = 'array';
            }
            if (!in_array($actual, $types, true) && !($actual === 'integer' && in_array('number', $types, true))) {
                return false;
            }
        }
        if (is_array($value) || is_object($value)) {
            foreach ((array) $value as $key => $child) {
                $next = $schema['properties'][$key] ?? ($schema['items'] ?? ($schema['additionalProperties'] ?? []));
                if ($next === false || !self::strict($child, is_array($next) ? $next : [], $depth + 1)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Project the current native choices without reusing a stale UI option cache. @since 4.12.0
     *
     * @param array<array-key, mixed> $choices
     * @return array<array-key, mixed>
     */
    public static function options(array $choices): array
    {
        $options = [];
        foreach ($choices as $value => $label) {
            $options[] = ['value' => (string) $value, 'label' => $label];
        }
        return $options;
    }
}
