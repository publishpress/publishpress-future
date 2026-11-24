<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use Closure;
use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\DBTableSchemas\ActionArgsSchema;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\SchedulerInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use Throwable;

\defined('ABSPATH') || exit;

class ExpirationController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @var Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ActionArgsSchema
     */
    private $actionArgsSchema;

    /**
     * @param HookableInterface $hooksFacade
     * @param SchedulerInterface $scheduler
     * @param Closure $expirablePostModelFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        HookableInterface $hooksFacade,
        SchedulerInterface $scheduler,
        Closure $expirablePostModelFactory,
        LoggerInterface $logger,
        ActionArgsSchema $actionArgsSchema
    ) {
        $this->hooks = $hooksFacade;
        $this->scheduler = $scheduler;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->logger = $logger;
        $this->actionArgsSchema = $actionArgsSchema;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
            [$this, 'onActionSchedulePostExpiration'],
            10,
            3
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION,
            [$this, 'onActionUnschedulePostExpiration']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_RUN_WORKFLOW,
            [$this, 'onActionRunPostExpiration']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_LEGACY_RUN_WORKFLOW,
            [$this, 'onActionRunPostExpiration']
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_POST_UPDATED,
            [$this, 'autoEnableOnPostUpdate'],
            10,
            3
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_INSERT_POST,
            [$this, 'autoEnableOnInsertPost'],
            10,
            3
        );
    }

    public function onActionSchedulePostExpiration($postId, $timestamp, $opts)
    {
        $this->scheduler->schedule((int) $postId, (int) $timestamp, $opts);
    }

    public function onActionUnschedulePostExpiration($postId)
    {
        $this->scheduler->unschedule($postId);
    }

    /**
     * @throws \Exception
     */
    public function onActionRunPostExpiration($postId, $force = false)
    {
        $postModelFactory = $this->expirablePostModelFactory;

        $postModel = $postModelFactory($postId);

        if (!($postModel instanceof ExpirablePostModel)) {
            throw new Exception('Invalid post model factory');
        }

        $postModel->expire($force);
    }

    public function autoEnableOnPostUpdate($postId, $postAfter, $postBefore): void
    {
        try {
            // Ignore auto-drafts
            if ($postAfter->post_status === 'auto-draft') {
                return;
            }

            // Transitioning from auto-draft to anything else
            if ($postBefore->post_status !== 'auto-draft') {
                return;
            }

            $this->setupFutureActionIfAutoEnabled($postId);
        } catch (Throwable $th) {
            $this->logger->error('Error setting default meta for post: ' . $th->getMessage());
        }
    }

    public function autoEnableOnInsertPost($postId, $post, $update)
    {
        if ($update) {
            return;
        }

        if ($post->post_status === 'auto-draft') {
            return;
        }

        $this->setupFutureActionIfAutoEnabled($postId);
    }

    private function setupFutureActionIfAutoEnabled(int $postId): void
    {
        // This is needed to avoid errors on fresh install. See issue #1051.
        if (! $this->actionArgsSchema->isTableHealthy()) {
            return;
        }

        $postModelFactory = $this->expirablePostModelFactory;
        $postModel = $postModelFactory($postId);

        if ($postModel->shouldAutoEnable()) {
            $postModel->setupFutureActionWithDefaultData();
        }
    }
}
