<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\DateTimeFacade;
use PublishPress\Future\Framework\WordPress\Facade\ErrorFacade;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\SchedulerInterface;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel;

use function tad\WPBrowser\vendorDir;

\defined('ABSPATH') || exit;

class ExpirationScheduler implements SchedulerInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var CronInterface
     */
    private $cron;

    /**
     * @var ErrorFacade
     */
    private $error;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTimeFacade
     */
    private $datetime;

    /**
     * @var \Closure
     */
    private $postModelFactory;

    /**
     * @var \Closure
     */
    private $actionArgsModelFactory;

    /**
     * @var ExpirationActionsModel
     */
    private $expirationActionsModel;

    /**
     * @var \Closure
     */
    private $currentUserModelFactory;

    /**
     * @param HookableInterface $hooksFacade
     * @param CronInterface $cron
     * @param ErrorFacade $errorFacade
     * @param LoggerInterface $logger
     * @param DateTimeFacade $datetime
     * @param \Closure $postModelFactory
     * @param \Closure $actionArgsModelFactory
     * @param ExpirationActionsModel $expirationActionsModel
     * @param \Closure $currentUserModelFactory
     */
    public function __construct(
        $hooksFacade,
        $cron,
        $errorFacade,
        $logger,
        $datetime,
        $postModelFactory,
        $actionArgsModelFactory,
        $expirationActionsModel,
        $currentUserModelFactory
    ) {
        $this->hooks = $hooksFacade;
        $this->cron = $cron;
        $this->error = $errorFacade;
        $this->logger = $logger;
        $this->datetime = $datetime;
        $this->postModelFactory = $postModelFactory;
        $this->actionArgsModelFactory = $actionArgsModelFactory;
        $this->expirationActionsModel = $expirationActionsModel;
        $this->currentUserModelFactory = $currentUserModelFactory;
    }

    private function convertLocalTimeToUtc($timestamp): int
    {
        // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        return (int) get_gmt_from_date(date('Y-m-d H:i:s', $timestamp), 'U');
    }

    /**
     * @inheritDoc
     */
    public function schedule($postId, $timestamp, $opts)
    {
        $factory = $this->actionArgsModelFactory;

        $postModelFactory = $this->postModelFactory;
        $postModel = $postModelFactory($postId);

        $opts['date'] = $timestamp;
        $opts['category'] ??= [];
        $opts['categoryTaxonomy'] ??= '';
        $opts['actionLabel'] = $this->expirationActionsModel->getLabelForAction($opts['expireType'], $postModel->getPostType());
        $opts['postTitle'] = $postModel->getTitle();
        $opts['postType'] = $postModel->getPostType();
        $opts['postLink'] = $postModel->getPermalink();
        $opts['postTypeLabel'] = $postModel->getPostTypeSingularLabel();
        $opts['extraData'] ??= $postModel->getExtraData();

        if (isset($opts['extraData']['extraData'])) {
            $opts['extraData'] = $opts['extraData']['extraData'];
        }

        // Listeners may rewrite expireType (legacy custom statuses). Authorize the rewritten action.
        $opts = $this->hooks->applyFilters(HooksAbstract::FILTER_PREPARE_POST_EXPIRATION_OPTS, $opts, $postId);

        if (! $this->currentRequestSkipsScheduleAuthorization() && ! $this->userCanScheduleOpts((int) $postId, $opts)) {
            return;
        }

        $timestamp = $this->convertLocalTimeToUtc($opts['date']);

        $this->hooks->doAction(HooksAbstract::ACTION_LEGACY_SCHEDULE, $postId, $timestamp, $opts);

        $this->unscheduleIfScheduled($postId, $timestamp);

        $actionId = $this->cron->scheduleSingleAction(
            $timestamp,
            HooksAbstract::ACTION_RUN_WORKFLOW,
            [
                'postId' => $postId,
                'workflow' => 'expire',
            ]
        );

        if (! $actionId) {
            $this->logger->debug(
                sprintf(
                    '%d  -> TRIED TO SCHEDULE ACTION using %s at %s (%s) with options %s',
                    $postId,
                    $this->cron->getIdentifier(),
                    $this->datetime->getWpDate('r', $timestamp),
                    $timestamp,
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
                    print_r($opts, true)
                )
            );

            return;
        }

        unset($opts['enabled']);
        unset($opts['id']);

        /**
         * @var ActionArgsModelInterface $actionArgsModel
         */
        $actionArgsModel = $factory();
        $actionArgsModel->setCronActionId($actionId);
        $actionArgsModel->setPostId($postId);
        if (! is_numeric($timestamp)) {
            $timestamp = $actionArgsModel->convertISO8601DateToUnixTime($timestamp);
        }
        $actionArgsModel->setScheduledDateFromUnixTime($timestamp);
        $actionArgsModel->setArgs($opts);
        $id = $actionArgsModel->insert();

        $this->logger->debug(
            sprintf(
                '%d  -> ACTION SCHEDULED using %s at %s (%s) with options %s, no errors found',
                $postId,
                $this->cron->getIdentifier(),
                $this->datetime->getWpDate('r', $timestamp),
                $timestamp,
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
                print_r($opts, true)
            )
        );

        $this->updateLegacyPostMetaUsedBy3rdPartySoftware($postId, $timestamp, $opts);
    }

    /**
     * Authorize the action after expiration options have been filtered.
     *
     * @since 4.11.0
     *
     * @param int $postId
     * @param array<string, mixed> $opts
     * @return bool
     */
    private function userCanScheduleOpts(int $postId, array $opts): bool
    {
        $expireType = '';
        $newStatus = '';

        if (isset($opts['expireType']) && is_string($opts['expireType'])) {
            $expireType = $opts['expireType'];
        }

        if (isset($opts['newStatus']) && is_string($opts['newStatus'])) {
            $newStatus = $opts['newStatus'];
        }

        $taxonomy = '';
        if (isset($opts['categoryTaxonomy']) && is_string($opts['categoryTaxonomy'])) {
            $taxonomy = $opts['categoryTaxonomy'];
        }

        $currentUserModelFactory = $this->currentUserModelFactory;
        $currentUserModel = $currentUserModelFactory();

        if (
            ! $currentUserModel instanceof CurrentUserModel
            || ! $currentUserModel->userCanScheduleAction($postId, $expireType, $newStatus, $taxonomy)
        ) {
            $this->logger->debug(
                sprintf(
                    '%1$d  -> ACTION NOT SCHEDULED: user cannot schedule %2$s (status %3$s)',
                    $postId,
                    $expireType,
                    $newStatus
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Cron and CLI reschedules run with no logged-in user and skip the cap check.
     * Every other logged-out request is checked and fails closed.
     *
     * @since 4.10.6
     *
     * @return bool
     */
    private function currentRequestSkipsScheduleAuthorization(): bool
    {
        if (is_user_logged_in()) {
            return false;
        }

        if (wp_doing_cron()) {
            return true;
        }

        return defined('WP_CLI') && WP_CLI;
    }

    private function updateLegacyPostMetaUsedBy3rdPartySoftware(int $postId, int $timestamp, array $opts): void
    {
        $postModelFactory = $this->postModelFactory;
        $postModel = $postModelFactory($postId);

        $type = $opts['expireType'] ?? '';
        $taxonomy = $opts['categoryTaxonomy'] ?? '';
        $terms = $opts['category'] ?? '';
        $newStatus = $opts['newStatus'] ?? '';

        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TIMESTAMP, $timestamp);
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_STATUS, 'saved');
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TYPE, $type);
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_POST_STATUS, $newStatus);
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TAXONOMY, $taxonomy);
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_TERMS, $terms);
        $postModel->updateMeta(PostMetaAbstract::EXPIRATION_DATE_OPTIONS, $opts);

        $postModel->updateMeta(
            ExpirablePostModel::FLAG_METADATA_HASH,
            $postModel->calcMetadataHash()
        );

        $postModel->deleteMeta(ExpirablePostModel::LEGACY_FLAG_METADATA_HASH);
    }

    private function unscheduleIfScheduled($postId, $timestamp)
    {
        if ($this->postIsScheduled($postId)) {
            $this->logger->debug($postId . ' -> FOUND SCHEDULED ACTION for the post using ' . $this->cron->getIdentifier());

            $this->unschedule($postId);
        }
    }

    /**
     * @param $postId
     * @return bool
     */
    public function postIsScheduled($postId)
    {
        return $this->cron->postHasScheduledActions($postId);
    }

    /**
     * @inheritDoc
     */
    public function unschedule($postId)
    {
        $this->hooks->doAction(HooksAbstract::ACTION_LEGACY_UNSCHEDULE, $postId);

        if ($this->postIsScheduled($postId)) {
            $result = $this->cron->clearScheduledAction(HooksAbstract::ACTION_RUN_WORKFLOW, ['postId' => $postId, 'workflow' => 'expire']);
            // Try to clear the legacy actions if the new one was not found
            if (! $result) {
                $result = $this->cron->clearScheduledAction(HooksAbstract::ACTION_LEGACY_RUN_WORKFLOW, ['postId' => $postId, 'workflow' => 'expire']);
            }
            if (! $result) {
                $result = $this->cron->clearScheduledAction(HooksAbstract::ACTION_LEGACY_EXPIRE_POST1, $postId);
            }
            if (! $result) {
                $result = $this->cron->clearScheduledAction(HooksAbstract::ACTION_LEGACY_EXPIRE_POST2, $postId);
            }

            $errorFeedback = null;
            if ($this->error->isWpError($result)) {
                $errorFeedback = 'found error: ' . $this->error->getWpErrorMessage($result);
            }

            if (empty($errorFeedback)) {
                $errorFeedback = 'no errors found';
            }

            $message = $postId . ' -> CLEARED SCHEDULED ACTION using ' . $this->cron->getIdentifier() . ', ' . $errorFeedback;

            $this->logger->debug($message);

            $this->disableExpirationPostMeta($postId);
        }
    }

    protected function disableExpirationPostMeta($postId)
    {
        $postModelFactory = $this->postModelFactory;
        $postModel = $postModelFactory($postId);
        $postModel->disableExpiration();
    }
}
