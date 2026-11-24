<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Core\HooksAbstract;
use PublishPress\Future\Framework\System\DateTimeHandlerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooksAbstract;
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

class PostTypeDefaultDataModel
{
    public const EXPECTED_DATE_PARTS = 5;

    public const DEFAULT_ACTION = ExpirationActionsAbstract::CHANGE_POST_STATUS;

    public const DEFAULT_NEW_STATUS = 'draft';

    /**
     * @var \PublishPress\Future\Modules\Settings\SettingsFacade
     */
    private $settings;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade
     */
    private $options;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var string
     */
    private $postType = '';

    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var DateTimeHandlerInterface
     */
    private $dateTimeHandler;

    /**
     * @param \PublishPress\Future\Modules\Settings\SettingsFacade $settings
     * @param \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade $options,
     */
    public function __construct(
        SettingsFacade $settings,
        OptionsFacade $options,
        string $postType,
        HooksFacade $hooks,
        DateTimeHandlerInterface $dateTimeHandler
    ) {
        $this->settings = $settings;
        $this->options = $options;
        $this->postType = $postType;
        $this->hooks = $hooks;
        $this->dateTimeHandler = $dateTimeHandler;

        $this->hooks->addAction(HooksAbstract::ACTION_PURGE_PLUGIN_CACHE, [$this, 'purgeCache']);
    }

    public function purgeCache()
    {
        $this->cache = [];
    }

    private function getDateTimeOffset(string $postType)
    {
        $customDateTimeOffset = $this->settings->getGeneralDateTimeOffset();

        $postTypeSettings = $this->settings->getPostTypeDefaults($postType);
        if (
            isset($postTypeSettings['default-expire-type'])
            && 'custom' === $postTypeSettings['default-expire-type']
            && ! empty($postTypeSettings['default-custom-date'])
        ) {
            $customDateTimeOffset = $postTypeSettings['default-custom-date'];
            $customDateTimeOffset = html_entity_decode($customDateTimeOffset, ENT_QUOTES);
            $customDateTimeOffset = preg_replace('/["\'`]/', '', $customDateTimeOffset);
            $customDateTimeOffset = trim($customDateTimeOffset);

            if (empty($customDateTimeOffset)) {
                $customDateTimeOffset = SettingsFacade::DEFAULT_CUSTOM_DATE_OFFSET;
            }
        }

        return $customDateTimeOffset;
    }

    private function extractDateParts($calculatedDate)
    {
        $gmDate = gmdate('Y-m-d H:i:s', $calculatedDate);

        $date = get_date_from_gmt($gmDate, 'Y-m-d-H-i');
        $date = explode('-', $date);

        if (count($date) < self::EXPECTED_DATE_PARTS) {
            throw new \Exception('Unexpected date format: ' . esc_html($gmDate));
        }

        return [
            'year' => $date[0],
            'month' => $date[1],
            'day' => $date[2],
            'hour' => $date[3],
            'minute' => $date[4],
            'ts' => $calculatedDate,
            'iso' => $gmDate,
        ];
    }

    /**
     * @throws \Exception
     */
    public function getActionDateParts(?int $postId = null): array
    {
        $dateTimeOffset = $this->getDateTimeOffset($this->postType);

        $baseDate = $this->hooks->applyFilters(
            ExpiratorHooksAbstract::FILTER_ACTION_BASE_DATE_STRING,
            $this->dateTimeHandler->getCurrentTime(true),
            $this->postType,
            $postId
        );

        if ($baseDate === '0000-00-00 00:00:00') {
            $baseDate = $this->dateTimeHandler->getCurrentTime(true);
        }

        if (! is_numeric($baseDate)) {
            $baseDate = strtotime($baseDate);
        }

        $calculatedDate = $this->dateTimeHandler->getCalculatedTimeWithOffset(
            (int) $baseDate,
            $dateTimeOffset
        );

        if (empty($calculatedDate)) {
            /* translators: 1: date/time offset, 2: post type. */
            $errorMessage = esc_html__('Invalid date/time offset "%1$s" for post type "%2$s". Please ensure you use only English terms for the date/time offset, such as "3 months" or "1 week".', 'post-expirator');

            throw new \Exception(
                sprintf(
                    $errorMessage, // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                    esc_html($dateTimeOffset),
                    esc_html($this->postType)
                )
            );

            $calculatedDate = $this->dateTimeHandler->getCurrentTime(true);
        }

        return $this->extractDateParts($calculatedDate);
    }

    private function getCache($key)
    {
        if (empty($this->cache)) {
            $this->cache = [];
        }

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = null;
        }

        return $this->cache[$key];
    }

    private function setCache($key, $value)
    {
        if (empty($this->cache)) {
            $this->cache = [];
        }

        $this->cache[$key] = $value;
    }

    private function getDefaults()
    {
        $defaults = $this->getCache('defaults');
        if (empty($defaults)) {
            $defaults = $this->settings->getPostTypeDefaults($this->postType);

            $this->setCache('defaults', $defaults);
        }

        return $defaults;
    }

    private function getSetting($setting)
    {
        $cache = $this->getCache($setting);
        if (empty($cache)) {
            $defaults = $this->getDefaults();
            $cache = $defaults[$setting];

            $this->setCache($setting, $cache);
        }

        return $cache;
    }

    public function isAutoEnabled(): bool
    {
        return (bool) $this->getSetting('autoEnable');
    }

    public function getAction(): string
    {
        $action = $this->getSetting('expireType');

        return empty($action) ? self::DEFAULT_ACTION : $action;
    }

    public function getNewStatus(): string
    {
        $newStatus = $this->getSetting('newStatus');

        return empty($newStatus) ? self::DEFAULT_NEW_STATUS : $newStatus;
    }

    public function getTaxonomy(): string
    {
        return (string) $this->getSetting('taxonomy');
    }

    public function getTerms(): array
    {
        $terms = $this->getSetting('terms');

        if (is_string($terms)) {
            $terms = explode(',', $terms);
        }

        if (! is_array($terms)) {
            $terms = [];
        }

        $terms = array_map('intval', $terms);

        return $terms;
    }

    public function getTermsAsString(): string
    {
        return implode(',', $this->getTerms());
    }
}
