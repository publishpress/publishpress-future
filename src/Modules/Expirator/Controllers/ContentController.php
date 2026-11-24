<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\WordPress\Facade\DateTimeFacade;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

class ContentController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var DateTimeFacade
     */
    private $dateTimeFacade;

    /**
     * @param HookableInterface $hooksFacade
     * @param SettingsFacade $settingsFacade
     * @param DateTimeFacade $dateTimeFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        SettingsFacade $settingsFacade,
        DateTimeFacade $dateTimeFacade
    ) {
        $this->hooks = $hooksFacade;
        $this->settingsFacade = $settingsFacade;
        $this->dateTimeFacade = $dateTimeFacade;
    }

    public function initialize()
    {
        $this->hooks->addFilter(ExpiratorHooks::FILTER_CONTENT_FOOTER, [$this, 'getFooterText'], 10, 2);
        $this->hooks->addFilter(ExpiratorHooks::FILTER_THE_CONTENT, [$this, 'addFooterToContent'], 0);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function addFooterToContent($content)
    {
        global $post;

        // Check to see if its enabled
        $displayFooter = (bool) $this->settingsFacade->getShowInPostFooter();

        // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
        if (! $displayFooter || empty($post)) {
            return $content;
        }

        $container = Container::getInstance();
        $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $postModel = $factory($post->ID);

        $enabled = $postModel->isExpirationEnabled();

        if (empty($enabled)) {
            return $content;
        }

        $expirationDate = $postModel->getExpirationDateAsUnixTime();
        if (! is_numeric($expirationDate)) {
            return $content;
        }

        $footerText = $this->hooks->applyFilters(ExpiratorHooks::FILTER_CONTENT_FOOTER, '');

        $expirationdateFooterStyle = $this->settingsFacade->getFooterStyle();

        $appendToFooter = '<p style="' . esc_attr($expirationdateFooterStyle) . '">' . esc_html($footerText) . '</p>';

        return $content . $appendToFooter;
    }

    public function getFooterText($content = '', $useDemoText = false)
    {
        $container = Container::getInstance();

        if ($useDemoText) {
            $expirationDate = time() + 60 * 60 * 24 * 7;
        } else {
            global $post;
            $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
            $postModel = $factory($post->ID);

            $expirationDate = $postModel->getExpirationDateAsUnixTime();
        }

        $dateformat = $this->settingsFacade->getDefaultDateFormat();
        $timeformat = $this->settingsFacade->getDefaultTimeFormat();
        $expirationdateFooterContents = $this->settingsFacade->getFooterContents();

        $defaultDateFormat = $this->dateTimeFacade->getDefaultDateFormat();
        $defaultTimeFormat = $this->dateTimeFacade->getDefaultTimeFormat();
        $defaultDateTimeFormat = $this->dateTimeFacade->getDefaultDateTimeFormat();

        $search = [
            // Deprecated placeholders
            'EXPIRATIONFULL',
            'EXPIRATIONDATE',
            'EXPIRATIONTIME',
            // New placeholders
            'ACTIONFULL',
            'ACTIONDATE',
            'ACTIONTIME',
        ];

        $replace = [
            // Deprecated placeholders
            $this->dateTimeFacade->getWpDate(
                "$dateformat $timeformat",
                $expirationDate,
                $defaultDateTimeFormat
            ),
            $this->dateTimeFacade->getWpDate(
                $dateformat,
                $expirationDate,
                $defaultDateFormat
            ),
            $this->dateTimeFacade->getWpDate(
                $timeformat,
                $expirationDate,
                $defaultTimeFormat
            ),
            // New placeholders
            $this->dateTimeFacade->getWpDate(
                "$dateformat $timeformat",
                $expirationDate,
                $defaultDateTimeFormat
            ),
            $this->dateTimeFacade->getWpDate(
                $dateformat,
                $expirationDate,
                $defaultDateFormat
            ),
            $this->dateTimeFacade->getWpDate(
                $timeformat,
                $expirationDate,
                $defaultTimeFormat
            ),
        ];

        $content .= str_replace(
            $search,
            $replace,
            $expirationdateFooterContents
        );

        return $content;
    }
}
