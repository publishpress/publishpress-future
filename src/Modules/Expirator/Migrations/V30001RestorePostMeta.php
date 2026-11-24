<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Migrations;

use Action_Scheduler\Migration\Scheduler;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter;
use PublishPress\Future\Modules\Expirator\ExpirationScheduler;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use PublishPress\Future\Modules\Expirator\PostMetaAbstract;
use PublishPress\Future\Modules\Expirator\Schemas\ActionArgsSchema;

use function tad\WPBrowser\vendorDir;

\defined('ABSPATH') || exit;

class V30001RestorePostMeta implements MigrationInterface
{
    public const HOOK = ExpiratorHooks::ACTION_MIGRATE_RESTORE_POST_META;

    /**
     * @var \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface
     */
    private $cronAdapter;

    private $hooksFacade;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade
     */
    private $optionsFacade;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    private $actionStore;

    /**
     * @param \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface $cronAdapter
     * @param \PublishPress\Future\Core\HookableInterface $hooksFacade
     * @param \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade $optionsFacade
     * @param \Closure $expirablePostModelFactory
     * @param $actionStore
     */
    public function __construct(
        CronInterface $cronAdapter,
        HookableInterface $hooksFacade,
        OptionsFacade $optionsFacade,
        \Closure $expirablePostModelFactory,
        $actionStore
    ) {
        $this->cronAdapter = $cronAdapter;
        $this->hooksFacade = $hooksFacade;
        $this->optionsFacade = $optionsFacade;
        $this->actionStore = $actionStore;

        $this->hooksFacade->addAction(self::HOOK, [$this, 'migrate']);
        $this->hooksFacade->addAction(
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
    private function getScheduledActions()
    {
        $events = [];

        $query = [
            'per_page' => -1,
            'offset' => 0,
            'status' => '',
            'orderby' => 'ID',
            'order' => 'ASC',
            'search' => '',
            'group' => CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP,
        ];

        $actions = $this->actionStore->query_actions($query);
        $modelFactory = $this->expirablePostModelFactory;

        foreach ($actions as $actionId) {
            try {
                $action = $this->actionStore->fetch_action($actionId);
            } catch (\Exception $e) {
                continue;
            }

            $args = $action->get_args();
            if (!isset($args['workflow']) || $args['workflow'] !== 'expire') {
                continue;
            }

            $postId = $args['postId'];
            $postModel = $modelFactory($postId);

            $expirationData = $postModel->getExpirationDataAsArray();

            if ($expirationData['date'] == 0) {
                continue;
            }

            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TIMESTAMP, $expirationData['date']);
            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_STATUS, 'saved');
            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TYPE, $expirationData['expireType']);
            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TAXONOMY, $expirationData['categoryTaxonomy']);
            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TERMS, $expirationData['category']);
            $postModel->updateMeta(PostMetaAbstract::EXPIRATION_DATE_OPTIONS, $postModel->getExpirationOptions());

            $postModel->updateMeta(
                ExpirablePostModel::FLAG_METADATA_HASH,
                $postModel->calcMetadataHash()
            );
        }

        return $events;
    }

    public function migrate()
    {
        $events = $this->getScheduledActions();
    }

    /**
     * @param string $text
     * @param array $row
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __('Restore post meta data after v3.0.1', 'post-expirator');
        }

        return $text;
    }
}
