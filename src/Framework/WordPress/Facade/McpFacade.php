<?php

/**
 * WordPress boundary for private Future abilities and their bounded data access.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Framework\WordPress\Facade;

\defined('ABSPATH') || exit;

class McpFacade
{
    /**
     * Encode bounded tool input at the WordPress infrastructure boundary. @since 4.12.0
     *
     * @param mixed $value
     */
    public function encode($value): string
    {
        $encoded = wp_json_encode($value);
        if ($encoded === false) {
            throw new \InvalidArgumentException('Tool data could not be encoded.');
        }
        return $encoded;
    }

    /** Respect a site-defined feature flag without assuming a compile-time default. @since 4.12.0 */
    public function experimentalAllowed(): bool
    {
        return defined('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL')
            && (bool) constant('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL');
    }

    /**
     * WordPress schema matching at the infrastructure boundary. @since 4.12.0
     *
     * @param mixed $value
     * @param array<array-key, mixed> $schema
     */
    public static function matchesSchema($value, array $schema): bool
    {
        return !(rest_validate_value_from_schema($value, $schema) instanceof \WP_Error);
    }

    /** @since 4.12.0 */
    public function available(): bool
    {
        return function_exists('wp_register_ability') && function_exists('wp_has_ability_category');
    }

    /** @since 4.12.0 */
    public function category(): void
    {
        if (!wp_has_ability_category('publishpress')) {
            wp_register_ability_category('publishpress', ['label' => __('PublishPress', 'post-expirator'),
                'description' => __('Actions provided by PublishPress plugins.', 'post-expirator')]);
        }
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $arguments
     */
    public function register(string $name, array $arguments): void
    {
        wp_register_ability('publishpress-future/' . $name, $arguments);
    }

    /**
     * @since 4.12.0
     *
     * @param mixed ...$objects
     */
    public function can(string $capability, ...$objects): bool
    {
        return current_user_can($capability, ...$objects);
    }

    /** @since 4.12.0 */
    public function post(int $id): ?\WP_Post
    {
        return $id > 0 ? get_post($id) : null;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $arguments
     */
    public function posts(array $arguments): \WP_Query
    {
        return new \WP_Query($arguments);
    }

    /**
     * @since 4.12.0
     *
     * @return mixed
     */
    public function postType(string $name)
    {
        return get_post_type_object($name);
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function postTypes(): array
    {
        return get_post_types(['show_ui' => true], 'names');
    }

    /**
     * @since 4.12.0
     *
     * @return mixed
     */
    public function taxonomy(string $name)
    {
        return get_taxonomy($name);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $arguments
     * @return mixed
     */
    public function terms(array $arguments)
    {
        return get_terms($arguments);
    }

    /**
     * @since 4.12.0
     *
     * @return mixed
     */
    public function term(int $id, string $taxonomy)
    {
        return get_term($id, $taxonomy);
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function roles(): array
    {
        return wp_roles()->roles;
    }

    /**
     * @since 4.12.0
     *
     * @param mixed $value
     * @param array<array-key, mixed> $schema
     * @return mixed
     */
    public function validate($value, array $schema, string $parameter = 'input')
    {
        return rest_validate_value_from_schema($value, $schema, $parameter);
    }

    /**
     * @since 4.12.0
     *
     * @param mixed $value
     */
    public function hash($value): string
    {
        return hash('sha256', $this->encode($this->canonical($value)));
    }

    /**
     * Preserve ordered lists while normalizing property order. @since 4.12.0
     *
     * @param mixed $value
     * @return mixed
     */
    private function canonical($value)
    {
        if (is_object($value)) {
            $value = (array) $value;
        }
        if (!is_array($value)) {
            return $value;
        }
        if ($value && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value);
        }
        return array_map([$this, 'canonical'], $value);
    }

    /** @since 4.12.0 */
    public function text(string $value): string
    {
        return sanitize_text_field($value);
    }

    /** @since 4.12.0 */
    public function html(string $value): string
    {
        return wp_kses_post($value);
    }

    /**
     * @since 4.12.0
     *
     * @return mixed
     */
    public function meta(int $id, string $key, bool $single = true)
    {
        return get_post_meta($id, $key, $single);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $metadata
     */
    public function restorePost(\WP_Post $post, array $metadata): bool
    {
        $result = wp_update_post(wp_slash((array) $post), true);
        foreach ($metadata as $key => $values) {
            delete_post_meta($post->ID, $key);
            foreach ($values as $value) {
                add_post_meta($post->ID, $key, wp_slash(maybe_unserialize($value)));
            }
        }
        return !is_wp_error($result);
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function allMeta(int $id): array
    {
        return get_post_meta($id);
    }

    /** @since 4.12.0 */
    public function trash(int $id): bool
    {
        return EMPTY_TRASH_DAYS && (bool) wp_trash_post($id);
    }

    /** @since 4.12.0 */
    public function deleteDraft(int $id): bool
    {
        return (bool) wp_delete_post($id, true);
    }

    /** Native scheduler takes a local wall-clock timestamp. @since 4.12.0 */
    public function localTimestamp(int $utc): int
    {
        $local = get_date_from_gmt(gmdate('Y-m-d H:i:s', $utc));
        return (new \DateTimeImmutable($local, new \DateTimeZone('UTC')))->getTimestamp();
    }

    /** @since 4.12.0 */
    public function timezone(): string
    {
        return wp_timezone_string();
    }

    /** @since 4.12.0 */
    public function now(): int
    {
        return time();
    }

    /** @since 4.12.0 */
    public function pro(): bool
    {
        return defined('PUBLISHPRESS_FUTURE_PRO_LOADED') && PUBLISHPRESS_FUTURE_PRO_LOADED
            && class_exists('PublishPress\\FuturePro\\Core\\Utils');
    }
    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function taxonomies(string $postType): array
    {
        return get_object_taxonomies($postType, 'objects');
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function statuses(): array
    {
        return get_post_stati([], 'objects');
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function allowedActionHooks(): array
    {
        return (array) apply_filters('publishpress_future_allowed_action_hooks', []);
    }

    /** @since 4.12.0 */
    public function userId(): int
    {
        return get_current_user_id();
    }

    /** @since 4.12.0 */
    public function css(string $css): string
    {
        return safecss_filter_attr($css);
    }

    /**
     * Bounded option keys are supplied by trusted providers, never tool callers. @since 4.12.0
     *
     * @param array<array-key, mixed> $keys
     * @return array<array-key, mixed>
     */
    public function snapshotOptions(array $keys): array
    {
        $snapshot = [];
        foreach ($keys as $key) {
            $sentinel = new \stdClass();
            $value = get_option($key, $sentinel);
            $snapshot[$key] = ['exists' => $value !== $sentinel, 'value' => $value === $sentinel ? null : $value];
        }
        return $snapshot;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $snapshot
     */
    public function restoreOptions(array $snapshot): bool
    {
        foreach ($snapshot as $key => $entry) {
            if ($entry['exists']) {
                update_option($key, $entry['value']);
            } else {
                delete_option($key);
            }
        }
        wp_roles()->for_site();
        return $this->snapshotOptions(array_keys($snapshot)) === $snapshot;
    }

    /** Re-read persisted roles after native setters. @since 4.12.0 */
    public function reloadRoles(): void
    {
        wp_roles()->for_site();
    }

    /** @since 4.12.0 */
    public function rolesOption(): string
    {
        return wp_roles()->role_key;
    }

    /** Fixed native debug switch; never an arbitrary option setter. @since 4.12.0 */
    public function setDebugEnabled(bool $enabled): void
    {
        update_option('expirationdateDebug', $enabled ? '1' : '0');
    }

    /** Native local timestamps cannot distinguish both occurrences of a DST fold. @since 4.12.0 */
    public function canRepresentSchedule(int $utc): bool
    {
        $wallClock = $this->localTimestamp($utc);
        return (int) get_gmt_from_date(gmdate('Y-m-d H:i:s', $wallClock), 'U') === $utc;
    }
}
