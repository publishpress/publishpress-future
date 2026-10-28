<?php

/**
 * Typed, non-secret settings and post-type defaults using Future's native facade.
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
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

class SettingsService
{
    private McpFacade $wordpress;
    private SettingsFacade $settings;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, SettingsFacade $settings)
    {
        $this->wordpress = $wordpress;
        $this->settings = $settings;
    }

    /** @since 4.12.0 */
    public function canManage(): bool
    {
        return $this->wordpress->can('manage_options');
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    private function schemas(): array
    {
        $boolean = ['type' => 'boolean'];
        $text = ['type' => 'string', 'maxLength' => 10000];
        $emails = ['type' => 'array', 'maxItems' => 100, 'uniqueItems' => true,
            'items' => ['type' => 'string', 'format' => 'email']];
        return [
            'general' => Registry::object(['defaultDateTimeOffset' => $text, 'hideCalendarByDefault' => $boolean,
                'allowUserRoles' => ['type' => 'array', 'maxItems' => 100, 'uniqueItems' => true,
                    'items' => ['type' => 'string', 'enum' => array_keys($this->wordpress->roles())]]]),
            'notifications' => Registry::object(['enableEmailNotification' => $boolean,
                'enableEmailNotificationToAdmins' => $boolean, 'emailNotificationAddressesList' => $emails, 'pastDueActionsNotification' => $boolean, 'pastDueActionsNotificationList' => $emails]),
            'display' => Registry::object(['defaultDateFormat' => $text, 'defaultTimeFormat' => $text,
                'showInPostFooter' => $boolean, 'footerContents' => $text, 'footerStyle' => $text,
                'shortcodeWrapper' => ['type' => 'string', 'enum' => ['', 'div', 'span', 'p']], 'shortcodeWrapperClass' => $text]),
            'admin' => Registry::object(['metaboxTitle' => $text, 'metaboxCheckboxLabel' => $text,
                'columnStyle' => ['type' => 'string', 'enum' => ['verbose', 'simple']],
                'timeFormatForDatePicker' => ['type' => 'string', 'enum' => ['inherited', '12h', '24h']]]),
            'advanced' => Registry::object(['stepScheduleCompressedArgs' => $boolean,
                'scheduledWorkflowStepsCleanup' => $boolean, 'scheduledWorkflowStepsCleanupRetention' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 3650],
                'experimentalFeatures' => $boolean, 'debugEnabled' => $boolean, 'preserveDataDeactivating' => $boolean]),
        ];
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $group = ['type' => 'string', 'enum' => ['general', 'notifications', 'display', 'admin', 'advanced'], 'default' => 'general'];
        $output = ['group' => ['type' => 'string'], 'settings' => ['type' => 'object'], 'schema' => ['type' => 'object'],
            'hash' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'get-plugin-settings',
            __('Read Future settings', 'post-expirator'),
            __('Read non-secret General, Notifications, Display, Admin or Advanced settings and their exact field schema. Requires manage_options. Post-type defaults, Pro metadata mappings and workflow graphs have separate actions.', 'post-expirator'),
            Registry::object(['group' => $group]),
            $output,
            [$this, 'get'],
            [$this, 'canManage']
        );
        $variants = [];
        foreach ($this->schemas() as $name => $schema) {
            $schema['minProperties'] = 1;
            $variants[] = Registry::object(['group' => ['type' => 'string', 'enum' => [$name]],
                'expected_hash' => Registry::revision(), 'settings' => $schema,
                'dry_run' => Registry::preview()], ['group', 'settings', 'expected_hash']);
        }
        $registry->add(
            'update-plugin-settings',
            __('Update Future settings', 'post-expirator'),
            __('Patch only supplied fields in one discovered settings group using its latest hash. Preserves omitted configuration. Native settings services and role/code locks apply. dry_run validates without saving. Never accepts license keys, arbitrary options or callbacks.', 'post-expirator'),
            ['type' => 'object', 'oneOf' => $variants],
            $output,
            [$this, 'update'],
            [$this, 'canManage'],
            true
        );
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    private function values(string $group): array
    {
        $methods = ['general' => 'getGeneralSettings', 'notifications' => 'getNotificationsSettings',
            'display' => 'getDisplaySettings', 'admin' => 'getAdminSettings', 'advanced' => 'getAdvancedSettings'];
        $raw = $this->settings->{$methods[$group]}();
        if ($group === 'advanced') {
            $raw['debugEnabled'] = $this->settings->getDebugIsEnabled();
        }
        if ($group === 'notifications') {
            $raw['pastDueActionsNotification'] = $this->settings->getPastDueActionsNotificationStatus();
            $raw['pastDueActionsNotificationList'] = $this->settings->getPastDueActionsNotificationAddressesList();
        }
        $values = array_intersect_key($raw, $this->schemas()[$group]['properties']);
        foreach ($this->schemas()[$group]['properties'] as $name => $schema) {
            $value = $values[$name] ?? null;
            if ($schema['type'] === 'boolean') {
                $value = (bool) $value;
            } elseif ($schema['type'] === 'integer') {
                $value = (int) $value;
            } elseif ($schema['type'] === 'array') {
                $value = array_values(array_filter((array) $value, static fn($item) => $item !== ''));
            } else {
                $value = (string) $value;
            }
            if ($name === 'allowUserRoles' && is_array($value)) {
                sort($value);
            }
            $values[$name] = $value;
        }
        return $values;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    public function get(array $input = []): array
    {
        $group = $input['group'] ?? 'general';
        $values = $this->values($group);
        return ['group' => $group, 'settings' => (object) $values, 'schema' => $this->schemas()[$group],
            'hash' => $this->wordpress->hash([$group, $values]), 'dry_run' => false];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function update(array $input)
    {
        $before = $this->get($input);
        if ($input['expected_hash'] !== $before['hash']) {
            return Registry::error(__('These settings changed. Read them again before saving.', 'post-expirator'), 409);
        }
        $valid = $this->wordpress->validate($input['settings'], $before['schema'], 'settings');
        if ($valid instanceof \WP_Error || !Registry::strict($input['settings'], $before['schema'])) {
            return $valid instanceof \WP_Error ? $valid : Registry::error(__('Use exact setting types returned by discovery.', 'post-expirator'));
        }
        $patch = $input['settings'];
        foreach ($patch as $key => $value) {
            $type = $before['schema']['properties'][$key]['type'];
            if (($type === 'boolean' && !is_bool($value)) || ($type === 'integer' && !is_int($value)) || ($type === 'string' && !is_string($value))) {
                return Registry::error(__('Use exact setting types returned by discovery.', 'post-expirator'));
            }
            if (is_string($value)) {
                $patch[$key] = $key === 'footerContents' ? $this->wordpress->html($value) : ($key === 'footerStyle' ? $this->wordpress->css($value) : $this->wordpress->text($value));
            }
        }
        if (!empty($patch['experimentalFeatures']) && !$this->wordpress->experimentalAllowed()) {
            return Registry::error(__('Experimental features are disabled by site configuration.', 'post-expirator'));
        }
        if (isset($patch['debugEnabled']) && !$patch['debugEnabled'] && defined('PUBLISHPRESS_FUTURE_FORCE_DEBUG') && PUBLISHPRESS_FUTURE_FORCE_DEBUG) {
            return Registry::error(__('Debug logging is required by site configuration.', 'post-expirator'));
        }
        $next = array_merge((array) $before['settings'], $patch);
        if ($input['group'] === 'general') {
            $next['allowUserRoles'] = array_values(array_unique(array_merge(['administrator'], $next['allowUserRoles'])));
            sort($next['allowUserRoles']);
            if ($next['defaultDateTimeOffset'] !== '' && strtotime($next['defaultDateTimeOffset']) === false) {
                return Registry::error(__('Use a valid native date offset, for example +1 week.', 'post-expirator'));
            }
        }
        if (!empty($input['dry_run'])) {
            return array_merge($before, ['settings' => (object) $next, 'dry_run' => true]);
        }
        $snapshot = $this->wordpress->snapshotOptions($this->optionKeys($input['group']));
        try {
            $this->persist($input['group'], $next);
            $this->wordpress->reloadRoles();
            $saved = $this->get($input);
            if ((array) $saved['settings'] != $next) {
                throw new \RuntimeException('Verification failed');
            }
        } catch (\Throwable $error) {
            $restored = $this->wordpress->restoreOptions($snapshot);
            $this->settings->purgeCache();
            return Registry::error($restored ? __('Settings save failed; original values were restored.', 'post-expirator')
                : __('Settings save failed and recovery was incomplete. Read settings before retrying.', 'post-expirator'), 500);
        }
        return $saved;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $values
     */
    private function persist(string $group, array $values): void
    {
        $methods = ['general' => 'setGeneralSettings', 'notifications' => 'setNotificationsSettings',
            'display' => 'setDisplaySettings', 'admin' => 'setAdminSettings', 'advanced' => 'setAdvancedSettings'];
        $this->settings->{$methods[$group]}($values);
        if ($group === 'advanced') {
            $this->wordpress->setDebugEnabled($values['debugEnabled']);
        }
        if ($group === 'notifications') {
            $this->settings->setPastDueActionsNotificationStatus($values['pastDueActionsNotification']);
            $this->settings->setPastDueActionsNotificationAddressesList($values['pastDueActionsNotificationList']);
        }
        $this->settings->purgeCache();
    }
    /**
     * Only keys owned by the selected native settings group. @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function optionKeys(string $group): array
    {
        $keys = [
            'general' => ['expirationdateDefaultDate', 'expirationdateDefaultDateCustom', 'expirationdateHideCalendarByDefault', $this->wordpress->rolesOption()],
            'notifications' => ['expirationdateEmailNotification', 'expirationdateEmailNotificationAdmins', 'expirationdateEmailNotificationList', 'pastDueActionsNotification', 'pastDueActionsNotificationList'],
            'display' => ['expirationdateDefaultDateFormat', 'expirationdateDefaultTimeFormat', 'expirationdateDisplayFooter', 'expirationdateFooterContents', 'expirationdateFooterStyle', 'shortcodeWrapper', 'shortcodeWrapperClass'],
            'admin' => [SettingsFacade::OPTION_METABOX_TITLE, SettingsFacade::OPTION_METABOX_CHECKBOX_LABEL, 'expirationdateColumnStyle', 'expirationdateTimeFormatForDatePicker'],
            'advanced' => [SettingsFacade::OPTION_STEP_SCHEDULE_COMPRESSED_ARGS, SettingsFacade::OPTION_SCHEDULED_STEP_CLEANUP_STATUS, SettingsFacade::OPTION_FINISHED_SCHEDULED_STEP_RETENTION, SettingsFacade::OPTION_EXPERIMENTAL_ENABLED, 'expirationdatePreserveData', 'expirationdateDebug'],
        ];
        return $keys[$group];
    }
}
