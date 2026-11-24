<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;
use PublishPress\Future\Modules\Expirator\PostMetaAbstract;
use PublishPress\Future\Modules\Expirator\Schemas\ActionArgsSchema;

\defined('ABSPATH') || exit;

class V30000WPCronToActionsScheduler implements MigrationInterface
{
    public const HOOK = ExpiratorHooks::ACTION_MIGRATE_WPCRON_EXPIRATIONS;

    /**
     * @var \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface
     */
    private $cronAdapter;

    private $hooks;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @param \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface $cronAdapter
     * @param \PublishPress\Future\Core\HookableInterface $hooks
     */
    public function __construct(
        CronInterface $cronAdapter,
        HookableInterface $hooks,
        \Closure $expirablePostModelFactory
    ) {
        $this->cronAdapter = $cronAdapter;
        $this->hooks = $hooks;

        $this->hooks->addAction(self::HOOK, [$this, 'migrate']);
        $this->hooks->addAction(
            ExpiratorHooks::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'formatLogActionColumn'],
            10,
            2
        );
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    /**
     * @return array
     */
    private function getLegacyScheduledActions()
    {
        $cron = _get_cron_array();
        $events = [];

        $pluginValidHooks = [
            ExpiratorHooks::ACTION_LEGACY_EXPIRE_POST2,
            ExpiratorHooks::ACTION_LEGACY_EXPIRE_POST1,
        ];

        foreach ($cron as $time => $value) {
            foreach ($value as $eventKey => $eventValue) {
                if (in_array($eventKey, $pluginValidHooks)) {
                    foreach ($eventValue as $eventGUID => $eventData) {
                        $events[] = [
                            'guid' => $eventGUID,
                            'hook' => $eventKey,
                            'time' => $time,
                            'args' => array_values($eventData['args']),
                        ];
                    }
                }
            }
        }

        return $events;
    }

    public function migrate()
    {
        $events = $this->getLegacyScheduledActions();

        foreach ($events as $eventData) {
            $postId = $eventData['args'][0];

            $factory = $this->expirablePostModelFactory;
            $postModel = $factory($postId);

            $expireType = $postModel->getMeta(PostMetaAbstract::EXPIRATION_TYPE, true);
            $expirationTaxonomy = $postModel->getMeta(PostMetaAbstract::EXPIRATION_TAXONOMY, true);
            $expirationCategories = (array) $postModel->getMeta(PostMetaAbstract::EXPIRATION_TERMS, true);

            $args = [
                'expireType' => $expireType,
                'category' => $expirationCategories,
                'categoryTaxonomy' => $expirationTaxonomy,
            ];

            $this->hooks->doAction(ExpiratorHooks::ACTION_SCHEDULE_POST_EXPIRATION, $postId, $eventData['time'], $args);

            wp_unschedule_event($eventData['time'], $eventData['hook'], $eventData['args']);
        }
    }

    /**
     * @param string $text
     * @param array $row
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __('Migrate legacy scheduled actions after v3.0.0', 'post-expirator');
        }
        return $text;
    }
}
