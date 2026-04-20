<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Settings\HooksAbstract;
use PublishPress\Future\Framework\WordPress\Facade\DateTimeFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;

defined('ABSPATH') or die('Direct access not allowed.');

class ShortcodeController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var DateTimeFacade
     */
    private $dateTimeFacade;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct(
        HookableInterface $hooks,
        DateTimeFacade $dateTimeFacade,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooks;
        $this->dateTimeFacade = $dateTimeFacade;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        add_shortcode('futureaction', [$this, 'renderShortcode']);

        /**
         * @deprecated 3.0.0 Use "futureaction" short instead
         */
        add_shortcode('postexpirator', [$this, 'renderShortcode']);
    }

    private function getCurrentPostId(array $attrs = []): int
    {
        $attrs = shortcode_atts(
            [
                'post_id' => null,
            ],
            $attrs
        );

        if (isset($attrs['post_id']) && !empty($attrs['post_id'])) {
            return (int) $attrs['post_id'];
        }

        global $post;

        if (function_exists('get_the_ID')) {
            return (int) get_the_ID();
        }

        return is_object($post) ? (int) $post->ID : 0;
    }

    /**
     * Register the shortcode.
     *
     * @internal
     *
     * @access private
     */
    public function renderShortcode($attrs = [])
    {
        $postId = $this->getCurrentPostId((array)$attrs);

        if (!$postId) {
            $debugIsEnabled = (bool)$this->hooks->applyFilters(HooksAbstract::FILTER_DEBUG_ENABLED, false);

            if ($debugIsEnabled) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                trigger_error(
                    esc_html__(
                        'The shortcode [futureaction] must be used inside the loop or with the post_id attribute.',
                        'post-expirator'
                    ),
                    E_USER_WARNING
                );
            }

            return '';
        }

        $container = Container::getInstance();
        $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $postModel = $factory($postId);

        $enabled = $postModel->isExpirationEnabled();
        $expirationDateTs = $postModel->getExpirationDateAsUnixTime();
        if (!$enabled || empty($expirationDateTs)) {
            return false;
        }

        $attrs = shortcode_atts(
            array(
                'dateformat' => $this->settingsFacade->getDefaultDateFormat(),
                'timeformat' => $this->settingsFacade->getDefaultTimeFormat(),
                'type' => 'full',
                'tz' => date('T'), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'wrapper' => '',
                'class' => '',
            ),
            $attrs
        );

        if (!isset($attrs['dateformat']) || empty($attrs['dateformat'])) {
            global $expirationdateDefaultDateFormat;
            $attrs['dateformat'] = $expirationdateDefaultDateFormat;
        }

        if (!isset($attrs['timeformat']) || empty($attrs['timeformat'])) {
            global $expirationdateDefaultTimeFormat;
            $attrs['timeformat'] = $expirationdateDefaultTimeFormat;
        }

        if (!isset($attrs['type']) || empty($attrs['type'])) {
            $attrs['type'] = 'full';
        }

        if (!isset($attrs['format']) || empty($attrs['format'])) {
            $attrs['format'] = $attrs['dateformat'] . ' ' . $attrs['timeformat'];
        }

        if ($attrs['type'] === 'full') {
            $attrs['format'] = $attrs['dateformat'] . ' ' . $attrs['timeformat'];
        } elseif ($attrs['type'] === 'date') {
            $attrs['format'] = $attrs['dateformat'];
        } elseif ($attrs['type'] === 'time') {
            $attrs['format'] = $attrs['timeformat'];
        }

        if (!isset($attrs['wrapper']) || empty($attrs['wrapper'])) {
            $attrs['wrapper'] = $this->settingsFacade->getShortcodeWrapper();
        }

        if (!isset($attrs['class']) || empty($attrs['class'])) {
            $attrs['class'] = $this->settingsFacade->getShortcodeWrapperClass();
        }

        $defaultDateTimeFormat = $this->dateTimeFacade->getDefaultDateTimeFormat();

        $output = $this->dateTimeFacade->getWpDate(
            trim($attrs['format']),
            $expirationDateTs,
            $defaultDateTimeFormat
        );

        $wrapper = ($attrs['wrapper'] === tag_escape($attrs['wrapper']))
            ? strtolower(trim($attrs['wrapper']))
            : 'div';

        if (!empty($attrs['wrapper'])) {
            $output = sprintf(
                '<%1$s class="%2$s">%3$s</%1$s>',
                esc_html($wrapper),
                esc_attr($attrs['class']),
                $output
            );
        }

        return $output;
    }
}
